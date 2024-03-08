<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\NewUserType;
use App\Helper\PasswordHelper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/admin/user")
 */
class AdminUserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;

    private MailerInterface $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        MailerInterface $mailer
    )
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    /**
     * @return Response
     * @throws \Exception
     * @Route("/", name="app_admin_user")
     */
    public function index(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('admin/admin_user/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @throws \Exception
     * @return Response
     * @Route("/new", name="app_admin_user_new")
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(NewUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = PasswordHelper::generatePassword();
            $hashedPassword = $this->passwordHasher->hashPassword($user,$password);

            $user->setPassword($hashedPassword);
            $this->entityManager->persist($user);

            try {
                $this->entityManager->flush();

                $this->addFlash('notice', sprintf('Uživatel %s byl úspěšně přidán.', $user->getName()));

                $this->sendUserEmail($user, $password);
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('fail',  'Nepodařilo se odeslat email.');
            } catch (\Exception $e) {
                $this->addFlash('fail',  'Nepodařilo se vytvořit uživatele.');
            }


            return $this->redirectToRoute('app_admin_user');
        }

        return $this->render('admin/admin_user/new.html.twig', [
            'form' =>  $form->createView(),
        ]);
    }

    /**
     * @throws \Exception
     * @return Response
     * @Route("/edit/{id}", name="app_admin_user_edit")
     */
    public function edit(Request $request, int $id): Response
    {
        $user = $this->userRepository->find($id);

        if ($user === null) {
            return $this->redirectToRoute('app_admin_user');
        }

        $form = $this->createForm(NewUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('notice',  sprintf('Uživatel %s byl úspěšně upraven.', $user->getEmail()));

            return $this->redirectToRoute('app_admin_user');
        }

        return $this->render('admin/admin_user/edit.html.twig', [
            'form' =>  $form->createView(),
        ]);
    }

    /**
     * @throws \Exception
     * @return Response
     * @Route("/delete/{id}", name="app_admin_user_delete")
     */
    public function delete(Request $request, UserInterface $userInterface, int $id): Response
    {
        if ($userInterface->getId() === $id) {
            $this->addFlash('fail',  'Nelze smazat přihlášeného uživatele.');

            return $this->redirectToRoute('app_admin_user');
        }

        $user = $this->userRepository->find($id);

        if ($user !== null) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }
        $this->addFlash('notice',  sprintf('Uživatel %s byl úspěšně smazán.', $user->getEmail()));

        return $this->redirectToRoute('app_admin_user');
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendUserEmail(User $user, string $password): void
    {
        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_EMAIL'])
            ->to($user->getEmail())
            ->subject('Přidání uživatele - '.$user->getName().' '.$user->getSurname())
            ->htmlTemplate('emails/newUser.html.twig')
            ->context([
                'user' => $user,
                'password' => $password
            ]);

        $this->mailer->send($email);
    }
}

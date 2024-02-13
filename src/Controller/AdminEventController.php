<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\DocumentFile;
use App\Entity\DocumentStorage;
use App\Entity\Event;
use App\Form\CommentType;
use App\Form\EventType;
use App\Form\NewDocumentType;
use App\Helper\SlugHelper;
use App\Repository\CommentRepository;
use App\Repository\DocumentFileRepository;
use App\Repository\DocumentStorageRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/event")
 */
class AdminEventController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private UserRepository $userRepository;

    private MailerInterface $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MailerInterface $mailer
    )
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/", name="app_admin_event")
     */
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('admin/admin_event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @Route("/detail/{id}", name="app_admin_event_detail")
     */
    public function detail(Request $request,EventRepository $eventRepository, int $id): Response
    {
        $event = $eventRepository->find($id);
        $user = $this->getUser();

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setEvent($event);
            $comment->setUser($this->getUser());

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_event_detail', [
                'id' => $event->getId()
            ]);
        }

        return $this->render('admin/admin_event/detail.html.twig', [
            'event' => $event,
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new", name="app_admin_event_new")
     */
    public function new(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $photo = $form->get('photo')->getData();

            if ($photo !== null) {
                //Název bez extension
                $originalFileName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = SlugHelper::getSlug($originalFileName);
                $newFileName = $safeFileName.'-'.uniqid().'.';
                //Extension bez .
                $extension = $photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move('uploads/eventPhotos/', $newFileName.$extension);
                    $event->setPhoto('uploads/eventPhotos/'.$newFileName.$extension);
                } catch (FileException $e) {
                    throw $e;
                }
            }

            $this->entityManager->persist($event);

            try {
                $this->entityManager->flush();
                $this->sendEventEmail($event);

                $this->addFlash('notice', sprintf('Akce %s byla úspěšně vytvořena.', $event->getName()));
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('fail', 'Nepodařilo se odeslat email.');
            } catch (\Exception $e) {
                $this->addFlash('fail', 'Nepodařilo se vytvořit akci');
            }

            return $this->redirectToRoute('app_admin_event');
        }

        return $this->render('admin/admin_event/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="app_admin_event_edit")
     */
    public function edit(Request $request, EventRepository $eventRepository, int $id): Response
    {
        $event = $eventRepository->find($id);
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $photo = $form->get('photo')->getData();

            if ($photo !== null) {
                //Název bez extension
                $originalFileName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = SlugHelper::getSlug($originalFileName);
                $newFileName = $safeFileName.'-'.uniqid().'.';
                //Extension bez .
                $extension = $photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    if (file_exists($event->getPhoto())) {
                        unlink($event->getPhoto());
                    }
                    $photo->move('uploads/eventPhotos/', $newFileName.$extension);
                    $event->setPhoto('uploads/eventPhotos/'.$newFileName.$extension);
                } catch (FileException $e) {
                    throw $e;
                }
            }

            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_event');
        }

        return $this->render('admin/admin_event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="app_admin_event_delete")
     */
    public function delete(EventRepository $eventRepository, int $id): Response
    {
        $event = $eventRepository->find($id);
        if ($event !== null) {
            try {
                if (file_exists($event->getPhoto())) {
                    unlink($event->getPhoto());
                }
            } catch (FileException $e) {
                throw $e;
            }
            $this->entityManager->remove($event);
            $this->entityManager->flush();
        }

        $this->addFlash('notice', 'Akce '.$event->getName().' byla úspěšně odstraněna.');

        return $this->redirectToRoute('app_admin_event');
    }

    /**
     * @Route("/delete-event-photo/{id}", name="app_admin_event_photo_delete")
     */
    public function deleteFile(EventRepository $eventRepository,int $id): Response
    {
        $event = $eventRepository->find($id);
        if ($event === null) {
            $this->addFlash('fail', 'Akce nebyla nalezena.');
            return $this->redirectToRoute('app_admin_event_edit', [
                'id' => $event->getId()
            ]);
        }

        if ($event->getPhoto() !== null) {
            try {
                if (file_exists($event->getPhoto())) {
                    unlink($event->getPhoto());
                }
            } catch (FileException $e) {
                $this->addFlash('fail', 'Fotku akce '.$event->getName().' se nepodařilo smazat.');
                return $this->redirectToRoute('app_admin_event_edit', [
                    'id' => $event->getId()
                ]);
            }
            $event->setPhoto(null);
            $this->entityManager->persist($event);
            $this->entityManager->flush();

            $this->addFlash('notice', 'Fotku akce '.$event->getName().' byla úspěšně smazána.');
        }

        return $this->redirectToRoute('app_admin_event_edit', [
            'id' => $event->getId()
        ]);
    }

    /**
     * @Route("/delete-comment/{id}", name="app_event_comment_delete")
     */
    public function deleteComment(CommentRepository $commentRepository,int $id): Response
    {
        $comment = $commentRepository->find($id);

        if ($comment !== null) {
            if ($this->getUser() !== $comment->getUser() && !$this->isGranted('ROLE_ADMIN')) {
                $this->addFlash('fail', 'Komentář nemůžete smazat.');
                return $this->redirectToRoute('app_admin_event_detail', [
                    'id' => $comment->getEvent()->getId()
                ]);
            }

            $this->entityManager->remove($comment);
            $this->entityManager->flush();

            $this->addFlash('notice', 'Komentář byl úspěšně smazán.');
        }

        return $this->redirectToRoute('app_admin_event_detail', [
            'id' => $comment->getEvent()->getId()
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendEventEmail(Event $event): void
    {
        $emails = $this->userRepository->getAllEmails();

        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_EMAIL'])
            ->to(...$emails)
            ->subject('Nová akce - '.$event->getName())
            ->htmlTemplate('emails/newEvent.html.twig')
            ->context([
                'event' => $event,
            ]);

        $this->mailer->send($email);
    }
}

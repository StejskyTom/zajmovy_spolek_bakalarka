<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticlePhoto;
use App\Form\ArticleType;
use App\Helper\SlugHelper;
use App\Repository\ArticlePhotoRepository;
use App\Repository\ArticleRepository;
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
 * @Route("/admin/article")
 */
class AdminArticleController extends AbstractController
{
    const ARTICLE_PHOTO_PATH = 'uploads/articlePhotos/';
    private EntityManagerInterface $entityManager;

    private MailerInterface $mailer;

    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserRepository $userRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="app_admin_article")
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('admin/admin_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/detail/{id}", name="app_admin_article_detail")
     */
    public function detail(Request $request,ArticleRepository $articleRepository, int $id): Response
    {
        $article = $articleRepository->find($id);

        return $this->render('admin/admin_article/detail.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/new", name="app_admin_article_new")
     */
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $mainPhoto = $form->get('mainPhoto')->getData();
            $photos = $form->get('articlePhotos')->getData();

            if ($mainPhoto !== null) {
                //Název bez extension
                $originalFileName = pathinfo($mainPhoto->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = SlugHelper::getSlug($originalFileName);
                $newFileName = $safeFileName.'-'.uniqid().'.';
                //Extension bez .
                $extension = $mainPhoto->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $mainPhoto->move(self::ARTICLE_PHOTO_PATH, $newFileName.$extension);
                    $article->setMainPhoto(self::ARTICLE_PHOTO_PATH.$newFileName.$extension);
                } catch (FileException $e) {
                    throw $e;
                }
            }

            if ($photos !== null) {
                foreach ($photos as $photo) {
                    $articlePhoto = new ArticlePhoto();
                    //Název bez extension
                    $originalFileName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);

                    $safeFileName = SlugHelper::getSlug($originalFileName);
                    $newFileName = $safeFileName.'-'.uniqid().'.';
                    //Extension bez .
                    $extension = $photo->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $photo->move(self::ARTICLE_PHOTO_PATH, $newFileName.$extension);
                        $articlePhoto->setFileName($originalFileName.'.'.$extension);
                        $articlePhoto->setFilePath(self::ARTICLE_PHOTO_PATH.$newFileName.$extension);
                        $articlePhoto->setArticle($article);
                        $this->entityManager->persist($articlePhoto);
                    } catch (FileException $e) {
                        throw $e;
                    }
                }
            }

            $this->entityManager->persist($article);

            try {
                $this->entityManager->flush();
                $this->sendArticleEmail($article);

                $this->addFlash('notice', 'Článek byl úspěšně vytvořen.');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('fail', 'Nepodařilo se odeslat email.');
            } catch (\Exception $e) {
                $this->addFlash('fail', 'Nepodařilo se vytvořit článek');
            }

            return $this->redirectToRoute('app_admin_article');
        }

        return $this->render('admin/admin_article/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="app_admin_article_edit")
     */
    public function edit(Request $request, ArticleRepository $articleRepository, int $id): Response
    {
        $article = $articleRepository->find($id);
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        $hasArticlePhotos = $article->getArticlePhotos()->count() > 0;

        if ($form->isSubmitted()) {
            $mainPhoto = $form->get('mainPhoto')->getData();
            $photos = $form->get('articlePhotos')->getData();

            if ($mainPhoto !== null) {
                //Název bez extension
                $originalFileName = pathinfo($mainPhoto->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = SlugHelper::getSlug($originalFileName);
                $newFileName = $safeFileName.'-'.uniqid().'.';
                //Extension bez .
                $extension = $mainPhoto->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    if (file_exists($article->getMainPhoto())) {
                        unlink($article->getMainPhoto());
                    }
                    $mainPhoto->move(self::ARTICLE_PHOTO_PATH, $newFileName.$extension);
                    $article->setMainPhoto(self::ARTICLE_PHOTO_PATH.$newFileName.$extension);
                } catch (FileException $e) {
                    throw $e;
                }
            }

            if ($photos !== null) {
                foreach ($photos as $photo) {
                    $articlePhoto = new ArticlePhoto();
                    //Název bez extension
                    $originalFileName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);

                    $safeFileName = SlugHelper::getSlug($originalFileName);
                    $newFileName = $safeFileName.'-'.uniqid().'.';
                    //Extension bez .
                    $extension = $photo->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $photo->move(self::ARTICLE_PHOTO_PATH, $newFileName.$extension);
                        $articlePhoto->setFileName($originalFileName.'.'.$extension);
                        $articlePhoto->setFilePath(self::ARTICLE_PHOTO_PATH.$newFileName.$extension);
                        $articlePhoto->setArticle($article);
                        $this->entityManager->persist($articlePhoto);
                    } catch (FileException $e) {
                        throw $e;
                    }
                }
            }

            $article->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_article');
        }

        return $this->render('admin/admin_article/edit.html.twig', [
            'article' => $article,
            'hasArticlePhotos' => $hasArticlePhotos,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="app_admin_article_delete")
     */
    public function delete(ArticleRepository $articleRepository, int $id): Response
    {
        $article = $articleRepository->find($id);
        if ($article !== null) {
            try {
                if (file_exists($article->getMainPhoto())) {
                    unlink($article->getMainPhoto());
                }
                foreach ($article->getArticlePhotos() as $photo) {
                    if (file_exists($photo->getFilePath())) {
                        unlink($photo->getFilePath());
                        $this->entityManager->remove($photo);
                    }
                }
            } catch (FileException $e) {
                throw $e;
            }
            $this->entityManager->remove($article);
            $this->entityManager->flush();
        }

        $this->addFlash('notice', 'Článek '.$article->getTitle().' byl úspěšně odstraněn.');

        return $this->redirectToRoute('app_admin_article');
    }

    /**
     * @Route("/delete-article-photo/{id}", name="app_admin_article_photo_delete")
     */
    public function deleteFile(ArticlePhotoRepository $articlePhotoRepository,int $id): Response
    {
        $photo = $articlePhotoRepository->find($id);
        if ($photo === null) {
            $this->addFlash('fail', 'Akce nebyla nalezena.');
            return $this->redirectToRoute('app_admin_article');
        }

        if ($photo->getFilePath() !== null) {
            try {
                if (file_exists($photo->getFilePath())) {
                    unlink($photo->getFilePath());
                }
            } catch (FileException $e) {
                $this->addFlash('fail', 'Fotku '.$photo->getFileName().' se nepodařilo smazat.');
                return $this->redirectToRoute('app_admin_article_edit', [
                    'id' => $photo->getArticle()->getId()
                ]);
            }
            $this->entityManager->remove($photo);
            $this->entityManager->flush();

            $this->addFlash('notice', 'Fotka '.$photo->getFileName().' byla úspěšně smazána.');
        }

        return $this->redirectToRoute('app_admin_article_edit', [
            'id' => $photo->getArticle()->getId()
        ]);
    }

    /**
     * @Route("/delete-article-main-photo/{id}", name="app_admin_article_main_photo_delete")
     */
    public function deleteMainPhoto(ArticleRepository $articleRepository, int $id): Response
    {
        $article = $articleRepository->find($id);
        if ($article === null) {
            $this->addFlash('fail', 'Akce nebyla nalezena.');
            return $this->redirectToRoute('app_admin_article');
        }

        if ($article->getMainPhoto() !== null) {
            try {
                if (file_exists($article->getMainPhoto())) {
                    unlink($article->getMainPhoto());
                    $article->setMainPhoto(null);
                }
            } catch (FileException $e) {
                $this->addFlash('fail', 'Fotku '.$article->getMainPhoto().' se nepodařilo smazat.');
                return $this->redirectToRoute('app_admin_article_edit', [
                    'id' => $article->getId()
                ]);
            }
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            $this->addFlash('notice', 'Fotka '.$article->getMainPhoto().' byla úspěšně smazána.');
        }

        return $this->redirectToRoute('app_admin_article_edit', [
            'id' => $article->getId()
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendArticleEmail(Article $article): void
    {
        $emails = $this->userRepository->getAllEmails();

        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_EMAIL'])
            ->to(...$emails)
            ->subject('Nový článek - '.$article->getTitle())
            ->htmlTemplate('emails/newArticle.html.twig')
            ->context([
                'article' => $article,
            ]);

        $this->mailer->send($email);
    }
}

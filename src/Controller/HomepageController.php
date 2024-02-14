<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\DocumentStorageRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    protected $articleRepository;

    protected $eventRepository;

    protected $storageRepository;

    public function __construct(
        ArticleRepository $articleRepository,
        EventRepository $eventRepository,
        DocumentStorageRepository $storageRepository
    )
    {
        $this->articleRepository = $articleRepository;
        $this->eventRepository = $eventRepository;
        $this->storageRepository = $storageRepository;
    }

    /**
     * @Route({"cs":"/","ms":"/ms"}, name="app_homepage")
     */
    public function index(): Response
    {
        $events = $this->eventRepository->getEventsOrderByDate();
        $articles = $this->articleRepository->getArticlesOrderByDate();
        $storages = $this->storageRepository->getPublicDocumentStorageOrderByCreatedAt();


        return $this->render('public/index.html.twig', [
            'events' => $events,
            'articles' => $articles,
            'documents' => $storages
        ]);
    }
}

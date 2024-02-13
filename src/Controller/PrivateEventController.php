<?php

namespace App\Controller;

use App\Repository\DocumentStorageRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/private")
 */
class PrivateEventController extends AbstractController
{
    /**
     * @Route("/events", name="app_admin_private_events")
     */
    public function index(EventRepository $eventRepository): Response
    {
        $events =  $eventRepository->findAll();

        return $this->render('admin/admin_event/index.html.twig', [
            'events' => $events,
        ]);
    }
}

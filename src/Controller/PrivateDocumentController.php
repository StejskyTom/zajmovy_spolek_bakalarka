<?php

namespace App\Controller;

use App\Repository\DocumentStorageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/private")
 */
class PrivateDocumentController extends AbstractController
{
    /**
     * @Route("/documents", name="app_admin_private_documents")
     */
    public function index(DocumentStorageRepository $documentStorageRepository): Response
    {
        $documentStorage =  $documentStorageRepository->findAll();

        return $this->render('admin/admin_document/index.html.twig', [
            'documents' => $documentStorage,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\DocumentFile;
use App\Entity\DocumentStorage;
use App\Form\NewDocumentType;
use App\Helper\SlugHelper;
use App\Repository\DocumentFileRepository;
use App\Repository\DocumentStorageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/document")
 */
class AdminDocumentController extends AbstractController
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="app_admin_document")
     */
    public function index(DocumentStorageRepository $documentStorageRepository): Response
    {
        $documents = $documentStorageRepository->findAll();

        return $this->render('admin/admin_document/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    /**
     * @Route("/new", name="app_admin_document_new")
     */
    public function new(Request $request): Response
    {
        $documentStorage = new DocumentStorage();
        $form = $this->createForm(NewDocumentType::class, $documentStorage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documents = $form->get('documents')->getData();

            foreach ($documents as $document) {
                $documentFile = new DocumentFile();
                //Název bez extension
                $originalFileName = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = SlugHelper::getSlug($originalFileName);
                $newFileName = $safeFileName.'-'.uniqid().'.';
                //Extension bez .
                $extension = $document->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $document->move('uploads/documents/', $newFileName.$extension);
                    $documentFile->setFileName($originalFileName.'.'.$extension);
                    $documentFile->setFilePath('uploads/documents/'.$newFileName.$extension);
                    $documentFile->setDocumentStorage($documentStorage);
                    $this->entityManager->persist($documentFile);
                } catch (FileException $e) {
                    throw $e;
                }
            }
            $this->entityManager->persist($documentStorage);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_document');
        }

        return $this->render('admin/admin_document/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="app_admin_document_edit")
     */
    public function edit(Request $request, DocumentStorageRepository $documentStorageRepository, int $id): Response
    {
        $documentStorage = $documentStorageRepository->find($id);
        $form = $this->createForm(NewDocumentType::class, $documentStorage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $documentStorage !== null) {
            $documents = $form->get('documents')->getData();

            foreach ($documents as $document) {
                $documentFile = new DocumentFile();
                //Název bez extension
                $originalFileName = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = SlugHelper::getSlug($originalFileName);
                $newFileName = $safeFileName.'-'.uniqid().'.';
                //Extension bez .
                $extension = $document->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $document->move('uploads/documents/', $newFileName.$extension);
                    $documentFile->setFileName($originalFileName.'.'.$extension);
                    $documentFile->setFilePath('uploads/documents/'.$newFileName.$extension);
                    $documentFile->setDocumentStorage($documentStorage);
                    $this->entityManager->persist($documentFile);
                } catch (FileException $e) {
                    throw $e;
                }
            }
            $documentStorage->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($documentStorage);
            $this->entityManager->flush();

            $this->addFlash('notice', 'Dokumenty byly úspěšně upraveny.');

            return $this->redirectToRoute('app_admin_document');
        }

        return $this->render('admin/admin_document/edit.html.twig', [
            'form' => $form->createView(),
            'document' => $documentStorage
        ]);
    }

    /**
     * @Route("/delete/{id}", name="app_admin_document_delete")
     */
    public function delete(DocumentStorageRepository $documentStorageRepository, int $id): Response
    {
        $documentStorage = $documentStorageRepository->find($id);
        if ($documentStorage !== null) {
            if ($documentStorage->getFiles() !== null) {
                foreach ($documentStorage->getFiles() as $documentFile) {
                    try {
                        unlink($documentFile->getFilePath());
                    } catch (FileException $e) {
                        throw $e;
                    }
                    $this->entityManager->remove($documentFile);
                }
            }

            $this->entityManager->remove($documentStorage);
            $this->entityManager->flush();
        }

        $this->addFlash('notice', 'Dokumenty byly úspěšně smazány.');

        return $this->redirectToRoute('app_admin_document');
    }

    /**
     * @Route("/delete-file/{id}", name="app_admin_document_file_delete")
     */
    public function deleteFile(DocumentFileRepository $documentFileRepository, int $id): Response
    {
        $documentFile = $documentFileRepository->find($id);
        if ($documentFile !== null) {
            try {
                unlink($documentFile->getFilePath());
            } catch (FileException $e) {
                $this->addFlash('fail', 'Soubor: '.$documentFile->getFileName().' se nepodařilo smazat.');
                return $this->redirectToRoute('app_admin_document_edit', [
                    'id' => $documentFile->getDocumentStorage()->getId()
                ]);
            }
            $this->entityManager->remove($documentFile);
            $this->entityManager->flush();

            $this->addFlash('notice', 'Soubor: '.$documentFile->getFileName().' byl úspěšně smazán.');
        }

        return $this->redirectToRoute('app_admin_document_edit', [
            'id' => $documentFile->getDocumentStorage()->getId()
        ]);
    }
}

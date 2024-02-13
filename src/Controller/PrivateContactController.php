<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/private")
 */
class PrivateContactController extends AbstractController
{
    /**
     * @Route("/", name="app_admin_contact")
     */
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAllWithoutAdmin();

        return $this->render('admin/admin_contact/index.html.twig', [
            'users' => $users,
        ]);
    }
}

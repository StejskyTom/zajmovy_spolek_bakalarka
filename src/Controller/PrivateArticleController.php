<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/private")
 */
class PrivateArticleController extends AbstractController
{
    /**
     * @Route("/articles", name="app_admin_private_articles")
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles =  $articleRepository->findAll();

        return $this->render('admin/admin_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}

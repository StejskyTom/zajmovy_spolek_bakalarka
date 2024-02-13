<?php

namespace App\Helper;

use App\Repository\BoatRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SlugHelper extends AbstractController
{
    public static function getSlug(string $word): string
    {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($word);
        $slug = strtolower($slug);
        return $slug;
    }
}

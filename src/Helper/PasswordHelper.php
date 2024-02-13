<?php

namespace App\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PasswordHelper extends AbstractController
{
    public static function generatePassword(): string
    {
        return str_pad(random_int(1,99999999),8,'0',STR_PAD_LEFT);
    }
}

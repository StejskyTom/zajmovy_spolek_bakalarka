<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(
        UserPasswordHasherInterface $encoder
    )
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Admin');
        $user->setSurname('Admin');
        $user->setEmail('admin@admin.cz');
        $user->setPassword($this->encoder->hashPassword($user, 'admin'));
        $user->setRoles([User::ROLE_ADMIN]);

        $manager->persist($user);
        $manager->flush();
    }
}

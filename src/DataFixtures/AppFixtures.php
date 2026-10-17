<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPaswwordHasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $userAdmin = new User();
        $userAdmin->setEmail('admin@webcraftdg.fr')
        ->setFirstname('admin')
        ->setLastname('admin')
        ->setPassword($this->userPaswwordHasher->hashPassword($userAdmin, 'redcat'))
        ->setRoles([User::ROLE_ADMIN]);
        $manager->persist($userAdmin);

        $exercises = [

        ];
        $blocs = [

        ];

        $manager->flush();
    }
}

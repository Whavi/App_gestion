<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

   
    public function load(ObjectManager $manager): void
    {
        // Users
        UserFactory::createMany(10);
        // Admin 
        $users = [];

        $admin = new User();
        $admin->setNom('Bouanane')
            ->setPrenom("Fethi")
            ->SetEmail('admin@SIF.org')
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPassword('password');

        $users[] = $admin;
        $manager->persist($admin);
        $manager->flush();
        
    }
}

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
        UserFactory::createMany(5);
        
        // Admin 
        $users = [];

        $admin = new User();
        $admin->setNom('Jean')
            ->setPrenom("Dupont")
            ->setEmail('stage.it@secours-islamique.org')
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPassword('$2y$13$mk0lFLjyJ7m2HOHEgQhdleQIjVo2YodvAIZqnMR3//Am5XLdW4Swu')
            ->setAzureId('stage.it@secours-islamique.org');

        $users[] = $admin;
        $manager->persist($admin);
        $manager->flush();
    }
}

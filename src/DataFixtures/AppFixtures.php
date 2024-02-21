<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppFixtures extends Fixture
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager): void
    {
        // Users
        UserFactory::createMany(5);
        
        // Admin 
        $users = [];

        $admin = new User();
        $admin->setNom('Bouanane')
            ->setPrenom("Fethi")
            ->setEmail('admin@admin.org')
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPassword('$2y$13$mk0lFLjyJ7m2HOHEgQhdleQIjVo2YodvAIZqnMR3//Am5XLdW4Swu');

        $users[] = $admin;
        $manager->persist($admin);
        $manager->flush();
    }
}

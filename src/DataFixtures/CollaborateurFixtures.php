<?php

namespace App\DataFixtures;

use App\Factory\CollaborateurFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CollaborateurFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        CollaborateurFactory::createMany(15);
    }
}

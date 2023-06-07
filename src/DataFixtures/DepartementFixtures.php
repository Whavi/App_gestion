<?php

namespace App\DataFixtures;

use App\Factory\DepartementFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DepartementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        DepartementFactory::createMany(20);
    }
}

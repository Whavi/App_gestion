<?php

namespace App\DataFixtures;

use App\Factory\AttributionFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AttributionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        AttributionFactory::createMany(20);
    }
}

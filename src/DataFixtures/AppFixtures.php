<?php

namespace App\DataFixtures;

use App\Entity\Cartes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $carte = new Cartes;
            $carte->setNom('Carte ' . $i);
            $carte->setEffet("sans effet");
            $carte->setAttaque(1000);
            $carte->setDefence(1000);
            $manager->persist($carte);
        }

        $manager->flush();
    }
}

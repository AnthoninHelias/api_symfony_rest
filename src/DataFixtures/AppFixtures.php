<?php

namespace App\DataFixtures;

use App\Entity\Cartes;
use App\Entity\Rarete;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{


    public function load(ObjectManager $manager): void
    {
        $listRarete = [];
        for ($i = 0; $i < 1000; $i++) {
            $rarete = new Rarete();
            $rarete->setName('secret rare' . $i);
            $manager->persist($rarete);
            $listRarete[] = $rarete;
        }


        for ($i = 0; $i < 100000; $i++) {
            $carte = new Cartes;
            $carte->setNom('Carte ' . $i);
            $carte->setEffet("sans effet");
            $carte->setAttaque(1000);
            $carte->setDefence(1000);
            $carte->setRarete($listRarete[array_rand($listRarete)]);
            $manager->persist($carte);
        }

        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Cartes;
use App\Entity\Rarete;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setEmail("user@user.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);

        $userAdmin = new User();
        $userAdmin->setEmail("admin@admin.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);





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

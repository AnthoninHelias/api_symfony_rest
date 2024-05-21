<?php

namespace App\Entity;

use App\Repository\RareteRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RareteRepository::class)]
class Rarete
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["rarete" , "cartes"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["rarete" , "cartes"])]
    #[Assert\NotBlank(message: "Le nom de la rareté est obligatoire")]
    private ?string $name = null;

    #[ORM\OneToOne(targetEntity: Cartes::class, mappedBy: "rarete")]
    private ?Cartes $carte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCarte(): ?Cartes
    {
        return $this->carte;
    }

    public function setCarte(?Cartes $carte): static
    {
        $this->carte = $carte;
        return $this;
    }
}


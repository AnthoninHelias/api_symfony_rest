<?php

namespace App\Entity;

use App\Repository\RaretéRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaretéRepository::class)]
class Rareté
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Cartes::class, mappedBy: 'rareté')]
    private Collection $nom;

    public function __construct()
    {
        $this->nom = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Cartes>
     */
    public function getNom(): Collection
    {
        return $this->nom;
    }

    public function addNom(Cartes $nom): static
    {
        if (!$this->nom->contains($nom)) {
            $this->nom->add($nom);
            $nom->setRareté($this);
        }

        return $this;
    }

    public function removeNom(Cartes $nom): static
    {
        if ($this->nom->removeElement($nom)) {
            // set the owning side to null (unless already changed)
            if ($nom->getRareté() === $this) {
                $nom->setRareté(null);
            }
        }

        return $this;
    }
}

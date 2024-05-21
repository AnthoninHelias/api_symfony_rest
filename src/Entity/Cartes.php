<?php

namespace App\Entity;

use App\Repository\CartesRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Since;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use ApiPlatform\Metadata\ApiResource;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_cartes_id",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="cartes")
 * )
 *
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteCard",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="cartes", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 *
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "updateCard",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="cartes", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 *
 */


#[ORM\Entity(repositoryClass: CartesRepository::class)]
#[ApiResource]
class Cartes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["cartes"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["cartes"])]
    #[Assert\NotBlank(message: "Le nom de la carte est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["cartes"])]
    private ?string $effet = null;

    #[ORM\Column]
    #[Groups(["cartes"])]
    #[Assert\NotBlank(message: "L'attaque est obligatoire")]
    private ?int $attaque = null;

    #[ORM\Column]
    #[Groups(["cartes"])]
    #[Assert\NotBlank(message: "La défense est obligatoire")]
    private ?int $defence = null;

    #[ORM\ManyToOne(targetEntity:Rarete::class)]
    #[ORM\JoinColumn(onDelete:"CASCADE")]
    #[Groups(["cartes"])]
    private ?Rarete $rarete = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["cartes"])]
    #[Since("2.0")]
    private ?int $niveau = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEffet(): ?string
    {
        return $this->effet;
    }

    public function setEffet(?string $effet): static
    {
        $this->effet = $effet;

        return $this;
    }

    #
    public function getAttaque(): ?int
    {
        return $this->attaque;
    }

    public function setAttaque(int $attaque): static
    {
        $this->attaque = $attaque;

        return $this;
    }

    public function getDefence(): ?int
    {
        return $this->defence;
    }


    public function setDefence(int $defence): static
    {
        $this->defence = $defence;

        return $this;
    }

    public function getRarete():?Rarete
    {
        return $this->rarete;
    }

    public function setRarete(?Rarete $rarete): static
    {
        $this->rarete = $rarete;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(?int $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }


}

<?php

namespace App\Entity;

use App\Repository\PnjRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Hero;

#[ORM\Entity(repositoryClass: PnjRepository::class)]
class Pnj
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 300)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $information = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $personnalite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $competence = null;

    // Relation obligatoire avec le héros
    #[ORM\ManyToOne(targetEntity: Hero::class, inversedBy: "pnjs")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hero $hero = null;

    // ===== Getters & Setters =====

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): static
    {
        $this->information = $information;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): static
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getPersonnalite(): ?string
    {
        return $this->personnalite;
    }

    public function setPersonnalite(?string $personnalite): static
    {
        $this->personnalite = $personnalite;
        return $this;
    }

    public function getCompetence(): ?string
    {
        return $this->competence;
    }

    public function setCompetence(?string $competence): static
    {
        $this->competence = $competence;
        return $this;
    }

    public function getHero(): ?Hero
    {
        return $this->hero;
    }

    public function setHero(Hero $hero): static
    {
        $this->hero = $hero;
        return $this;
    }
}

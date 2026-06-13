<?php

namespace App\Entity;

use App\Repository\HeroRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Quest;
use App\Entity\Inventory;
use App\Entity\Competence;

#[ORM\Entity(repositoryClass: HeroRepository::class)]
class Hero
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $universe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $additionalInfo = null;

    #[ORM\Column(type: 'integer')]
    private int $gold = 0;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "heroes")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'hero', targetEntity: Inventory::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $inventories;

    #[ORM\OneToMany(mappedBy: 'hero', targetEntity: Quest::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $quests;

    #[ORM\OneToMany(mappedBy: 'hero', targetEntity: Competence::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $competences;

    public function __construct()
    {
        $this->inventories = new ArrayCollection();
        $this->quests = new ArrayCollection();
        $this->competences = new ArrayCollection();
        $this->gold = 0;
    }

    // ===== GETTERS / SETTERS =====
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }
    public function getUniverse(): ?string { return $this->universe; }
    public function setUniverse(?string $universe): static { $this->universe = $universe; return $this; }
    public function getAdditionalInfo(): ?string { return $this->additionalInfo; }
    public function setAdditionalInfo(?string $additionalInfo): static { $this->additionalInfo = $additionalInfo; return $this; }
    public function getGold(): int { return $this->gold; }
    public function setGold(int $gold): static { $this->gold = $gold; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    // ===== INVENTORY =====
    public function getInventories(): Collection { return $this->inventories; }
    public function addInventory(Inventory $inventory): static { 
        if (!$this->inventories->contains($inventory)) {
            $this->inventories->add($inventory);
            $inventory->setHero($this);
        }
        return $this; 
    }
    public function removeInventory(Inventory $inventory): static {
        if ($this->inventories->removeElement($inventory)) {
            if ($inventory->getHero() === $this) $inventory->setHero(null);
        }
        return $this;
    }

    // ===== QUEST =====
    public function getQuests(): Collection { return $this->quests; }
    public function addQuest(Quest $quest): static {
        if (!$this->quests->contains($quest)) {
            $this->quests->add($quest);
            $quest->setHero($this);
        }
        return $this;
    }
    public function removeQuest(Quest $quest): static {
        if ($this->quests->removeElement($quest)) {
            // La quête sera supprimée automatiquement grâce à orphanRemoval=true
        }
        return $this;
    }

    // ===== COMPETENCE =====
    public function getCompetences(): Collection { return $this->competences; }
    public function addCompetence(Competence $competence): static {
        if (!$this->competences->contains($competence)) {
            $this->competences->add($competence);
            $competence->setHero($this);
        }
        return $this;
    }
    public function removeCompetence(Competence $competence): static {
        if ($this->competences->removeElement($competence)) {
            if ($competence->getHero() === $this) $competence->setHero(null);
        }
        return $this;
    }
}
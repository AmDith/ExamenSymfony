<?php

namespace App\Entity;

use App\Repository\CoursClasseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoursClasseRepository::class)]
class CoursClasse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Cours $cours = null;

    #[ORM\ManyToOne]
    private ?Classe $classe = null;

    #[ORM\ManyToOne(inversedBy: 'coursClasse')]
    private ?Cours $cours2 = null;

    // #[ORM\ManyToOne(inversedBy: 'cours')]
    // private ?Classe $classe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;

        return $this;
    }

    // public function getClasse(): ?Classe
    // {
    //     return $this->classe;
    // }

    // public function setClasse(?Classe $classe): static
    // {
    //     $this->classe = $classe;

    //     return $this;
    // }

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): static
    {
        $this->classe = $classe;

        return $this;
    }

    public function getCours2(): ?Cours
    {
        return $this->cours2;
    }

    public function setCours2(?Cours $cours2): static
    {
        $this->cours2 = $cours2;

        return $this;
    }
}

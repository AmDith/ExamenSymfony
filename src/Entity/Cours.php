<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Professeur $prof = null;

    #[ORM\ManyToOne]
    private ?Module $module = null;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'cours')]
    private Collection $sessions;

    /**
     * @var Collection<int, CoursClasse>
     */
    #[ORM\OneToMany(targetEntity: CoursClasse::class, mappedBy: 'cours2')]
    private Collection $coursClasse;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->coursClasse = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProf(): ?Professeur
    {
        return $this->prof;
    }

    public function setProf(?Professeur $prof): static
    {
        $this->prof = $prof;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): static
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setCours($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getCours() === $this) {
                $session->setCours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CoursClasse>
     */
    public function getCoursClasse(): Collection
    {
        return $this->coursClasse;
    }

    public function addCoursClasse(CoursClasse $coursClasse): static
    {
        if (!$this->coursClasse->contains($coursClasse)) {
            $this->coursClasse->add($coursClasse);
            $coursClasse->setCours2($this);
        }

        return $this;
    }

    public function removeCoursClasse(CoursClasse $coursClasse): static
    {
        if ($this->coursClasse->removeElement($coursClasse)) {
            // set the owning side to null (unless already changed)
            if ($coursClasse->getCours2() === $this) {
                $coursClasse->setCours2(null);
            }
        }

        return $this;
    }
}

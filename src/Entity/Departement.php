<?php

namespace App\Entity;

use App\Repository\DepartementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartementRepository::class)]
class Departement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 45)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $create_At = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $update_At = null;

    #[ORM\OneToMany(mappedBy: 'id_depart', targetEntity: Collaborateur::class, orphanRemoval: true)]
    private Collection $collaborateurs;

    public function __construct()
    {
        $this->collaborateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_At;
    }

    public function setCreateAt(\DateTimeImmutable $create_At): self
    {
        $this->create_At = $create_At;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->update_At;
    }

    public function setUpdateAt(\DateTimeImmutable $update_At): self
    {
        $this->update_At = $update_At;

        return $this;
    }

    /**
     * @return Collection<int, Collaborateur>
     */
    public function getCollaborateurs(): Collection
    {
        return $this->collaborateurs;
    }

    public function addCollaborateur(Collaborateur $collaborateur): self
    {
        if (!$this->collaborateurs->contains($collaborateur)) {
            $this->collaborateurs->add($collaborateur);
            $collaborateur->setIdDepart($this);
        }

        return $this;
    }

    public function removeCollaborateur(Collaborateur $collaborateur): self
    {
        if ($this->collaborateurs->removeElement($collaborateur)) {
            // set the owning side to null (unless already changed)
            if ($collaborateur->getIdDepart() === $this) {
                $collaborateur->setIdDepart(null);
            }
        }

        return $this;
    }
}

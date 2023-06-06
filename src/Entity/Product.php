<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 45)]
    private ?string $identifiant = null;

    #[ORM\Column(length: 45)]
    private ?string $nom = null;

    #[ORM\Column(length: 45)]
    private ?string $category = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $create_At = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $update_At = null;

    #[ORM\OneToMany(mappedBy: 'id_product', targetEntity: Attribution::class, orphanRemoval: true)]
    private Collection $attributions;

    public function __construct()
    {
        $this->attributions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): self
    {
        $this->identifiant = $identifiant;

        return $this;
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

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
     * @return Collection<int, Attribution>
     */
    public function getAttributions(): Collection
    {
        return $this->attributions;
    }

    public function addAttribution(Attribution $attribution): self
    {
        if (!$this->attributions->contains($attribution)) {
            $this->attributions->add($attribution);
            $attribution->setIdProduct($this);
        }

        return $this;
    }

    public function removeAttribution(Attribution $attribution): self
    {
        if ($this->attributions->removeElement($attribution)) {
            // set the owning side to null (unless already changed)
            if ($attribution->getIdProduct() === $this) {
                $attribution->setIdProduct(null);
            }
        }

        return $this;
    }
}
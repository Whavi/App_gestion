<?php

namespace App\Entity;

use App\Repository\AttributionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributionRepository::class)]
class Attribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAttribution = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateRestitution = null;

    #[ORM\ManyToOne(inversedBy: 'attributions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?product $id_product = null;

    #[ORM\ManyToOne(inversedBy: 'attributions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?collaborateur $id_collaborateur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $create_At = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $update_At = null;

    #[ORM\ManyToOne(inversedBy: 'attributions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $byUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAttribution(): ?\DateTimeInterface
    {
        return $this->dateAttribution;
    }

    public function setDateAttribution(\DateTimeInterface $dateAttribution): self
    {
        $this->dateAttribution = $dateAttribution;

        return $this;
    }

    public function getDateRestitution(): ?\DateTimeInterface
    {
        return $this->dateRestitution;
    }

    public function setDateRestitution(\DateTimeInterface $dateRestitution): self
    {
        $this->dateRestitution = $dateRestitution;

        return $this;
    }

    public function getIdProduct(): ?product
    {
        return $this->id_product;
    }

    public function setIdProduct(?product $id_product): self
    {
        $this->id_product = $id_product;

        return $this;
    }

    public function getIdCollaborateur(): ?collaborateur
    {
        return $this->id_collaborateur;
    }

    public function setIdCollaborateur(?collaborateur $id_collaborateur): self
    {
        $this->id_collaborateur = $id_collaborateur;

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

    public function getByUser(): ?user
    {
        return $this->byUser;
    }

    public function setByUser(?user $byUser): self
    {
        $this->byUser = $byUser;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ContratRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContratRepository::class)]
class Contrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signatureID = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentID = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signerID = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfNotSigned = null;

    #[ORM\ManyToOne(inversedBy: 'contrats')]
    private ?Product $fk_id_product = null;

    #[ORM\ManyToOne(inversedBy: 'contrats')]
    private ?Collaborateur $fk_id_collaborateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSignatureID(): ?string
    {
        return $this->signatureID;
    }

    public function setSignatureID(?string $signatureID): static
    {
        $this->signatureID = $signatureID;

        return $this;
    }

    public function getDocumentID(): ?string
    {
        return $this->documentID;
    }

    public function setDocumentID(?string $documentID): static
    {
        $this->documentID = $documentID;

        return $this;
    }

    public function getSignerID(): ?string
    {
        return $this->signerID;
    }

    public function setSignerID(?string $signerID): static
    {
        $this->signerID = $signerID;

        return $this;
    }

    public function getPdfNotSigned(): ?string
    {
        return $this->pdfNotSigned;
    }

    public function setPdfNotSigned(?string $pdfNotSigned): static
    {
        $this->pdfNotSigned = $pdfNotSigned;

        return $this;
    }

    public function getFkIdProduct(): ?Product
    {
        return $this->fk_id_product;
    }

    public function setFkIdProduct(?Product $fk_id_product): static
    {
        $this->fk_id_product = $fk_id_product;

        return $this;
    }

    public function getFkIdCollaborateur(): ?Collaborateur
    {
        return $this->fk_id_collaborateur;
    }

    public function setFkIdCollaborateur(?Collaborateur $fk_id_collaborateur): static
    {
        $this->fk_id_collaborateur = $fk_id_collaborateur;

        return $this;
    }
}

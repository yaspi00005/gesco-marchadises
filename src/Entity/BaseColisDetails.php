<?php

namespace App\Entity;

use App\Repository\BaseColisDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BaseColisDetailsRepository::class)]
class BaseColisDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $typeMarchandises = null;

    #[ORM\Column]
    private ?string $poidsVolume = null;

    #[ORM\Column]
    private ?int $prixPoidsVolume = null;

    #[ORM\Column]
    private ?int $montantTotal = null;

    #[ORM\Column(length: 255 ,nullable:true)]
    private ?string $photo = null;

    #[ORM\Column]
    private ?bool $reception = false;

    #[ORM\Column(length: 255)]
    private ?string $numeroColis = null;

    #[ORM\ManyToOne(inversedBy: 'baseColisDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BaseColis $colis = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $observations = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeMarchandises(): ?string
    {
        return $this->typeMarchandises;
    }

    public function setTypeMarchandises(string $typeMarchandises): static
    {
        $this->typeMarchandises = $typeMarchandises;

        return $this;
    }

    public function getPoidsVolume(): ?string
    {
        return $this->poidsVolume;
    }

    public function setPoidsVolume(string $poidsVolume): static
    {
        $this->poidsVolume = $poidsVolume;

        return $this;
    }

    public function getPrixPoidsVolume(): ?int
    {
        return $this->prixPoidsVolume;
    }

    public function setPrixPoidsVolume(int $prixPoidsVolume): static
    {
        $this->prixPoidsVolume = $prixPoidsVolume;

        return $this;
    }

    public function getMontantTotal(): ?int
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(int $montantTotal): static
    {
        $this->montantTotal = $montantTotal;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function isReception(): ?bool
    {
        return $this->reception;
    }

    public function setReception(bool $reception): static
    {
        $this->reception = $reception;

        return $this;
    }

    public function getNumeroColis(): ?string
    {
        return $this->numeroColis;
    }

    public function setNumeroColis(string $numeroColis): static
    {
        $this->numeroColis = $numeroColis;

        return $this;
    }

    public function getColis(): ?BaseColis
    {
        return $this->colis;
    }

    public function setColis(?BaseColis $colis): static
    {
        $this->colis = $colis;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(string $observations): static
    {
        $this->observations = $observations;

        return $this;
    }
}

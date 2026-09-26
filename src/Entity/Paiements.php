<?php

namespace App\Entity;

use App\Repository\PaiementsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementsRepository::class)]
class Paiements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BaseColis $colis = null;

    #[ORM\Column]
    private ?int $montants = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePaiements = null;

    #[ORM\Column(length: 255)]
    private ?string $modePaiement = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?User $caissier = null;

    #[ORM\Column]
    private ?int $remises = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMontants(): ?int
    {
        return $this->montants;
    }

    public function setMontants(int $montants): static
    {
        $this->montants = $montants;

        return $this;
    }

    public function getDatePaiements(): ?\DateTimeInterface
    {
        return $this->datePaiements;
    }

    public function setDatePaiements(\DateTimeInterface $datePaiements): static
    {
        $this->datePaiements = $datePaiements;

        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->modePaiement;
    }

    public function setModePaiement(string $modePaiement): static
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }

    public function getCaissier(): ?User
    {
        return $this->caissier;
    }

    public function setCaissier(?User $caissier): static
    {
        $this->caissier = $caissier;

        return $this;
    }

    public function getRemises(): ?int
    {
        return $this->remises;
    }

    public function setRemises(int $remises): static
    {
        $this->remises = $remises;

        return $this;
    }
}

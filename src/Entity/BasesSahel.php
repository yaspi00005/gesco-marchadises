<?php

namespace App\Entity;

use App\Repository\BasesSahelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasesSahelRepository::class)]
class BasesSahel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'basesSahels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CLients $Clients = null;

    #[ORM\Column]
    private ?int $sahel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateOperations = null;

    #[ORM\ManyToOne(inversedBy: 'basesSahels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BaseColis $colis = null;

    #[ORM\Column]
    private ?bool $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClients(): ?CLients
    {
        return $this->Clients;
    }

    public function setClients(?CLients $Clients): static
    {
        $this->Clients = $Clients;

        return $this;
    }

    public function getSahel(): ?int
    {
        return $this->sahel;
    }

    public function setSahel(int $sahel): static
    {
        $this->sahel = $sahel;

        return $this;
    }

    public function getDateOperations(): ?\DateTimeInterface
    {
        return $this->dateOperations;
    }

    public function setDateOperations(\DateTimeInterface $dateOperations): static
    {
        $this->dateOperations = $dateOperations;

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

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}

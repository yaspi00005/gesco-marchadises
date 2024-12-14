<?php

namespace App\Entity;

use App\Repository\ExpeditionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpeditionsRepository::class)]
class Expeditions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $modeTransport = null;

    #[ORM\Column(length: 100)]
    private ?string $numeroExpeditions = null;

    #[ORM\Column(length: 100)]
    private ?string $depart = null;

    #[ORM\Column(length: 100)]
    private ?string $destinations = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateExpeditions = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateArrive = null;

    /**
     * @var Collection<int, BaseColis>
     */
    #[ORM\OneToMany(targetEntity: BaseColis::class, mappedBy: 'expeditions')]
    private Collection $baseColis;

    #[ORM\Column]
    private ?bool $receptions = null;

    public function __construct()
    {
        $this->baseColis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModeTransport(): ?string
    {
        return $this->modeTransport;
    }

    public function setModeTransport(string $modeTransport): static
    {
        $this->modeTransport = $modeTransport;

        return $this;
    }

    public function getNumeroExpeditions(): ?string
    {
        return $this->numeroExpeditions;
    }

    public function setNumeroExpeditions(string $numeroExpeditions): static
    {
        $this->numeroExpeditions = $numeroExpeditions;

        return $this;
    }

    public function getDepart(): ?string
    {
        return $this->depart;
    }

    public function setDepart(string $depart): static
    {
        $this->depart = $depart;

        return $this;
    }

    public function getDestinations(): ?string
    {
        return $this->destinations;
    }

    public function setDestinations(string $destinations): static
    {
        $this->destinations = $destinations;

        return $this;
    }

    public function getDateExpeditions(): ?\DateTimeInterface
    {
        return $this->dateExpeditions;
    }

    public function setDateExpeditions(\DateTimeInterface $dateExpeditions): static
    {
        $this->dateExpeditions = $dateExpeditions;

        return $this;
    }

    public function getDateArrive(): ?\DateTimeInterface
    {
        return $this->dateArrive;
    }

    public function setDateArrive(?\DateTimeInterface $dateArrive): static
    {
        $this->dateArrive = $dateArrive;

        return $this;
    }

    /**
     * @return Collection<int, BaseColis>
     */
    public function getBaseColis(): Collection
    {
        return $this->baseColis;
    }

    public function addBaseColi(BaseColis $baseColi): static
    {
        if (!$this->baseColis->contains($baseColi)) {
            $this->baseColis->add($baseColi);
            $baseColi->setExpeditions($this);
        }

        return $this;
    }

    public function removeBaseColi(BaseColis $baseColi): static
    {
        if ($this->baseColis->removeElement($baseColi)) {
            // set the owning side to null (unless already changed)
            if ($baseColi->getExpeditions() === $this) {
                $baseColi->setExpeditions(null);
            }
        }

        return $this;
    }

    public function isReceptions(): ?bool
    {
        return $this->receptions;
    }

    public function setReceptions(bool $receptions): static
    {
        $this->receptions = $receptions;

        return $this;
    }
}

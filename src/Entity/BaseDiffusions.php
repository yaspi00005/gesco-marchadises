<?php

namespace App\Entity;

use App\Repository\BaseDiffusionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BaseDiffusionsRepository::class)]
class BaseDiffusions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'baseDiffusions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $destinateurs = null;

    #[ORM\Column(length: 20)]
    private ?string $canaux = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'baseDiffusions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?expeditions $expeditions = null;

    #[ORM\Column]
    private ?bool $send = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDiffusion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDestinateurs(): ?User
    {
        return $this->destinateurs;
    }

    public function setDestinateurs(?User $destinateurs): static
    {
        $this->destinateurs = $destinateurs;

        return $this;
    }

    public function getCanaux(): ?string
    {
        return $this->canaux;
    }

    public function setCanaux(string $canaux): static
    {
        $this->canaux = $canaux;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getExpeditions(): ?expeditions
    {
        return $this->expeditions;
    }

    public function setExpeditions(?expeditions $expeditions): static
    {
        $this->expeditions = $expeditions;

        return $this;
    }

    public function isSend(): ?bool
    {
        return $this->send;
    }

    public function setSend(bool $send): static
    {
        $this->send = $send;

        return $this;
    }

    public function getDateDiffusion(): ?\DateTimeInterface
    {
        return $this->dateDiffusion;
    }

    public function setDateDiffusion(?\DateTimeInterface $dateDiffusion): static
    {
        $this->dateDiffusion = $dateDiffusion;

        return $this;
    }
}

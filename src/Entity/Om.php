<?php

namespace App\Entity;

use App\Repository\OmRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OmRepository::class)]
class Om
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $txnid = null;

    #[ORM\Column(length: 255)]
    private ?string $payToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperations = null;

    #[ORM\Column(length: 255)]
    private ?string $orderNum = null;

    #[ORM\Column]
    private ?bool $notif = false;

    #[ORM\Column(length: 255)]
    private ?string $statuts = '';

    #[ORM\ManyToOne(inversedBy: 'oms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BaseColis $colis = null;

    #[ORM\Column]
    private ?int $montant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTxnid(): ?string
    {
        return $this->txnid;
    }

    public function setTxnid(string $txnid): static
    {
        $this->txnid = $txnid;

        return $this;
    }

    public function getPayToken(): ?string
    {
        return $this->payToken;
    }

    public function setPayToken(string $payToken): static
    {
        $this->payToken = $payToken;

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

    public function getOrderNum(): ?string
    {
        return $this->orderNum;
    }

    public function setOrderNum(string $orderNum): static
    {
        $this->orderNum = $orderNum;

        return $this;
    }

    public function isNotif(): ?bool
    {
        return $this->notif;
    }

    public function setNotif(bool $notif): static
    {
        $this->notif = $notif;

        return $this;
    }

    public function getStatuts(): ?string
    {
        return $this->statuts;
    }

    public function setStatuts(string $statuts): static
    {
        $this->statuts = $statuts;

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

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): static
    {
        $this->montant = $montant;

        return $this;
    }
}

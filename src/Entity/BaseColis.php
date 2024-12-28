<?php

namespace App\Entity;

use App\Repository\BaseColisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BaseColisRepository::class)]
class BaseColis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $numeroSuivi = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateReceptions = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateRecuperations = null;

    #[ORM\Column(length: 100)]
    private ?string $statutPaiments = 'Non payé';

    #[ORM\ManyToOne(inversedBy: 'baseColis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $expediteurs = null;

    #[ORM\ManyToOne(inversedBy: 'baseColis')]
    private ?Expeditions $expeditions = null;

    #[ORM\Column(length: 100)]
    private ?string $typeExpeditions = null;

    #[ORM\Column]
    private ?int $fraisExpeditions = 0;

    #[ORM\Column(length: 255 , nullable:true)]
    private ?string $paiement = '-';

    #[ORM\ManyToOne(inversedBy: 'baseColisDestinateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $destinateurs = null;

    /**
     * @var Collection<int, BaseColisDetails>
     */
    #[ORM\OneToMany(targetEntity: BaseColisDetails::class, mappedBy: 'colis')]
    private Collection $baseColisDetails;

    #[ORM\Column]
    private ?bool $receptions = false;

    #[ORM\Column(length: 100)]
    private ?string $statut = 'En préparation';

    #[ORM\Column]
    private ?int $poidsVolumeTotal = 0;

    #[ORM\Column]
    private ?int $unites = null;

    #[ORM\Column(length: 255)]
    private ?string $qrCodePath = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    public function __construct()
    {
        $this->baseColisDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroSuivi(): ?string
    {
        return $this->numeroSuivi;
    }

    public function setNumeroSuivi(string $numeroSuivi): static
    {
        $this->numeroSuivi = $numeroSuivi;

        return $this;
    }


    public function getDateReceptions(): ?\DateTimeInterface
    {
        return $this->dateReceptions;
    }

    public function setDateReceptions(\DateTimeInterface $dateReceptions): static
    {
        $this->dateReceptions = $dateReceptions;

        return $this;
    }

    public function getDateRecuperations(): ?\DateTimeInterface
    {
        return $this->dateRecuperations;
    }

    public function setDateRecuperations(?\DateTimeInterface $dateRecuperations): static
    {
        $this->dateRecuperations = $dateRecuperations;

        return $this;
    }

    public function getStatutPaiments(): ?string
    {
        return $this->statutPaiments;
    }

    public function setStatutPaiments(string $statutPaiments): static
    {
        $this->statutPaiments = $statutPaiments;

        return $this;
    }

    public function getExpediteurs(): ?User
    {
        return $this->expediteurs;
    }

    public function setExpediteurs(?User $expediteurs): static
    {
        $this->expediteurs = $expediteurs;

        return $this;
    }

    public function getExpeditions(): ?Expeditions
    {
        return $this->expeditions;
    }

    public function setExpeditions(?Expeditions $expeditions): static
    {
        $this->expeditions = $expeditions;

        return $this;
    }

    public function getTypeExpeditions(): ?string
    {
        return $this->typeExpeditions;
    }

    public function setTypeExpeditions(string $typeExpeditions): static
    {
        $this->typeExpeditions = $typeExpeditions;

        return $this;
    }

    public function getFraisExpeditions(): ?int
    {
        return $this->fraisExpeditions;
    }

    public function setFraisExpeditions(int $fraisExpeditions): static
    {
        $this->fraisExpeditions = $fraisExpeditions;

        return $this;
    }

    public function getPaiement(): ?string
    {
        return $this->paiement;
    }

    public function setPaiement(string $paiement): static
    {
        $this->paiement = $paiement;

        return $this;
    }

    public function getDestinateurs(): ?user
    {
        return $this->destinateurs;
    }

    public function setDestinateurs(?user $destinateurs): static
    {
        $this->destinateurs = $destinateurs;

        return $this;
    }

    /**
     * @return Collection<int, BaseColisDetails>
     */
    public function getBaseColisDetails(): Collection
    {
        return $this->baseColisDetails;
    }

    public function addBaseColisDetail(BaseColisDetails $baseColisDetail): static
    {
        if (!$this->baseColisDetails->contains($baseColisDetail)) {
            $this->baseColisDetails->add($baseColisDetail);
            $baseColisDetail->setColis($this);
        }

        return $this;
    }

    public function removeBaseColisDetail(BaseColisDetails $baseColisDetail): static
    {
        if ($this->baseColisDetails->removeElement($baseColisDetail)) {
            // set the owning side to null (unless already changed)
            if ($baseColisDetail->getColis() === $this) {
                $baseColisDetail->setColis(null);
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPoidsVolumeTotal(): ?int
    {
        return $this->poidsVolumeTotal;
    }

    public function setPoidsVolumeTotal(int $poidsVolumeTotal): static
    {
        $this->poidsVolumeTotal = $poidsVolumeTotal;

        return $this;
    }

    public function getUnites(): ?int
    {
        return $this->unites;
    }

    public function setUnites(int $unites): static
    {
        $this->unites = $unites;

        return $this;
    }

    public function getQrCodePath(): ?string
    {
        return $this->qrCodePath;
    }

    public function setQrCodePath(string $qrCodePath): static
    {
        $this->qrCodePath = $qrCodePath;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }
}

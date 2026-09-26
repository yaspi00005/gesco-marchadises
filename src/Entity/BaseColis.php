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
    private ?string $statutPaiements = 'Non payé';

    #[ORM\ManyToOne(inversedBy: 'baseColis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $expediteurs = null;

    #[ORM\ManyToOne(inversedBy: 'baseColis')]
    private ?Expeditions $expeditions = null;

    #[ORM\Column(length: 100)]
    private ?string $typeExpeditions = 'Standard';

    #[ORM\Column]
    private ?int $fraisExpeditions = 0;

    #[ORM\Column(length: 255 , nullable:true)]
    private ?string $paiement = '-';

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
    private ?int $unites = null;

    #[ORM\Column(length: 255)]
    private ?string $qrCodePath = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    /**
     * @var Collection<int, Paiements>
     */
    #[ORM\OneToMany(targetEntity: Paiements::class, mappedBy: 'colis')]
    private Collection $paiements;

    /**
     * @var Collection<int, Om>
     */
    #[ORM\OneToMany(targetEntity: Om::class, mappedBy: 'colis')]
    private Collection $oms;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateUpdate = null;

    #[ORM\ManyToOne(inversedBy: 'baseColis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clients $clients = null;

    #[ORM\Column(length: 255)]
    private ?string $destinateursNom = null;

    #[ORM\Column(length: 255)]
    private ?string $destinateursTelephones = null;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    #[ORM\Column(length: 10)]
    private ?int $montantPaye = 0;

    #[ORM\Column]
    private ?int $remises = 0;

    /**
     * @var Collection<int, BasesSahel>
     */
    #[ORM\OneToMany(targetEntity: BasesSahel::class, mappedBy: 'colis')]
    private Collection $basesSahels;

    public function __construct()
    {
        $this->baseColisDetails = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->oms = new ArrayCollection();
        $this->basesSahels = new ArrayCollection();
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

    public function getStatutPaiements(): ?string
    {
        return $this->statutPaiements;
    }

    public function setStatutPaiements(string $statutPaiements): static
    {
        $this->statutPaiements = $statutPaiements;

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

    /**
     * @return Collection<int, Paiements>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiements $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setColis($this);
        }

        return $this;
    }

    public function removePaiement(Paiements $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getColis() === $this) {
                $paiement->setColis(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Om>
     */
    public function getOms(): Collection
    {
        return $this->oms;
    }

    public function addOm(Om $om): static
    {
        if (!$this->oms->contains($om)) {
            $this->oms->add($om);
            $om->setColis($this);
        }

        return $this;
    }

    public function removeOm(Om $om): static
    {
        if ($this->oms->removeElement($om)) {
            // set the owning side to null (unless already changed)
            if ($om->getColis() === $this) {
                $om->setColis(null);
            }
        }

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(\DateTimeInterface $dateUpdate): static
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getClients(): ?Clients
    {
        return $this->clients;
    }

    public function setClients(?Clients $clients): static
    {
        $this->clients = $clients;

        return $this;
    }

    public function getDestinateursNom(): ?string
    {
        return $this->destinateursNom;
    }

    public function setDestinateursNom(string $destinateursNom): static
    {
        $this->destinateursNom = $destinateursNom;

        return $this;
    }

    public function getDestinateursTelephones(): ?string
    {
        return $this->destinateursTelephones;
    }

    public function setDestinateursTelephones(string $destinateursTelephones): static
    {
        $this->destinateursTelephones = $destinateursTelephones;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getMontantPaye(): ?int
    {
        return $this->montantPaye;
    }

    public function setMontantPaye(int $montantPaye): static
    {
        $this->montantPaye = $montantPaye;

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

    /**
     * @return Collection<int, BasesSahel>
     */
    public function getBasesSahels(): Collection
    {
        return $this->basesSahels;
    }

    public function addBasesSahel(BasesSahel $basesSahel): static
    {
        if (!$this->basesSahels->contains($basesSahel)) {
            $this->basesSahels->add($basesSahel);
            $basesSahel->setColis($this);
        }

        return $this;
    }

    public function removeBasesSahel(BasesSahel $basesSahel): static
    {
        if ($this->basesSahels->removeElement($basesSahel)) {
            // set the owning side to null (unless already changed)
            if ($basesSahel->getColis() === $this) {
                $basesSahel->setColis(null);
            }
        }

        return $this;
    }
}

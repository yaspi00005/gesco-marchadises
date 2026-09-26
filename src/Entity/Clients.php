<?php

namespace App\Entity;

use App\Repository\ClientsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ClientsRepository::class)]
class Clients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: "Le prénom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^(?:\+33|0)[1-9](?:[\s.-]?[0-9]{2}){4}$/",
        message: "Le numéro de téléphone doit être un numéro français valide."
    )]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "L'adresse doit contenir au moins {{ limit }} caractères.",
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $adresse = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "La ville est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "La ville doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La ville ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $ville = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Le code postal est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^\d{5}$/",
        message: "Le code postal doit être composé de 5 chiffres."
    )]
    private ?string $codePostal = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le pays est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Le pays doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le pays ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $pays = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, BaseColis>
     */
    #[ORM\OneToMany(targetEntity: BaseColis::class, mappedBy: 'clients')]
    private Collection $baseColis;

    /**
     * @var Collection<int, Programmes>
     */
    #[ORM\OneToMany(targetEntity: Programmes::class, mappedBy: 'Clients')]
    private Collection $programmes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    /**
     * @var Collection<int, BasesSahel>
     */
    #[ORM\OneToMany(targetEntity: BasesSahel::class, mappedBy: 'Clients')]
    private Collection $basesSahels;

    #[ORM\Column]
    private ?int $sahel = 0;

    public function __construct()
    {
        $this->baseColis = new ArrayCollection();
        $this->programmes = new ArrayCollection();
        $this->basesSahels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = mb_strtoupper($nom, "UTF-8"); // Convertit tout en majuscules
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }


    public function setPrenom(string $prenom): self
    {
        $this->prenom = mb_convert_case($prenom, MB_CASE_TITLE, "UTF-8"); // Met la première lettre en majuscule
        return $this;
    }



    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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
            $baseColi->setClients($this);
        }

        return $this;
    }

    public function removeBaseColi(BaseColis $baseColi): static
    {
        if ($this->baseColis->removeElement($baseColi)) {
            // set the owning side to null (unless already changed)
            if ($baseColi->getClients() === $this) {
                $baseColi->setClients(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Programmes>
     */
    public function getProgrammes(): Collection
    {
        return $this->programmes;
    }

    public function addProgramme(Programmes $programme): static
    {
        if (!$this->programmes->contains($programme)) {
            $this->programmes->add($programme);
            $programme->setClients($this);
        }

        return $this;
    }

    public function removeProgramme(Programmes $programme): static
    {
        if ($this->programmes->removeElement($programme)) {
            // set the owning side to null (unless already changed)
            if ($programme->getClients() === $this) {
                $programme->setClients(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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
            $basesSahel->setClients($this);
        }

        return $this;
    }

    public function removeBasesSahel(BasesSahel $basesSahel): static
    {
        if ($this->basesSahels->removeElement($basesSahel)) {
            // set the owning side to null (unless already changed)
            if ($basesSahel->getClients() === $this) {
                $basesSahel->setClients(null);
            }
        }

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
}

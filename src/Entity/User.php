<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USENAME', fields: ['usename'])]
#[UniqueEntity(fields: ['usename'], message: 'Il existe déjà un compte avec ce nom d\'utilisateur')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    #[Assert\Length(
        min: 8,
        max: 8,
        minMessage: 'Your first name must be at least {{ limit }} characters long',
        maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
    )]
    /* #[Assert\Regex(
        pattern: '/^(6|7|8|9)\d{7}$/',
        message: 'Veuillez saisir un numéro de téléphone malien valide.'
    )] */
    private ?string $usename = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: 'Votre prénom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Votre prénom ne peut pas contenir plus de {{ limit }} caractères',
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: 'Votre nom doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Votre nom ne peut pas contenir plus de {{ limit }} caractères',
    )]
    private ?string $nom = null;

  /*   #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(
        message: 'L\'e-mail {{ value }} n\'est pas un e-mail valide.',
    )]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Votre e-mail ne peut pas contenir plus de {{ limit }} caractères',
    )]
    private ?string $email = null; */

    #[ORM\Column]
    private ?bool $statut = true;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, BaseColis>
     */
    #[ORM\OneToMany(targetEntity: BaseColis::class, mappedBy: 'expediteurs')]
    private Collection $baseColis;

    /**
     * @var Collection<int, BaseColis>
     */
    #[ORM\OneToMany(targetEntity: BaseColis::class, mappedBy: 'destinateurs')]
    private Collection $baseColisDestinateurs;

    /**
     * @var Collection<int, Paiements>
     */
    #[ORM\OneToMany(targetEntity: Paiements::class, mappedBy: 'caissier')]
    private Collection $paiements;

   

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    /**
     * @var Collection<int, BaseDiffusions>
     */
    #[ORM\OneToMany(targetEntity: BaseDiffusions::class, mappedBy: 'destinateurs')]
    private Collection $baseDiffusions;

    public function __construct()
    {
        $this->baseColis = new ArrayCollection();
        $this->baseColisDestinateurs = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->baseDiffusions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsename(): ?string
    {
        return $this->usename;
    }

    public function setUsename(string $usename): static
    {
        $this->usename = $usename;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->usename;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

/*     public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
 */
    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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
            $baseColi->setExpediteurs($this);
        }

        return $this;
    }

    public function removeBaseColi(BaseColis $baseColi): static
    {
        if ($this->baseColis->removeElement($baseColi)) {
            // set the owning side to null (unless already changed)
            if ($baseColi->getExpediteurs() === $this) {
                $baseColi->setExpediteurs(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BaseColis>
     */
    public function getBaseColisDestinateurs(): Collection
    {
        return $this->baseColisDestinateurs;
    }

    public function addBaseColisDestinateur(BaseColis $baseColisDestinateur): static
    {
        if (!$this->baseColisDestinateurs->contains($baseColisDestinateur)) {
            $this->baseColisDestinateurs->add($baseColisDestinateur);
            $baseColisDestinateur->setDestinateurs($this);
        }

        return $this;
    }

    public function removeBaseColisDestinateur(BaseColis $baseColisDestinateur): static
    {
        if ($this->baseColisDestinateurs->removeElement($baseColisDestinateur)) {
            // set the owning side to null (unless already changed)
            if ($baseColisDestinateur->getDestinateurs() === $this) {
                $baseColisDestinateur->setDestinateurs(null);
            }
        }

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
            $paiement->setCaissier($this);
        }

        return $this;
    }

    public function removePaiement(Paiements $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getCaissier() === $this) {
                $paiement->setCaissier(null);
            }
        }

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
     * @return Collection<int, BaseDiffusions>
     */
    public function getBaseDiffusions(): Collection
    {
        return $this->baseDiffusions;
    }

    public function addBaseDiffusion(BaseDiffusions $baseDiffusion): static
    {
        if (!$this->baseDiffusions->contains($baseDiffusion)) {
            $this->baseDiffusions->add($baseDiffusion);
            $baseDiffusion->setDestinateurs($this);
        }

        return $this;
    }

    public function removeBaseDiffusion(BaseDiffusions $baseDiffusion): static
    {
        if ($this->baseDiffusions->removeElement($baseDiffusion)) {
            // set the owning side to null (unless already changed)
            if ($baseDiffusion->getDestinateurs() === $this) {
                $baseDiffusion->setDestinateurs(null);
            }
        }

        return $this;
    }
}

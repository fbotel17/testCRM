<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse_societe = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'client')]
    private Collection $user_id;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\ManyToOne(inversedBy: 'clients')]
    private ?Company $company = null;

    /**
     * @var Collection<int, ClientNote>
     */
    #[ORM\OneToMany(targetEntity: ClientNote::class, mappedBy: 'client_id', cascade: ['persist', 'remove'])]
    private Collection $clientNotes;

    

    public function __construct()
    {
        $this->user_id = new ArrayCollection();
        $this->clientNotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresseSociete(): ?string
    {
        return $this->adresse_societe;
    }

    public function setAdresseSociete(string $adresse_societe): static
    {
        $this->adresse_societe = $adresse_societe;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserId(): Collection
    {
        return $this->user_id;
    }

    public function addUserId(User $userId): static
    {
        if (!$this->user_id->contains($userId)) {
            $this->user_id->add($userId);
            $userId->setClient($this);
        }

        return $this;
    }

    public function removeUserId(User $userId): static
    {
        if ($this->user_id->removeElement($userId)) {
            // set the owning side to null (unless already changed)
            if ($userId->getClient() === $this) {
                $userId->setClient(null);
            }
        }

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, ClientNote>
     */
    public function getClientNotes(): Collection
    {
        return $this->clientNotes;
    }

    public function addClientNote(ClientNote $clientNote): static
    {
        if (!$this->clientNotes->contains($clientNote)) {
            $this->clientNotes->add($clientNote);
            $clientNote->setClientId($this);
        }

        return $this;
    }

    public function removeClientNote(ClientNote $clientNote): static
    {
        if ($this->clientNotes->removeElement($clientNote)) {
            // Set the owning side to null (unless already changed)
            if ($clientNote->getClientId() === $this) {
                $clientNote->setClientId(null);
            }
        }

        return $this;
    }
    

    
}

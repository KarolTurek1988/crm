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
    public const string STATUS_NEW = 'NEW';
    public const string STATUS_CONTACTED = 'CONTACTED';
    public const string STATUS_OFFER_SENT = 'OFFER_SENT';
    public const string STATUS_WON = 'WON';
    public const string STATUS_LOST = 'LOST';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(length: 30)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Lead>
     */
    #[ORM\OneToMany(targetEntity: Lead::class, mappedBy: 'client')]
    private Collection $leads;

    /**
     * @var Collection<int, ContactHistory>
     */
    #[ORM\OneToMany(targetEntity: ContactHistory::class, mappedBy: 'client')]
    private Collection $contactHistories;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->leads = new ArrayCollection();
        $this->contactHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Collection<int, Lead>
     */
    public function getLeads(): Collection
    {
        return $this->leads;
    }

    public function addLead(Lead $lead): static
    {
        if (!$this->leads->contains($lead)) {
            $this->leads->add($lead);
            $lead->setClient($this);
        }

        return $this;
    }

    public function removeLead(Lead $lead): static
    {
        if ($this->leads->removeElement($lead)) {
            // set the owning side to null (unless already changed)
            if ($lead->getClient() === $this) {
                $lead->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ContactHistory>
     */
    public function getContactHistories(): Collection
    {
        return $this->contactHistories;
    }

    public function addContactHistory(ContactHistory $contactHistory): static
    {
        if (!$this->contactHistories->contains($contactHistory)) {
            $this->contactHistories->add($contactHistory);
            $contactHistory->setClient($this);
        }

        return $this;
    }

    public function removeContactHistory(ContactHistory $contactHistory): static
    {
        if ($this->contactHistories->removeElement($contactHistory)) {
            // set the owning side to null (unless already changed)
            if ($contactHistory->getClient() === $this) {
                $contactHistory->setClient(null);
            }
        }

        return $this;
    }
}

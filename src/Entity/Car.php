<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $SerialNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $SerialDate = null;

    #[ORM\Column(length: 255)]
    private ?string $Type = null;

    #[ORM\Column(length: 10)]
    private ?string $Color = null;

    #[ORM\Column(length: 255)]
    private ?string $Brand = null;

    #[ORM\Column]
    private ?int $Seats = null;

    #[ORM\Column]
    private ?bool $Electrical = null;

    #[ORM\ManyToOne(inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    /**
     * @var Collection<int, Travel>
     */
    #[ORM\OneToMany(targetEntity: Travel::class, mappedBy: 'car', orphanRemoval: true)]
    private Collection $travel;

    public function __construct()
    {
        $this->travel = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerialNumber(): ?string
    {
        return $this->SerialNumber;
    }

    public function setSerialNumber(string $SerialNumber): static
    {
        $this->SerialNumber = $SerialNumber;

        return $this;
    }

    public function getSerialDate(): ?\DateTime
    {
        return $this->SerialDate;
    }

    public function setSerialDate(\DateTime $SerialDate): static
    {
        $this->SerialDate = $SerialDate;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): static
    {
        $this->Type = $Type;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->Color;
    }

    public function setColor(string $Color): static
    {
        $this->Color = $Color;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->Brand;
    }

    public function setBrand(string $Brand): static
    {
        $this->Brand = $Brand;

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->Seats;
    }

    public function setSeats(int $Seats): static
    {
        $this->Seats = $Seats;

        return $this;
    }

    public function isElectrical(): ?bool
    {
        return $this->Electrical;
    }

    public function setElectrical(bool $Electrical): static
    {
        $this->Electrical = $Electrical;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    /**
     * @return Collection<int, Travel>
     */
    public function getTravel(): Collection
    {
        return $this->travel;
    }

    public function addTravel(Travel $travel): static
    {
        if (!$this->travel->contains($travel)) {
            $this->travel->add($travel);
            $travel->setCar($this);
        }

        return $this;
    }

    public function removeTravel(Travel $travel): static
    {
        if ($this->travel->removeElement($travel)) {
            // set the owning side to null (unless already changed)
            if ($travel->getCar() === $this) {
                $travel->setCar(null);
            }
        }

        return $this;
    }
}

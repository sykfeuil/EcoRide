<?php

namespace App\Entity;

use App\Repository\TravelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TravelRepository::class)]
class Travel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $availableSeats = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    private ?string $startingPlace = null;

    #[ORM\Column(length: 255)]
    private ?string $endingPlace = null;

    #[ORM\ManyToOne(inversedBy: 'travel')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Car $car = null;

    #[ORM\ManyToOne(inversedBy: 'travelAsDriver')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $driver = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'travelAsPassenger')]
    private Collection $passengers;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $startingDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $startingHour = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $endingDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $endingHour = null;

    #[ORM\Column]
    private ?int $currentState = null;

    public function __construct()
    {
        $this->passengers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAvailableSeats(): ?int
    {
        return $this->availableSeats;
    }

    public function setAvailableSeats(int $availableSeats): static
    {
        $this->availableSeats = $availableSeats;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStartingPlace(): ?string
    {
        return $this->startingPlace;
    }

    public function setStartingPlace(string $startingPlace): static
    {
        $this->startingPlace = $startingPlace;

        return $this;
    }

    public function getEndingPlace(): ?string
    {
        return $this->endingPlace;
    }

    public function setEndingPlace(string $endingPlace): static
    {
        $this->endingPlace = $endingPlace;

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): static
    {
        $this->car = $car;

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPassengers(): Collection
    {
        return $this->passengers;
    }

    public function addPassenger(User $passenger): static
    {
        if (!$this->passengers->contains($passenger)) {
            $this->passengers->add($passenger);
        }

        return $this;
    }

    public function removePassenger(User $passenger): static
    {
        $this->passengers->removeElement($passenger);

        return $this;
    }

    public function getStartingDate(): ?\DateTime
    {
        return $this->startingDate;
    }

    public function setStartingDate(\DateTime $startingDate): static
    {
        $this->startingDate = $startingDate;

        return $this;
    }

    public function getStartingHour(): ?\DateTime
    {
        return $this->startingHour;
    }

    public function setStartingHour(\DateTime $startingHour): static
    {
        $this->startingHour = $startingHour;

        return $this;
    }

    public function getEndingDate(): ?\DateTime
    {
        return $this->endingDate;
    }

    public function setEndingDate(\DateTime $endingDate): static
    {
        $this->endingDate = $endingDate;

        return $this;
    }

    public function getEndingHour(): ?\DateTime
    {
        return $this->endingHour;
    }

    public function setEndingHour(\DateTime $endingHour): static
    {
        $this->endingHour = $endingHour;

        return $this;
    }

    public function getCurrentState(): ?int
    {
        return $this->currentState;
    }

    public function setCurrentState(int $currentState): static
    {
        $this->currentState = $currentState;

        return $this;
    }
}

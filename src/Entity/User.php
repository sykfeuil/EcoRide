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

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cet adresse mail')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

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
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private $picture = null;

    #[ORM\Column]
    private ?bool $driver = false;

    #[ORM\Column]
    private ?bool $passenger = false;

    #[ORM\Column]
    private ?int $mark = 0;

    #[ORM\Column(nullable: true)]
    private ?bool $smoke = null;

    #[ORM\Column(nullable: true)]
    private ?bool $animal = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $preferences = null;

    #[ORM\Column]
    private ?int $credit = 20;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Car>
     */
    #[ORM\OneToMany(targetEntity: Car::class, mappedBy: 'User', orphanRemoval: true)]
    private Collection $cars;

    /**
     * @var Collection<int, Travel>
     */
    #[ORM\OneToMany(targetEntity: Travel::class, mappedBy: 'driver', orphanRemoval: true)]
    private Collection $travelAsDriver;

    /**
     * @var Collection<int, Travel>
     */
    #[ORM\ManyToMany(targetEntity: Travel::class, mappedBy: 'passengers')]
    private Collection $travelAsPassenger;

    /**
     * @var Collection<int, Opinion>
     */
    #[ORM\OneToMany(targetEntity: Opinion::class, mappedBy: 'driver', orphanRemoval: true)]
    private Collection $opinions;

    /**
     * @var Collection<int, Opinion>
     */
    #[ORM\OneToMany(targetEntity: Opinion::class, mappedBy: 'passenger', orphanRemoval: true)]
    private Collection $opinionsISubmitted;

    public function __construct()
    {
        $this->cars = new ArrayCollection();
        $this->travelAsDriver = new ArrayCollection();
        $this->travelAsPassenger = new ArrayCollection();
        $this->opinions = new ArrayCollection();
        $this->opinionsISubmitted = new ArrayCollection();
    }

    
    public function getId(): ?int
    {
        return $this->id;
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    /**
     * @see UserInterface
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function isDriver(): ?bool
    {
        return $this->driver;
    }

    public function setDriver(bool $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    public function isPassenger(): ?bool
    {
        return $this->passenger;
    }

    public function setPassenger(bool $passenger): static
    {
        $this->passenger = $passenger;

        return $this;
    }

    public function getMark(): ?float
    {
        return $this->mark;
    }

    public function setMark(float $mark): static
    {
        $this->mark = $mark;

        return $this;
    }

    public function isSmoke(): ?bool
    {
        return $this->smoke;
    }

    public function setSmoke(?bool $smoke): static
    {
        $this->smoke = $smoke;

        return $this;
    }

    public function isAnimal(): ?bool
    {
        return $this->animal;
    }

    public function setAnimal(?bool $animal): static
    {
        $this->animal = $animal;

        return $this;
    }

    public function getPreferences(): ?string
    {
        return $this->preferences;
    }

    public function setPreferences(?string $preferences): static
    {
        $this->preferences = $preferences;

        return $this;
    }

    public function getCredit(): ?int
    {
        return $this->credit;
    }

    public function setCredit(int $credit): static
    {
        $this->credit = $credit;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Car>
     */
    public function getCars(): Collection
    {
        return $this->cars;
    }

    public function addCar(Car $car): static
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
            $car->setUser($this);
        }

        return $this;
    }

    public function removeCar(Car $car): static
    {
        if ($this->cars->removeElement($car)) {
            // set the owning side to null (unless already changed)
            if ($car->getUser() === $this) {
                $car->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Travel>
     */
    public function getTravelAsDriver(): Collection
    {
        return $this->travelAsDriver;
    }

    public function addTravelAsDriver(Travel $travelAsDriver): static
    {
        if (!$this->travelAsDriver->contains($travelAsDriver)) {
            $this->travelAsDriver->add($travelAsDriver);
            $travelAsDriver->setDriver($this);
        }

        return $this;
    }

    public function removeTravelAsDriver(Travel $travelAsDriver): static
    {
        if ($this->travelAsDriver->removeElement($travelAsDriver)) {
            // set the owning side to null (unless already changed)
            if ($travelAsDriver->getDriver() === $this) {
                $travelAsDriver->setDriver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Travel>
     */
    public function getTravelAsPassenger(): Collection
    {
        return $this->travelAsPassenger;
    }

    public function addTravelAsPassenger(Travel $travelAsPassenger): static
    {
        if (!$this->travelAsPassenger->contains($travelAsPassenger)) {
            $this->travelAsPassenger->add($travelAsPassenger);
            $travelAsPassenger->addPassenger($this);
        }

        return $this;
    }

    public function removeTravelAsPassenger(Travel $travelAsPassenger): static
    {
        if ($this->travelAsPassenger->removeElement($travelAsPassenger)) {
            $travelAsPassenger->removePassenger($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Opinion>
     */
    public function getOpinions(): Collection
    {
        return $this->opinions;
    }

    public function addOpinion(Opinion $opinion): static
    {
        if (!$this->opinions->contains($opinion)) {
            $this->opinions->add($opinion);
            $opinion->setDriver($this);
        }

        return $this;
    }

    public function removeOpinion(Opinion $opinion): static
    {
        if ($this->opinions->removeElement($opinion)) {
            // set the owning side to null (unless already changed)
            if ($opinion->getDriver() === $this) {
                $opinion->setDriver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Opinion>
     */
    public function getOpinionsISubmitted(): Collection
    {
        return $this->opinionsISubmitted;
    }

    public function addOpinionsISubmitted(Opinion $opinionsISubmitted): static
    {
        if (!$this->opinionsISubmitted->contains($opinionsISubmitted)) {
            $this->opinionsISubmitted->add($opinionsISubmitted);
            $opinionsISubmitted->setPassenger($this);
        }

        return $this;
    }

    public function removeOpinionsISubmitted(Opinion $opinionsISubmitted): static
    {
        if ($this->opinionsISubmitted->removeElement($opinionsISubmitted)) {
            // set the owning side to null (unless already changed)
            if ($opinionsISubmitted->getPassenger() === $this) {
                $opinionsISubmitted->setPassenger(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CountryRepository")
 */
class Country
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"api-cases", "api-countries"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"api-cases", "api-countries"})
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Cases", mappedBy="country")
     */
    private $cases;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $disabled;

    public function __construct()
    {
        $this->cases = new ArrayCollection();
    }

    /**
     * @Groups({"api-countries"})
     */
    public function getTotalCases()
    {
        $nb = 0;
        foreach ($this->cases as $case) {
            $nb += $case->getCases();
        }
        return $nb;
    }

    /**
     * @Groups({"api-countries"})
     */
    public function getTotalDeaths()
    {
        $nb = 0;
        foreach ($this->cases as $case) {
            $nb += $case->getDeaths();
        }
        return $nb;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|Cases[]
     */
    public function getCases(): Collection
    {
        return $this->cases;
    }

    public function addDailyCase(Cases $dailyCase): self
    {
        if (!$this->cases->contains($dailyCase)) {
            $this->cases[] = $dailyCase;
            $dailyCase->setCountry($this);
        }

        return $this;
    }

    public function removeDailyCase(Cases $dailyCase): self
    {
        if ($this->cases->contains($dailyCase)) {
            $this->cases->removeElement($dailyCase);
            // set the owning side to null (unless already changed)
            if ($dailyCase->getCountry() === $this) {
                $dailyCase->setCountry(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function addCase(Cases $case): self
    {
        if (!$this->cases->contains($case)) {
            $this->cases[] = $case;
            $case->setCountry($this);
        }

        return $this;
    }

    public function removeCase(Cases $case): self
    {
        if ($this->cases->contains($case)) {
            $this->cases->removeElement($case);
            // set the owning side to null (unless already changed)
            if ($case->getCountry() === $this) {
                $case->setCountry(null);
            }
        }

        return $this;
    }
}

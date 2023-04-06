<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $type;

    #[ORM\Column(type: 'float')]
    private float $adjustment;

    #[ORM\Column(type: 'json')]
    private array $criteria = [];

    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: ProductPromotion::class, orphanRemoval: true)]
    private Collection $productPromotions;

    public function __construct()
    {
        $this->productPromotions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAdjustment(): float
    {
        return $this->adjustment;
    }

    public function setAdjustment(float $adjustment): self
    {
        $this->adjustment = $adjustment;

        return $this;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function setCriteria(array $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * @return Collection<int, ProductPromotion>
     */
    public function getProductPromotions(): Collection
    {
        return $this->productPromotions;
    }

    public function addProductPromotions(ProductPromotion $productPromotions): self
    {
        if (!$this->productPromotions->contains($productPromotions)) {
            $this->productPromotions->add($productPromotions);
            $productPromotions->setPromotion($this);
        }

        return $this;
    }

    public function removeProductPromotions(ProductPromotion $productPromotions): self
    {
        if ($this->productPromotions->removeElement($productPromotions)) {
            // set the owning side to null (unless already changed)
            if ($productPromotions->getPromotion() === $this) {
                $productPromotions->setPromotion(null);
            }
        }

        return $this;
    }
}

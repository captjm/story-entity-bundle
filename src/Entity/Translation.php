<?php

namespace CaptJM\Bundle\StoryEntityBundle\Entity;

use App\Repository\TranslationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class Translation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'translation', targetEntity: StoryInterface::class)]
    private Collection $stories;

    public function __construct()
    {
        $this->stories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStories(): Collection
    {
        return $this->stories;
    }

    public function addStory(StoryInterface $story): self
    {
        if (!$this->stories->contains($story)) {
            $this->stories->add($story);
            $story->setTranslation($this);
        }

        return $this;
    }

    public function removeStory(StoryInterface $story): self
    {
        if ($this->stories->removeElement($story)) {
            if ($story->getTranslation() === $this) {
                $story->setTranslation(null);
            }
        }

        return $this;
    }
}

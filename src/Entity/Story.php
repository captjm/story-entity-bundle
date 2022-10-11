<?php

namespace CaptJM\Bundle\StoryEntityBundle\Entity;

use App\Entity\Translation;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
class Story
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;
    #[ORM\Column(length: 2)]
    protected ?string $locale = null;
    #[ORM\Column(length: 255)]
    protected ?string $headline = null;
    #[ORM\Column(length: 255)]
    protected ?string $slug = null;
    #[ORM\Column(length: 255)]
    protected ?string $cover = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $extract = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $content = null;
    #[ORM\Column]
    protected ?bool $published = null;
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected ?DateTimeInterface $publishDate = null;
    #[ORM\ManyToOne(Translation::class)]
    protected Translation $translation;

    public function getTranslation(): Translation
    {
        return $this->translation;
    }

    public function setTranslation(Translation $translation): self
    {
        $this->translation = $translation;
        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }


    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(string $headline): self
    {
        $this->headline = $headline;

        return $this;
    }

    public function getExtract(): ?string
    {
        return $this->extract;
    }

    public function setExtract(?string $extract): self
    {
        $this->extract = $extract;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getPublishDate(): ?DateTimeInterface
    {
        return $this->publishDate;
    }

    public function setPublishDate(DateTimeInterface $publishDate): self
    {
        $this->publishDate = $publishDate;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\TranslationRepository;
use CaptJM\Bundle\StoryEntityBundle\Entity\TranslationInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class Translation implements TranslationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

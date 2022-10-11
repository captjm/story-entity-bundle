<?php

namespace CaptJM\Bundle\StoryEntityBundle\Entity;

interface StoryInterface
{

    public function setTranslation(Translation $translation): self;

    public function getTranslation();
}
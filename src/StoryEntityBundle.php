<?php

namespace CaptJM\Bundle\StoryEntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class StoryEntityBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
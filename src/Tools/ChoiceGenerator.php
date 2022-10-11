<?php
namespace CaptJM\Bundle\StoryEntityBundle\Tools;

class ChoiceGenerator
{
    public static function generate($str) : array {
        $locales = explode('|', $str);
        return array_combine(array_map('strtoupper', $locales), $locales);
    }
}
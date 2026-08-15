<?php

namespace Modules\Articles\Actions;

class CalculateReadingTimeAction
{
    public function execute(string $body): int
    {
        $text = strip_tags($body);
        $wordCount = str_word_count($text, 0);
        $minutes = (int) ceil($wordCount / 200);
        return max(1, $minutes);
    }
}
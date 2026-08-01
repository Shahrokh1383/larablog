<?php

namespace Modules\Content\Actions;

class CalculateReadingTimeAction
{
    /**
     * @param string $body The post body (HTML or plain text)
     * @return int minutes
     */
    public function execute(string $body): int
    {
        $text = strip_tags($body);
        $wordCount = str_word_count($text, 0);
        // Average reading speed: 200 words per minute
        $minutes = (int) ceil($wordCount / 200);
        return max(1, $minutes); // at least 1 minute
    }
}
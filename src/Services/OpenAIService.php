<?php

namespace Bitdigital\StatamicChatgpt\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    public function generateContentFromPrompt(string $title, string $type): string
    {
        config([
            'openai.api_key' => Settings::get('api_key'),
            'openai.organization' => Settings::get('organization'),
            'openai.request_timeout' => 120,
        ]);

        if ($type === 'full') {
            $lengthPrompt = 'You write full articles, at least 1,000 words long. Use titles and subtitles where appropriate.';
            $finalPrompt = 'Write me an SEO optimised article, with links to relevant websites included called: '.$title;
        } else {
            $lengthPrompt = 'Write me a single paragraph only.';
            $finalPrompt = 'Write me a single paragraph about: '.$title;
        }

        $promptPreface = Settings::get('prompt_preface', '');

        $tokenChars = strlen($lengthPrompt)
            + strlen($finalPrompt)
            + strlen($promptPreface)
            + strlen($title)
            + 100;

        $tokens = ceil($tokenChars / 4);

        $messages = [];
        if ($promptPreface) {
            $messages[] = ['role' => 'system', 'content' => $promptPreface];
        }
        $messages[] = ['role' => 'system', 'content' => $lengthPrompt];
        $messages[] = ['role' => 'system', 'content' => 'Return the response as HTML'];
        $messages[] = ['role' => 'user', 'content' => $finalPrompt];

        $result = OpenAI::chat()->create([
            'model' => Settings::get('model'),
            'max_tokens' => Settings::get('max_tokens', 4096) - $tokens,
            'messages' => $messages,
        ]);

        return $this->cleanResult($result['choices'][0]['message']['content'] ?? '');
    }

    public function cleanResult($content = ''): string
    {
        $content = mb_convert_encoding($content, 'UTF-8');
        $content = trim($content, '"');
        $content = str_replace("'", "'", $content);
        $content = str_replace("â", "'", $content);
        $content = str_replace('  ', ' ', $content);
        $content = str_replace('â', '&', $content);
        $content = str_replace("'", '-', $content);
        $content = str_replace('', '', $content);

        return $content;
    }
}

<?php

namespace Bitdigital\StatamicChatgpt\Controllers;

use Bitdigital\StatamicChatgpt\Services\OpenAIService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenAI\Exceptions\RateLimitException;
use OpenAI\Laravel\Exceptions\ApiKeyIsMissing;
use Psr\Http\Message\ResponseInterface;
use Tiptap\Editor;

class StatamicChatgptBardController
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['full', 'paragraph'])],
            'promptText' => ['required'],
        ]);

        // Load our OpenAI service
        $openAIService = new OpenAIService;

        // Fire the prompt
        try {
            $response = $openAIService->generateContentFromPrompt($validated['promptText'], $validated['type']);
        } catch (ApiKeyIsMissing $e) {
            return ['error' => 'You haven\'t added your API key yet, or you are using an API key not compatible with the model selected. Go to Tools → Addons → ChatGPT to add your API key.'];
        } catch (RateLimitException $e) {
            return ['error' => $this->openAiErrorMessage($e)];
        } catch (Exception $e) {
            $message = $e->getMessage();
            if ($message === 'you must provide a model parameter') {
                return ['error' => 'You haven\'t selected a model from the settings screen. Go to Tools → Addons → ChatGPT and choose a model to use.'];
            }

            if (str_starts_with($message, 'cURL error 28: Operation timed out after')) {
                return ['error' => 'It looks like the OpenAI API timed out. This can sometimes happen, try it again. If the error persists please check OpenAI status: https://status.openai.com/'];
            }

            return ['error' => $this->openAiErrorMessage($e)];
        }

        // Create an editor instance and load the OpenAI response into the editor to get the HTML, then return it to our Vue component
        $editorHTML = (new Editor)
            ->setContent($response)
            ->getHTML();

        return ['text' => $editorHTML];
    }

    private function openAiErrorMessage(Exception $exception): string
    {
        if (! property_exists($exception, 'response') || ! $exception->response instanceof ResponseInterface) {
            return $exception->getMessage();
        }

        $body = json_decode((string) $exception->response->getBody(), true);
        $error = $body['error'] ?? null;

        if (($error['code'] ?? null) === 'insufficient_quota') {
            return 'OpenAI-kontoen har ikke mer kredit igjen. Dette er ikke en forespørselsgrense — legg til betaling eller forhøy bruksgrensen på https://platform.openai.com/settings/organization/billing';
        }

        if (! empty($error['message'])) {
            return $error['message'];
        }

        return $exception->getMessage();
    }
}

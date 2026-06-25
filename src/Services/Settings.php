<?php

namespace Bitdigital\StatamicChatgpt\Services;

use Statamic\Facades\Addon;

class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Addon::get('bitdigitalcodes/statamic-chatgpt')?->settings()?->get($key);

        if ($value !== null && $value !== '') {
            return $value;
        }

        return config("statamic-chatgpt.{$key}", $default);
    }
}

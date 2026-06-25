<?php

namespace Bitdigital\StatamicChatgpt;

use Bitdigital\StatamicChatgpt\Controllers\StatamicChatgptBardController;
use Illuminate\Support\Facades\Route;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/statamic-chatgpt.php', 'statamic-chatgpt'
        );

        $this->publishes([
            __DIR__.'/../config/statamic-chatgpt.php' => config_path('statamic-chatgpt.php'),
        ], 'statamic-chatgpt');

        $this->registerActionRoutes(function () {
            Route::post('/', [StatamicChatgptBardController::class, 'handle']);
        });
    }
}

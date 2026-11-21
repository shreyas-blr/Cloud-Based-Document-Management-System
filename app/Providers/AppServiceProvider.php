<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use Illuminate\Contracts\Foundation\Application;

use Illuminate\Filesystem\FilesystemAdapter;

use Illuminate\Support\Facades\Storage;

use League\Flysystem\Filesystem;

use Spatie\Dropbox\Client as DropboxClient;

use Spatie\FlysystemDropbox\DropboxAdapter;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paginator::useBootstrap();
        Storage::extend('dropbox', function (Application $app, array $config) {
            $token = $config['authorization_token'] ?? null;
            if (empty($token)) {
                return Storage::disk('local');
            }

            try {
                $adapter = new DropboxAdapter(new DropboxClient($token));

                return new FilesystemAdapter(
                    new Filesystem($adapter, $config),
                    $adapter,
                    $config
                );
            } catch (\Throwable $e) {
                return Storage::disk('local');
            }
        });
    }
}

<?php namespace Pensoft\ServicesProvider;

use Backend;
use System\Classes\MarkupManager;
use System\Classes\PluginBase;
use Event;
use Illuminate\Support\Facades\Storage;

class Plugin extends PluginBase
{
    public $require = [
        'rainlab.location',
        'rainlab.user',
        'pensoft.servicesprovider'
    ];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'ServicesProvider',
            'description' => 'No description provided yet...',
            'author'      => 'Pensoft',
            'icon'        => 'icon-leaf'
        ];
    }

    public function register(): void
    {
    }

    public function registerMarkupTags(): array
    {
        return [
            'filters' => [
                'md_indent' => function (?string $string, string $indent = ''): string {
                    if (!is_string($string)) {
                        return '';
                    }
                    $lines = explode("\n", $string);
                    return implode("\n", array_map(fn($line) => $indent . $line, $lines));
                },
            ],
        ];
    }

    public function boot(): void
    {
        Event::listen('rainlab.user.getNotificationVars', function ($user): array {
            $code = implode('!', [$user->id, $user->persist_code ?? hash('sha256', $user->email . $user->id)]);
            $link = url('/profile') . '?activate=' . $code . '&code=' . $code;

            return ['link' => $link, 'surname' => $user->surname];
        });

        MarkupManager::instance()->registerCallback(function ($manager): void {
            $manager->registerFunctions([
                'getAWSFile' => function ($path): string|false {
                    if ($path) {
                        $removeStr = 'https://s3-' . env('AWS_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
                        $key = str_replace($removeStr, '', $path);
                        $client = Storage::disk('s3')->getClient();

                        $command = $client->getCommand('GetObject', [
                            'Bucket' => env('AWS_BUCKET'),
                            'Key' => $key
                        ]);
                        $request = $client->createPresignedRequest($command, '+20 minutes');
                        $presignedUrl = (string)$request->getUri();

                        return $presignedUrl;
                    }
                    return false;
                },
            ]);
        });
    }

    public function registerComponents(): array
    {
        return [];
    }

    public function registerPermissions(): array
    {
        return [];
    }

    public function registerNavigation(): array
    {
        return [];
    }
}
<?php namespace Pensoft\ServicesProvider;

use Backend;
use System\Classes\MarkupManager;
use System\Classes\PluginBase;
use Event;
use Illuminate\Support\Facades\Storage;

/**
 * ServicesProvider Plugin Information File
 */
class Plugin extends PluginBase
{
	/**
	 * @var array Plugin dependencies
	 */
	public $require = [
		'rainlab.location',
		'rainlab.user',
		'pensoft.servicesprovider'
	];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'ServicesProvider',
            'description' => 'No description provided yet...',
            'author'      => 'Pensoft',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        Event::listen('rainlab.user.getNotificationVars', function ($user) {
            $code = implode('!', [$user->id, $user->getActivationCode()]);
            $link = url('/profile') . '?activate=' . $code . '&code=' . $code;

            return ['link' => $link, 'surname' => $user->surname];
        });

        MarkupManager::instance()->registerCallback(function ($manager) {
            $manager->registerFunctions([
                'getAWSFile' => function ($path) {
                    if($path){
                        $removeStr = 'https://s3-' . env('AWS_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
                        $key = str_replace($removeStr, '', $path);
                        $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();

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

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Pensoft\ServicesProvider\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'pensoft.servicesprovider.some_permission' => [
                'tab' => 'ServicesProvider',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'servicesprovider' => [
                'label'       => 'ServicesProvider',
                'url'         => Backend::url('pensoft/servicesprovider/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['pensoft.servicesprovider.*'],
                'order'       => 500,
            ],
        ];
    }
}

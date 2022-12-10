<?php

namespace Dnsinyukov\SyncCalendars;

use Dnsinyukov\SyncCalendars\Providers\GoogleProvider;
use Dnsinyukov\SyncCalendars\Providers\ProviderInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Manager;

class CalendarManager extends Manager
{
    /**
     * Create an instance of the specified driver.
     * @throws BindingResolutionException
     */
    protected function createGoogleDriver(): ProviderInterface
    {
        $config = $this->config->get('services.google');

        return $this->buildProvider(GoogleProvider::class, $config);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function buildProvider($provider, $config): ProviderInterface
    {
        return new $provider(
            $this->container->make('request'),
            $config['client_id'],
            $config['client_secret'],
            $config['redirect_uri'],
            $config['scopes']
        );
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        throw new \InvalidArgumentException('No Calendar driver was specified.');
    }
}

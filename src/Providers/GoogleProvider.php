<?php

namespace Dnsinyukov\SyncCalendars\Providers;

use Dnsinyukov\SyncCalendars\Providers\Synchronizers\GoogleSynchronizer;
use Dnsinyukov\SyncCalendars\Token;
use Dnsinyukov\SyncCalendars\Account;
use Dnsinyukov\SyncCalendars\TokenFactory;
use Google\Client;
use Google\Service\Oauth2\Userinfo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class GoogleProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $providerName = 'google';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @return string
     */
    public function createAuthUrl(): string
    {
        return $this->getHttpClient()->createAuthUrl();
    }

    /**
     * @param string $code
     * @return array
     */
    protected function fetchAccessTokenWithAuthCode(string $code): array
    {
        return $this->getHttpClient()->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * @return array
     */
    protected function getBasicProfile($credentials)
    {
        $jwt = explode('.', $credentials['id_token']);

        // Extract the middle part, base64 decode it, then json_decode it
        return json_decode(base64_decode($jwt[1]), true);
    }

    /**
     * @param Userinfo $userProfile
     * @return void
     */
    protected function toUser($userProfile)
    {
        return tap(new Account(), function ($account) use ($userProfile) {
            $account->setProviderId($userProfile['sub']);
            $account->setName($userProfile['name']);
            $account->setEmail($userProfile['email']);
            $account->setPicture($userProfile['picture']);
        });
    }

    /**
     * @param array $credentials
     * @return Token
     */
    protected function createToken(array $credentials): Token
    {
        return TokenFactory::create([
            'id_token' => $credentials['id_token'],
            'access_token' => $credentials['access_token'],
            'refresh_token' => $credentials['refresh_token'],
            'scope' => $credentials['scope'],
            'created_at' => $credentials['created'],
            'expires_at' => $credentials['expires_in'],
        ]);
    }

    /**
     * @return Client
     */
    protected function getHttpClient(): Client
    {
        if (is_null($this->httpClient)) {

            $this->httpClient = new \Google\Client();
            $this->httpClient->setApplicationName(config('app.name'));
            $this->httpClient->setClientId($this->clientId);
            $this->httpClient->setClientSecret($this->clientSecret);
            $this->httpClient->setRedirectUri($this->redirectUrl);
            $this->httpClient->setScopes($this->scopes);
            $this->httpClient->setApprovalPrompt(config('services.google.approval_prompt'));
            $this->httpClient->setAccessType(config('services.google.access_type'));
            $this->httpClient->setIncludeGrantedScopes(config('services.google.include_granted_scopes'));

            // Add request query to the state
            $this->httpClient->setState(
                Crypt::encrypt($this->request->all())
            );
        }

        return $this->httpClient;
    }

    /**
     * @param string $resource
     * @param Account $account
     * @param array $options
     *
     * @return mixed
     */
    public function synchronize(string $resource, Account $account, array $options = [])
    {
        $resource = Str::ucfirst($resource);

        $method = 'synchronize' . Str::plural($resource);

        $synchronizer = $this->getSynchronizer();

        if (method_exists($synchronizer, $method) === false) {
            throw new \InvalidArgumentException('Method is not allowed.', 400);
        }

        return call_user_func([$synchronizer, $method], $account, $options);
    }

    /**
     * @return GoogleSynchronizer
     */
    public function getSynchronizer(): GoogleSynchronizer
    {
        if (empty($this->synchronizer)) {
            $this->synchronizer = app(GoogleSynchronizer::class, [
                'provider' => $this
            ]);
            $this->synchronizer->setHttpClient($this->getHttpClient()->getHttpClient());
        }

        return $this->synchronizer;
    }
}

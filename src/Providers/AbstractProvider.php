<?php

namespace Dnsinyukov\SyncCalendars\Providers;

use Dnsinyukov\SyncCalendars\Token;
use Dnsinyukov\SyncCalendars\Account;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    protected $providerName;

    /**
     * The HTTP request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The HTTP Client instance.
     *
     * @var $httpClient
     */
    protected $httpClient;

    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The request user
     *
     * @var Account|null
     */
    protected $account;

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * @var string
     */
    protected $version;

    /**
     * @var array
     */
    protected $config;

    /**
     * Create a new provider instance.
     *
     * @param Request $request
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param array $scopes
     */
    public function __construct(Request $request, string $clientId, string $clientSecret, string $redirectUrl, array $scopes = [])
    {
        $this->request = $request;
        $this->clientId = $clientId;
        $this->redirectUrl = $redirectUrl;
        $this->clientSecret = $clientSecret;
        $this->scopes = $scopes;
        $this->config = config('services.' . $this->getProviderName());
    }

    /**
     * @return RedirectResponse
     * @throws \Exception
     */
    public function redirect(): RedirectResponse
    {
        $this->request->query->add(['state' => $this->getState()]);

        if ($user = $this->request->user($this->getConfig('guard', 'web'))) {
            $this->request->query->add(['user_id' => $user->getKey()]);
        }

        return new RedirectResponse($this->createAuthUrl());
    }

    /**
     * @return Account
     */
    public function callback(): Account
    {
        if (isset($this->account)) {
            return $this->account;
        }

        $state = $this->request->get('state');

        try {
            if (isset($state)) {
                $state = Crypt::decrypt($state);
            }

            $credentials = $this->fetchAccessTokenWithAuthCode(
                $this->request->get('code', '')
            );

            $this->account = $this->toUser($this->getBasicProfile($credentials));
        } catch (\Exception $exception) {
            report($exception);
            throw new \InvalidArgumentException($exception->getMessage());
        }

        $token = $this->createToken($credentials);
        $userId = $state['user_id'] ?? null;

        return $this->account->setUserId($userId)->setToken($token);
    }

    /**
     * Get an instance of the HTTP client.
     *
     * @return Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    /**
     * @throws \Exception
     */
    protected function getState(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * @return string
     */
    public function getScopeSeparator(): string
    {
        return $this->scopeSeparator;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @return array
     */
    public function getConfig(string $key, string $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (! method_exists($this->httpClient, $method)) {
            throw new \InvalidArgumentException("Method Not Allowed ${method}");
        }

        return call_user_func_array([$this->httpClient, $method], $args);
    }

    abstract protected function createAuthUrl();
    abstract protected function fetchAccessTokenWithAuthCode(string $code);
    abstract protected function getBasicProfile($credentials);
    abstract protected function toUser($userProfile);
    abstract protected function createToken(array $credentials): Token;
}

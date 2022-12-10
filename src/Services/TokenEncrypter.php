<?php

namespace Dnsinyukov\SyncCalendars\Services;

use Illuminate\Contracts\Encryption\Encrypter;

use Firebase\JWT\JWT;

class TokenEncrypter
{
    /**
     * @var Encrypter
     */
    protected $encrypter;
    /**
     * @var string
     */
    protected $alg = 'HS512';

    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * @param array $payload
     * @return string
     * @throws \Exception
     */
    public function encode(array $payload): string
    {
        $config = config('app');

        $tokenId = base64_encode(random_bytes(16));
        $issuedAt = new \DateTimeImmutable();

        $jwtPayload = [
            'iat'  => $issuedAt->getTimestamp(),
            'jti'  => $tokenId,
            'iss'  => $config['name'],
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $payload['expires_at']->getTimestamp(),
            'data' => [
                'access_token' => $payload['access_token'],
                'refresh_token' => $payload['refresh_token'],
                'provider' => $payload['provider'],
                'scopes' => $payload['scopes'],
                'email' => $payload['email'],
                'account_id' => $payload['account_id'],
            ]
        ];

        return JWT::encode($jwtPayload, $config['key'], $this->alg);
    }

    public function decode(string $payload): array
    {
        return (array) JWT::decode($payload, config('app.key'));
    }
}

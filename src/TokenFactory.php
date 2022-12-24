<?php

namespace Dnsinyukov\SyncCalendars;

class TokenFactory
{
    /**
     * @param array $credentials
     *
     * @return Token
     */
    public static function create(array $credentials): Token
    {
        return tap(new Token(), function ($token) use ($credentials) {
            $token->setTokenId($credentials['id_token'])
                ->setAccessToken($credentials['access_token'])
                ->setRefreshToken($credentials['refresh_token'])
                ->setScopes($credentials['scope'])
                ->setCreatedAt($credentials['created_at'])
                ->setExpiresAt($credentials['expires_at']);
        });
    }
}

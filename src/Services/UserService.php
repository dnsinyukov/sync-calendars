<?php

namespace Dnsinyukov\SyncCalendars\Services;

use Dnsinyukov\SyncCalendars\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    protected $encrypter;

    /**
     * @param TokenEncrypter $encrypter
     */
    public function __construct(TokenEncrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * @param User $user
     * @param string $provider
     * @return void
     * @throws \Exception
     */
    public function saveFromUser(User $user, string $provider)
    {
        $payload = [
            'account_id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'picture' => $user->getPicture(),
            'provider' => $provider,
            'access_token' => $user->getAccessToken(),
            'refresh_token' => $user->getRefreshToken(),
            'scopes' => implode(' ', $user->getScopes()),
            'expires_at' => $user->getExpiresAt(),
            'created_at' => now(),
            'updated_at' => now()
        ];

        $payload['token'] = $this->encrypter->encode($payload);

        unset($payload['access_token'], $payload['refresh_token'], $payload['scopes']);

        if (DB::table('oauth2_accounts')
            ->where('account_id', $payload['account_id'])
            ->where('provider', $provider)
            ->exists()
        ) {
            unset($payload['created_at']);

            DB::table('oauth2_accounts')
                ->where('account_id', $payload['account_id'])
                ->where('provider', $provider)
                ->update($payload);
        } else {
            DB::table('oauth2_accounts')->insert($payload);
        }
    }
}

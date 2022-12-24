<?php

namespace Dnsinyukov\SyncCalendars;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class Token implements Arrayable
{
    /**
     * @var string
     */
    protected $tokenId;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $refreshToken;

    /**
     * @var array
     */
    protected $scopes;

    /**
     * @var Carbon
     */
    protected $expiresAt;

    /**
     * @var Carbon
     */
    protected $createdAt;

    /**
     * @param string $accessToken
     * @return Token
     */
    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @param string $tokenId
     * @return Token
     */
    public function setTokenId(string $tokenId): static
    {
        $this->tokenId = $tokenId;

        return $this;
    }

    /**
     * @param array|string $scopes
     */
    public function setScopes($scopes, string $scopeSeparator = ' '): static
    {
        if (is_string($scopes)) {
            $this->scopes = explode($scopeSeparator, $scopes);
        } else if (is_array($scopes)){
            $this->scopes = $scopes;
        }

        return $this;
    }

    /**
     * @param string $refreshToken
     * @return Token
     */
    public function setRefreshToken(string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @param Carbon|int $expiresAt
     */
    public function setExpiresAt($expiresAt): static
    {
        if (is_int($expiresAt)) {
            $this->expiresAt = Carbon::now()->addSeconds($expiresAt);
        } else {
            $this->expiresAt = Carbon::parse($expiresAt);
        }

        return $this;
    }

    /**
     * @param int|Carbon $createdAt
     * @return Token
     */
    public function setCreatedAt($createdAt): static
    {
        $this->createdAt = Carbon::parse($createdAt);

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        $expiration = $this->expiresAt;

        return !is_null($expiration) && Carbon::now()->greaterThanOrEqualTo($expiration);
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id_token' => $this->tokenId,
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'scope' => $this->scopes,
            'expires_at' => $this->expiresAt,
            'created_at' => $this->createdAt
        ];
    }
}

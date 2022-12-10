<?php

namespace Dnsinyukov\SyncCalendars;

use Carbon\Carbon;

class User
{
    /**
     * The unique identifier for the user.
     *
     * @var mixed
     */
    protected $id;

    /**
     * The user's full name.
     *
     * @var string
     */
    protected $name;

    /**
     * The user's e-mail address.
     *
     * @var string
     */
    protected $email;

    /**
     * The user's avatar image URL.
     *
     * @var string
     */
    protected $picture;

    /**
     * The user's access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * The refresh token that can be exchanged for a new access token.
     *
     * @var string
     */
    protected $refreshToken;

    /**
     * Date of the access token is valid for.
     *
     * @var Carbon
     */
    protected $expiresAt;

    /**
     * The scopes the user authorized. The approved scopes may be a subset of the requested scopes.
     *
     * @var array
     */
    protected $scopes;

    /**
     * @var string
     */
    protected $redirectCallback;

    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->accessToken = $token;

        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     *
     * @param  string  $refreshToken
     * @return $this
     */
    public function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     *
     * @param $expiresAt
     * @return $this
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Set the scopes that were approved by the user during authentication.
     *
     * @param $scopes
     * @return $this
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return User
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPicture(): string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     * @return User
     */
    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @return Carbon
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param string $redirectCallback
     * @return User
     */
    public function setRedirectCallback(string $redirectCallback): self
    {
        $this->redirectCallback = $redirectCallback;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectCallback(): string
    {
        return $this->redirectCallback;
    }
}

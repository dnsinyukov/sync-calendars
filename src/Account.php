<?php

namespace Dnsinyukov\SyncCalendars;

class Account
{
    /**
     * @var int
     */
    protected $id;

    /**
     * The unique identifier for the user.
     *
     * @var mixed
     */
    protected $providerId;

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
     * The user's token.
     *
     * @var Token
     */
    protected $token;

    /**
     * @var string|null
     */
    protected $syncToken;

    /**
     * @var int
     */
    protected $userId;


    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Account
     */
    public function setId(int $id): self
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
     * @return Account
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
     * @return Account
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
     * @return Account
     */
    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * Set the token on the user.
     *
     * @param  Token  $token
     * @return $this
     */
    public function setToken(Token $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param string $providerId
     * @return Account
     */
    public function setProviderId(string $providerId): self
    {
        $this->providerId = $providerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getProviderId(): string
    {
        return $this->providerId;
    }

    /**
     * @param string|null $syncToken
     * @return Account
     */
    public function setSyncToken(?string $syncToken): self
    {
        $this->syncToken = $syncToken;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSyncToken(): ?string
    {
        return $this->syncToken;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param ?int $userId
     * @return Account
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}

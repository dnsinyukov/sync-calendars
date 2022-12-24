<?php

namespace Dnsinyukov\SyncCalendars\Repositories;

class AccountRepository extends DBRepository
{
    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'calendar_accounts';
    }
}

<?php

namespace Dnsinyukov\SyncCalendars\Providers;

use Dnsinyukov\SyncCalendars\Account;

interface ProviderInterface
{
    public function callback();
    public function synchronize(string $resource, Account $account);
}

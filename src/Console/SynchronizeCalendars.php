<?php

namespace Dnsinyukov\SyncCalendars\Console;

use Dnsinyukov\SyncCalendars\Account;
use Dnsinyukov\SyncCalendars\CalendarManager;
use Dnsinyukov\SyncCalendars\Providers\GoogleProvider;
use Dnsinyukov\SyncCalendars\Repositories\AccountRepository;
use Dnsinyukov\SyncCalendars\TokenFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SynchronizeCalendars extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'synchronize:calendars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Calendars';

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle()
    {
        /** @var GoogleProvider $provider */
        $provider = app(CalendarManager::class)->driver('google');

        $accounts = app(AccountRepository::class)->get();

        foreach ($accounts as $accountModel) {
            $provider->synchronize('Calendar', tap(new Account(), function ($account) use ($accountModel) {

                $token = Crypt::decrypt($accountModel->token);
                $syncToken = '';

                if (isset($accountModel->sync_token)) {
                    $syncToken = Crypt::decryptString($accountModel->sync_token);
                }

                $account
                    ->setId($accountModel->id)
                    ->setProviderId($accountModel->provider_id)
                    ->setUserId($accountModel->user_id)
                    ->setName($accountModel->name)
                    ->setEmail($accountModel->email)
                    ->setPicture($accountModel->picture)
                    ->setSyncToken($syncToken)
                    ->setToken(TokenFactory::create($token));
            }));
        }
    }
}

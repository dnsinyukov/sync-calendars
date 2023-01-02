<?php

namespace Dnsinyukov\SyncCalendars\Console;

use Carbon\Carbon;
use Dnsinyukov\SyncCalendars\Account;
use Dnsinyukov\SyncCalendars\CalendarManager;
use Dnsinyukov\SyncCalendars\Providers\GoogleProvider;
use Dnsinyukov\SyncCalendars\Repositories\AccountRepository;
use Dnsinyukov\SyncCalendars\Repositories\CalendarRepository;
use Dnsinyukov\SyncCalendars\TokenFactory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Crypt;

class SynchronizeEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'synchronize:events {accountId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Events';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $accountId = $this->argument('accountId');

        $accountModel = app(AccountRepository::class)->find($accountId);

        throw_if(empty($accountModel), ModelNotFoundException::class);

        /** @var GoogleProvider $provider */
        $provider = app(CalendarManager::class)->driver('google');

        $calendars = app(CalendarRepository::class)->getByAttributes([
            'account_id' => $accountId
        ]);

        $account = tap(new Account(), function ($account) use ($accountModel) {

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
        });

        foreach ($calendars as $calendar) {
            $options = ['calendarId' => $calendar->provider_id];

            if (isset($calendar->sync_token)) {
                $options['syncToken'] = Crypt::decryptString($calendar->sync_token);
            }

            $provider->synchronize('Event', $account, $options);
        }
    }
}

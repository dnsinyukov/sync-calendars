<?php

namespace Dnsinyukov\SyncCalendars\Console;

use Carbon\Carbon;
use Dnsinyukov\SyncCalendars\Account;
use Dnsinyukov\SyncCalendars\CalendarManager;
use Dnsinyukov\SyncCalendars\Providers\GoogleProvider;
use Dnsinyukov\SyncCalendars\Repositories\AccountRepository;
use Dnsinyukov\SyncCalendars\TokenFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class SynchronizeEvents extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'synchronize:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Events';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var GoogleProvider $provider */
        $provider = app(CalendarManager::class)->driver('google');

        $accounts = app(AccountRepository::class)->get();

        foreach ($accounts as $accountModel) {
            $provider->synchronize('Event', tap(new Account(), function ($account) use ($accountModel) {

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
            }), [
                'calendarId' => 'dnsinyukov@gmail.com',
//                'syncToken' => Crypt::decryptString('eyJpdiI6ImgxRWZ2RFkxRVZUc0FTK2pmWThqNVE9PSIsInZhbHVlIjoidE8yZ3ZKOXFOMGNtWkhKalAwbUl5dVIvVmRGa2t0SFg3anZQVjV5MmxFZlZ4bE5XVTNoS1M1MFFjSWNxNU9mTyIsIm1hYyI6ImIxODdhYmZhMmQwYjQwYTIxZTE0N2YzMGY0YzY2MjBlM2Y4MmRhNDIwMzA4YmExMzE2YjYzMTE1OWE1OTNhMmUiLCJ0YWciOiIifQ==')
            ]);

        }
    }
}

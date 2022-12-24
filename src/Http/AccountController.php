<?php

namespace Dnsinyukov\SyncCalendars\Http;

use Dnsinyukov\SyncCalendars\CalendarManager;
use Dnsinyukov\SyncCalendars\Providers\ProviderInterface;
use Dnsinyukov\SyncCalendars\Services\AccountService;
use Dnsinyukov\SyncCalendars\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AccountController extends Controller
{
    protected $manager;

    /**
     * @param CalendarManager $manager
     */
    public function __construct(CalendarManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @param string $driver
     * @return RedirectResponse
     */
    public function auth(Request $request, string $driver): RedirectResponse
    {
        /** @var ProviderInterface $provider */
        $provider = $this->manager->driver($driver);

        $authUser = $request->user(
            $provider->getConfig('guard', 'web')
        );

        if (empty($authUser)) {
            abort(403);
        }

//        dd(
//            $this->manager->driver($driver)->synchronize('Calendar', [2])
//        );
        try {
            return $provider->redirect();
        } catch (\InvalidArgumentException $exception) {
            report($exception);

            abort(400, $exception->getMessage());
        }
    }

    /**
     * @param string $driver
     * @return RedirectResponse
     */
    public function callback(string $driver): RedirectResponse
    {
        /** @var ProviderInterface $provider */
        $provider = $this->manager->driver($driver);

        /** @var Account $account */
        $account = $provider->callback();

        $accountId = app(AccountService::class)->createFrom($account, $driver);

        $account->setId($accountId);

        $provider->synchronize('Calendar', $account);

        return redirect()->to(
          config('services.' . $driver . '.redirect_callback', '/')
        );
    }
}

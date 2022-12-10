<?php

namespace Dnsinyukov\SyncCalendars\Http;

use Dnsinyukov\SyncCalendars\CalendarManager;
use Dnsinyukov\SyncCalendars\Services\UserService;
use Dnsinyukov\SyncCalendars\User;
use Illuminate\Http\RedirectResponse;
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
     * @param string $driver
     * @return RedirectResponse
     */
    public function auth(string $driver): RedirectResponse
    {
        try {
            return $this->manager->driver($driver)->redirect();
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
        /** @var User $user */
        $user = $this->manager->driver($driver)->getUser();

        app(UserService::class)->saveFromUser($user, $driver);

        return redirect()->to($user->getRedirectCallback() ?? '/');

    }
}

<?php

namespace Dnsinyukov\SyncCalendars\Providers\Synchronizers;

use Carbon\Carbon;
use Dnsinyukov\SyncCalendars\Account;
use Dnsinyukov\SyncCalendars\Providers\ProviderInterface;
use Dnsinyukov\SyncCalendars\Repositories\AccountRepository;
use Dnsinyukov\SyncCalendars\Repositories\CalendarRepository;
use Dnsinyukov\SyncCalendars\Repositories\EventRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Crypt;

class GoogleSynchronizer
{
    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     * @throws GuzzleException
     */
    protected function call(string $method, string $uri = '', array $options = []): array
    {
        $response = $this->httpClient()->request($method, $uri, [
            'headers' => $this->headers($options['headers'] ?? []),
            'query' => $options['query'],
        ]);

        $body = (string) $response->getBody();

        return json_decode($body, true);
    }

    /**
     * @throws GuzzleException
     */
public function synchronizeCalendars(Account $account, array $options = [])
{
    $token = $account->getToken();
    $accountId = $account->getId();
    $syncToken = $account->getSyncToken();

    if ($token->isExpired()) {
        return false;
    }

    $query = array_merge([
        'maxResults' => 100,
        'minAccessRole' => 'owner', // The user can read and modify events and access control lists.
    ], $options['query'] ?? []);

    if (isset($syncToken)) {
        $query = [
            'syncToken' => $syncToken,
        ];
    }

    $body = $this->call('GET', "/calendar/{$this->provider->getVersion()}/users/me/calendarList", [
        'headers' => ['Authorization' => 'Bearer ' . $token->getAccessToken()],
        'query' => $query
    ]);

    $nextSyncToken = $body['nextSyncToken'];
    $calendarIterator = new \ArrayIterator($body['items']);

    /** @var CalendarRepository $calendarRepository */
    $calendarRepository = app(CalendarRepository::class);

    // Check user calendars
    $providersIds = $calendarRepository
        ->setColumns(['provider_id'])
        ->getByAttributes(['account_id' => $accountId, 'provider_type' => $this->provider->getProviderName()])
        ->pluck('provider_id');

    $now = now();

    while ($calendarIterator->valid()) {
        $calendar = $calendarIterator->current();
        $calendarId = $calendar['id'];

        // Delete account calendar by ID
        if (key_exists('deleted', $calendar) && $calendar['deleted'] === true && $providersIds->contains($calendarId)) {
            $calendarRepository->deleteWhere([
                'provider_id' => $calendarId,
                'provider_type' => $this->provider->getProviderName(),
                'account_id' => $accountId,
            ]);

        // Update account calendar by ID
        } else if ($providersIds->contains($calendarId)) {
            $calendarRepository->updateByAttributes(
                [
                    'provider_id' => $calendarId,
                    'provider_type' => $this->provider->getProviderName(),
                    'account_id' => $accountId,
                ],
                [
                    'summary' => $calendar['summary'],
                    'timezone' => $calendar['timeZone'],
                    'description' => $calendar['description'] ?? null,
                    'updated_at' => $now,
                ]
            );
        // Create account calendar
        } else {
            $calendarRepository->insert([
                'provider_id' => $calendarId,
                'provider_type' => $this->provider->getProviderName(),
                'account_id' => $accountId,
                'summary' => $calendar['summary'],
                'timezone' => $calendar['timeZone'],
                'description' => $calendar['description'] ?? null,
                'selected' => $calendar['selected'] ?? false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $calendarIterator->next();
    }

    $this->getAccountRepository()->updateByAttributes(
        ['id' => $accountId],
        ['sync_token' => Crypt::encryptString($nextSyncToken), 'updated_at' => $now]
    );
}

    /**
     * @throws GuzzleException
     */
    public function synchronizeEvents(Account $account, array $options = [])
    {
        $token = $account->getToken();
        $accountId = $account->getId();
        $calendarId = $options['calendarId'] ?? 'primary';
        $pageToken = $options['pageToken'] ?? null;
        $syncToken = $options['syncToken'] ?? null;

        $now = now();

        $query = [
            'maxResults' => 25,
            'timeMin' => $now->copy()->startOfMonth()->toRfc3339String(),
            'timeMax' => $now->copy()->addMonth()->toRfc3339String()
        ];

        if ($token->isExpired()) {
            return false;
        }

        if (isset($syncToken)) {
            $query = [
                'syncToken' => $syncToken,
            ];
        }

        /** @var EventRepository $eventRepository */
        $eventRepository = app(EventRepository::class);

        do {
            if (isset($pageToken) && empty($syncToken)) {
                $query = [
                    'pageToken' => $pageToken
                ];
            }

            $body = $this->call('GET', "/calendar/{$this->provider->getVersion()}/calendars/${calendarId}/events", [
                'headers' => ['Authorization' => 'Bearer ' . $token->getAccessToken()],
                'query' => $query
            ]);

            $items = $body['items'];

            // Skip loop
            if (count($items) === 0) {
                break;
            }

            $pageToken = $body['nextPageToken'] ?? null;

            $itemIterator = new \ArrayIterator($items);

            while ($itemIterator->valid()) {
                $event = $itemIterator->current();
                $eventId = $event['id'];

                // Delete event if status is cancelled
                if ($event['status'] === 'cancelled') {
                    // TODO delete events
                } else {
                    $eventStart = $event['start'] ?? null;
                    $startTimeZone = null;

                    if (isset($eventStart)) {
                        $startTimeZone = $eventStart['timeZone'] ?? 'UTC';
                        $eventStart = $eventStart['dateTime'] ?? $eventStart['date'];
                    }

                    $eventEnd = $event['end'] ?? null;
                    $endTimeZone = null;

                    if (isset($eventEnd)) {
                        $endTimeZone = $eventEnd['timeZone'] ?? 'UTC';
                        $eventEnd = $eventEnd['dateTime'] ?? $eventEnd['date'];
                    }

                    $eventRepository->insert([
                        'calendar_id' => $calendarId,
                        'summary' => $event['summary'],
                        'provider_id' => $eventId,
                        'provider_type' => $this->provider->getProviderName(),
                        'start_at' => Carbon::parse($eventStart)->tz($startTimeZone),
                        'end_at' =>  Carbon::parse($eventEnd)->tz($endTimeZone),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $itemIterator->next();
            }

        } while (is_null($pageToken) === false);

        $syncToken = $body['nextSyncToken'];

        app(CalendarRepository::class)->updateByAttributes(
            ['provider_id' => $calendarId, 'account_id' => $accountId],
            [
                'sync_token' => Crypt::encryptString($syncToken),
                'last_sync_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * @return AccountRepository
     */
    public function getAccountRepository(): AccountRepository
    {
        if (empty($this->accountRepository)) {
            $this->accountRepository = app(AccountRepository::class);
        }

        return $this->accountRepository;
    }

    /**
     * @return Client
     */
    protected function httpClient(): Client
    {
        if (empty($this->httpClient)) {
            $this->httpClient = app(Client::class, [
                'config' => [
                    'base_uri' => 'https://www.googleapis.com',
                    'headers' => $this->headers()
                ]
            ]);
        }

        return $this->httpClient;
    }

    /**
     * @param Client $httpClient
     *
     * @return GoogleSynchronizer
     */
    public function setHttpClient(Client $httpClient): static
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param array $headers
     * @return array
     */
    protected function headers(array $headers = []): array
    {
        return array_merge([
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'gzip',
            'User-Agent' => config('app.name') . ' (gzip)',

        ], $headers);
    }
}

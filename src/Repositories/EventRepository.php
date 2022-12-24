<?php

namespace Dnsinyukov\SyncCalendars\Repositories;

class EventRepository extends DBRepository
{
    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'calendar_events';
    }
}

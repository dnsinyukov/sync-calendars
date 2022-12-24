<?php

namespace Dnsinyukov\SyncCalendars\Repositories;

class CalendarRepository extends DBRepository
{
    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'calendars';
    }
}

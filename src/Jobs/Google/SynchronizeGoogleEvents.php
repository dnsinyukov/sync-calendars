<?php

namespace Dnsinyukov\SyncCalendars\Jobs\Google;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SynchronizeGoogleEvents extends SynchronizeGoogleResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $calendar;

    public function __construct($calendar)
    {
        $this->calendar = $calendar;
    }

    public function getGoogleService()
    {
        // TODO: Implement getGoogleService() method.
    }

    public function getGoogleRequest($service, $options)
    {
        // TODO: Implement getGoogleRequest() method.
    }

    public function syncItem($item)
    {
        // TODO: Implement syncItem() method.
    }
}

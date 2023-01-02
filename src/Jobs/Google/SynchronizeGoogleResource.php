<?php

namespace Dnsinyukov\SyncCalendars\Jobs\Google;

abstract class SynchronizeGoogleResource
{
    /**
     * @return void
     */
    public function handle()
    {
        $pageToken = null;

        $service = $this->getGoogleService();

        do {
            $list = $this->getGoogleRequest($service, compact('pageToken'));

            foreach ($list->getItems() as $item) {
                $this->syncItem($item);
            }

            $pageToken = $list->getNextPageToken();

            // Continue until the new page token is null.
        } while ($pageToken);
    }

    abstract public function getGoogleService();
    abstract public function getGoogleRequest($service, $options);
    abstract public function syncItem($item);
}

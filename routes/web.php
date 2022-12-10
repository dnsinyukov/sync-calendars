<?php

use Dnsinyukov\SyncCalendars\Http\AccountController;

Route::name('oauth2.auth')->get('/oauth2/{provider}', [AccountController::class, 'auth']);
Route::name('oauth2.callback')->get('/oauth2/{provider}/callback', [AccountController::class, 'callback']);

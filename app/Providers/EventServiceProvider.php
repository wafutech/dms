<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\MemberRegistrationEvent' => [
            'App\Listeners\GenerateRegistrationCertificate',
        ],
        'App\Events\ImportMembersEvent' => [
            'App\Listeners\CreateRegisrationCertificateToImportedMembers',
        ],

        'App\Events\ImportSharesEvent' => [
            'App\Listeners\ShareReceiptGeneratorListener',
        ],
         'App\Events\PaymentsReceivedEvent' => [
            'App\Listeners\PreparePaymentReceiptListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}

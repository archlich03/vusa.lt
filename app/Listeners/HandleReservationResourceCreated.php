<?php

namespace App\Listeners;

use App\Events\ReservationResourceCreated;
use App\Models\Pivots\ReservationResource;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;

class HandleReservationResourceCreated
{
    public function handle(ReservationResourceCreated $event)
    {
        $reservationResource = $event->reservationResource;

        // check if doing is instance of Doing
        if (! $reservationResource instanceof ReservationResource) {
            return;
        }

        $resourceManagers = $reservationResource->resource->managers();

        TaskService::storeTask('Patvirtinti arba atšaukti rezervaciją', $reservationResource->reservation, $resourceManagers);
    }
}

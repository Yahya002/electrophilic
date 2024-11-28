<?php

namespace App\Observers;

use App\Models\Commission;
use App\Models\Ticket;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if (!$ticket->employee_id)
        {
            return;
        }

        if ($ticket->payment_status === 'PAID')
        {
            if (!$ticket->commission) {
                Commission::create([
                    'ticket_id' => $ticket->id,
                    'employee_id' => $ticket->employee_id,
                    'amount' => $ticket->profit * $ticket->commission_rate,
                ]);
            }
            else
            {
                $ticket->commission->employee_id = $ticket->employee_id;
                $ticket->commission->amount = $ticket->profit * $ticket->commission_rate;
            }
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}

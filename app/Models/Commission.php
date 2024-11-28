<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'ticket_id',
        'amount',
        'status',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function ($commission) {
    //         $ticket = Ticket::find($commission->ticket_id);
    //         $commission->employee_id = $ticket->employee_id;
    //         $commission->amount = $ticket->profit * $ticket->commission_rate;
    //     });
    // }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'phone_number',
        'password',
        'role',
        'base_salary',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function isActive()
    {
        return $this->status == 'ACTIVE';
    }

    public function hasOwnerPerms()
    {
        
        return $this->currentAccessToken()->name == 'owner';
    }

    public function hasManagerPerms()
    {
        return $this->currentAccessToken()->name == 'owner' || $this->currentAccessToken()->name == 'manager';
    }

    public function hasEmployeePerms()
    {
        return $this->currentAccessToken()->name == 'owner' || $this->currentAccessToken()->name == 'manager' || $this->currentAccessToken()->name == 'employee';
    }
}

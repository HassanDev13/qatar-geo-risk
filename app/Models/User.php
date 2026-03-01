<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \Illuminate\Notifications\HasDatabaseNotifications;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'daily_credits',
        'last_credit_reset_at',
        'is_unlimited',
        'linkedin_url',
        'speciality_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_credit_reset_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'daily_credits' => 'integer',
            'is_unlimited' => 'boolean',
        ];
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return 1;
    }



    /**
     * Check and reset the daily credits if needed.
     */
    public function checkAndResetDailyCredits()
    {
        $now = now();
        $lastReset = $this->last_credit_reset_at;

        // Ensure daily_credits is integer
        if ($this->daily_credits === null) {
            $this->daily_credits = 10;
        }

        // If unlimited, ensure they have high credits and return
        if ($this->is_unlimited) {
            if ($this->daily_credits < 1000) {
                 $this->daily_credits = 999999;
                 $this->save();
            }
            return;
        }

        if (!$lastReset) {
            $this->daily_credits = 10;
            $this->last_credit_reset_at = $now;
            $this->save();
            return;
        }

        if (!$lastReset || !$lastReset->isSameDay($now)) {
            \Illuminate\Support\Facades\Log::info("User Model Reset: Resetting credits to 10 for User {$this->id}. LastReset: " . ($lastReset ? $lastReset->toDateTimeString() : 'NULL') . " Now: " . $now->toDateTimeString());
            $this->daily_credits = 10;
            $this->last_credit_reset_at = $now;
            $this->save();
        } else {
             \Illuminate\Support\Facades\Log::info("User Model Check: No need to reset for User {$this->id}. LastReset: " . ($lastReset ? $lastReset->toDateTimeString() : 'NULL'));
        }
    }


}

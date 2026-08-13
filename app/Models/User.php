<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Models\Branch;
use App\Models\AppointmentStaff;
use App\Models\ServiceBooking;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\Transaction;

class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, CanResetPassword;

    public const DEFAULT_PASSWORD = '11223344';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'house_no',
        'street',
        'state',
        'pincode',
        'country',
        'role',
        'status',
        'user_type',
        'profile_image',
        'city',
        'branch_id',
        'staff_id',
        'capabilities',
        'current_duty',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'capabilities' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function assignmentRelationships(): HasMany
    {
        return $this->hasMany(AppointmentStaff::class, 'staff_id');
    }

    public function assignedAppointments(): HasMany
    {
        return $this->hasMany(ServiceBooking::class, 'assigned_staff_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(ServiceBooking::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function assignedStaffAppointments(): HasManyThrough
    {
        return $this->hasManyThrough(ServiceBooking::class, AppointmentStaff::class, 'staff_id', 'appointment_id', 'id', 'id');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(ServiceBooking::class, 'appointment_staff', 'staff_id', 'appointment_id')
            ->withPivot(['assigned_at'])
            ->withTimestamps();
    }

    public function shouldRequirePasswordChange(): bool
    {
        return $this->must_change_password || Hash::check(self::DEFAULT_PASSWORD, $this->password);
    }
}

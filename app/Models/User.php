<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'portal',
        'is_active',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function labelPrints(): HasMany
    {
        return $this->hasMany(LabelPrint::class, 'created_by');
    }

    public function accessRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'super_admin' || (bool) $this->accessRole?->is_super_admin;
    }

    public function canAccess(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->accessRole?->permissions()->where('slug', $permission)->exists() ?? false;
    }

    public function homePath(): string
    {
        if ($this->portal === 'pos') {
            return '/pos';
        }

        foreach ([
            'dashboard.view' => '/dashboard',
            'inventory.stock_in' => '/stock-masuk',
            'inventory.stock_out' => '/stock-keluar',
            'labels.create' => '/labels/create',
            'labels.view' => '/labels',
            'products.view' => '/products',
            'branding.manage' => '/settings',
        ] as $permission => $path) {
            if ($this->canAccess($permission)) {
                return $path;
            }
        }

        return '/profile';
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function clientsProducts()
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    /** Ayarlardaki teknisyen listesi */
    public function technicians(): array
    {
        return array_values(array_filter(array_map('trim', (array) $this->setting('tickets.technicians', []))));
    }

    public function boardIds()
    {
        return $this->hasMany(BoardId::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return \App\Support\Settings::get($this, $key, $default);
    }

    /** Müşteriye görünen firma adı (ayarlanmamışsa kullanıcı adı). */
    public function companyName(): string
    {
        return $this->setting('company.name') ?: $this->name;
    }
}

<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    protected $fillable = ['name','email','password'];
    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function authorProfile(): HasOne { return $this->hasOne(AuthorProfile::class); }
    public function articles(): HasMany { return $this->hasMany(Article::class, 'author_id'); }
}

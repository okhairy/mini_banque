<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'name',
        'email',
        'password',
        'account_number', // Ajout du champ account_number ici

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    // Dans le modèle User
// Dans le modèle User
public static function findByAccountNumber($accountNumber)
{
    return self::where('account_number', $accountNumber)->first();
}
public function transactions()
{
    return $this->hasMany(Transaction::class);
}
public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');  // ou utilisez la clé étrangère appropriée
    }


}

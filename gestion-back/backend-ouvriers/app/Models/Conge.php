<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'date_debut',
        'date_fin',
        'motif',
        'status',
        'commentaire_admin'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

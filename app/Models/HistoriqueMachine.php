<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueMachine extends Model
{
    use HasFactory, HasFactory;

    protected $fillable = ["id_machine", "historique", "date_heure"];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('date_heure', 'DESC');
        });
    }
}

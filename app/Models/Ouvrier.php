<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ouvrier extends Model
{
    use HasFactory;
    protected $fillable = ["nom", "matricule", "id_chaine", "allure", "present"];
    protected $with = ["competences.reference"];

    function competences(): HasMany
    {
        return $this->hasMany(OuvrierMachine::class, "id_ouvrier");
    }

    function chaine(): BelongsTo
    {
        return $this->belongsTo(Chaine::class, "id_chaine");
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('allure', 'DESC');
        });
    }
}

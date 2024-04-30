<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class districts extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'region_name'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function customer()
    {
        return $this->hasMany(customer::class);
    }
    public function campaigns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(campaigns::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class campaigns extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_code', 'name', 'template_id', 'region_id', 'category_id', 'district_id', 'is_active'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function category()
    {
        return $this->belongsTo(categories::class);
    }

    public function district()
    {
        return $this->belongsTo(districts::class);
    }

    public function template()
    {
        return $this->belongsTo(templates::class, 'template_id');
    }
    public function schedules()
    {
        return $this->hasMany(campaign_schedules::class);
    }
}

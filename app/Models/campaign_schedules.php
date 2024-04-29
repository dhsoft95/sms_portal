<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class campaign_schedules extends Model
{
    use HasFactory;

    public function campaign()
    {
        return $this->belongsTo(campaigns::class);
    }
}

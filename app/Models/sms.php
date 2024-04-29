<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sms extends Model
{
    use HasFactory;
    protected $fillable = ['phone', 'sms_campaign_id', 'category', 'district', 'status'];

    public function campaigns()
    {
        return $this->belongsTo(campaigns::class, 'sms_campaign_id');
    }
}

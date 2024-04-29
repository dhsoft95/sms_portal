<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageSchedule extends Model
{
    use HasFactory;

    public function message()
    {
        return $this->belongsTo(message::class);
    }
}

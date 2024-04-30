<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    use HasFactory;

    protected $fillable = ['fname', 'lname', 'phone', 'district_name', 'region_name','category_name'];

    public function district()
    {
        return $this->belongsTo(districts::class, 'district_name'); // Change 'district_id' to 'district_name'
    }


    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Categories::class,'category_name');
    }
}

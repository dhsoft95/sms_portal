<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    use HasFactory;

    protected $fillable = ['fname', 'lname', 'phone', 'district_name', 'region_name','category_name'];

    public static $rules = [
        'fname' => 'required|string|max:255',
        'lname' => 'required|string|max:255',
        'phone' => 'required|string|max:20|unique:customers,phone',
        'district_name' => 'required|string|max:255',
        'region_name' => 'required|string|max:255',
        'category_name' => 'required|string|max:255',
    ];

    public function district()
    {
        return $this->belongsTo(districts::class, 'district_name'); // Change 'district_id' to 'district_name'
    }


    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Categories::class,'category_name');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'other_attributes',
    ];

    protected $casts = [
        // Other attributes with automatic casting
        'other_attributes' => 'array', // If you just want to automatically decode JSON
    ];

    // Mutator to convert the other_attributes array to json, thus saving the json in the database.
    public function setOtherAttributesAttribute($value)
    {
        $this->attributes['other_attributes'] = json_encode($value);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }
}

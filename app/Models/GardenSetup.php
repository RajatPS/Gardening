<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GardenSetup extends Model
{
    protected $fillable = ['name','phone','address','budget','notes'];
}

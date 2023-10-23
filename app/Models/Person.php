<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'password',
        'gender',
        'age',
        'type',
        'image',
        'special'
    ];
}

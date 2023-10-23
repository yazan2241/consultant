<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consult extends Model
{
    use HasFactory;
    protected $fillable = [
        'userId',
        'doctorId',
        'message',
        'answer',
        'diagnostic',
        'firstName',
        'lastName',
        'age',
        'image'
    ];
}

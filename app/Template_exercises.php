<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template_exercises extends Model
{
    protected $primaryKey = 'texercise_id';
    protected $guarded = ['texercise_id'];
}

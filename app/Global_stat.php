<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Global_stat extends Model
{
    protected $primaryKey = 'gstat_name';
	public $incrementing = false;
    protected $guarded = ['gstat_name'];
}

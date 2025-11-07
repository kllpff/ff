<?php

namespace App\Models;

use FF\Database\Model;

class TestModel extends Model
{
    protected $table = 'testmodels';
    protected $fillable = [];
    protected $hidden = [];

    public function __construct()
    {
        parent::__construct();
    }
}
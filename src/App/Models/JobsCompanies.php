<?php

namespace FeedzRecoloca\Models;

use Illuminate\Database\Eloquent\Model;

class JobsCompanies extends Model{

    public $timestamps = true;
    protected $table = 'jobs_companies';

    public function getLogoImageAttribute($value)
    {
        return base64_encode($value);
    }
}
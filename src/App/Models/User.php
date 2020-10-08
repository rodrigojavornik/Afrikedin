<?php

namespace FeedzRecoloca\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model{

    public $timestamps = true;
    protected $table = 'users';
    
    public function resume()
    {
        return $this->hasOne(Resume::class, 'id_user', 'id');
    }

    public static function findByLinkedinId($linkedinId)
    {
        return self::where('linkedin_user_id', $linkedinId)->first();
    }
}
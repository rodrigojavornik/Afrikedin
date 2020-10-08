<?php

namespace FeedzRecoloca\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model{

    public $timestamps = true;
    protected $table = 'resumes';

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'id_city');
    }

    public function occupation()
    {
        return $this->hasOne(Occupation::class, 'id', 'id_occupation_area');
    }

    public function getSkillsAttribute($value)
    {
        return explode(',', $value);
    }    

    public static function get($idUser)
    {
        return self::where('id_user', $idUser)->first();
    }

    public static function getList($occupation = null, $city = null, $page = 1)
    {
        $page = $page ?? 1;
        $limit = 15;
        $total = self::where('status', true)->count();
        $offset = ($page -1) * $limit;

        $resumes = self::where('status', true)
            ->skip($offset)
            ->take($limit)
            ->inRandomOrder($_SESSION['random']);

        if (!is_null($occupation)) {
            $resumes->where('id_occupation_area', $occupation);
        }

        if (!is_null($city)) {
            $resumes->where('id_city', $city);
        }

        return [
            'data' => $resumes->get(),
            'maxPage' => ceil($total / $limit),
            'currentPage' => $page
        ];
    }
}
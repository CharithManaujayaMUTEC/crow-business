<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    protected $fillable = ['enabled','api_url','api_user_id','api_key','sender_id','country_code'];
    protected $casts = ['enabled'=>'boolean'];
}

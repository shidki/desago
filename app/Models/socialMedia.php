<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class socialMedia extends Model
{
    //

    protected $table = "app_socialMedias";
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
        'avatar'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}

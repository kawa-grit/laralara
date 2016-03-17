<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Oauth extends Model {

    protected $table = 'user_oauths';

    protected $fillable = [
        'provider', 'uid', 'nickname', 'name', 'email', 'avatar', 'user', 'token', 'token_secret'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }
}

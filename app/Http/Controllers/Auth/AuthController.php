<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

use Auth;
use Socialite;
use Validator;
use Redirect;

use App\User;
use App\Oauth;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data) {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function redirectToProvider($provider) {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider) {
        $oauthUser = Socialite::driver($provider)->user(); // TODO キャンセル時にエラー！
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::whereEmail($oauthUser->email)->first();
            if (count($user) === 0) {
                $name = $oauthUser->nickname;
                if (empty($name)) {
                    $name = $oauthUser->name;
                }
                $user = $this->create([
                    'name' => $name,
                    'email' => $oauthUser->email,
                    'password' => $oauthUser->token,
                ]);
            }
        }
        $oauth = Oauth::whereProvider($provider)->whereUid($oauthUser->id)->first();
        if (count($oauth) === 0) {
            $oauth = new Oauth();
            $oauth->provider = $provider;
            $oauth->uid = $oauthUser->id;
        }
        $oauth->nickname = $oauthUser->nickname;
        $oauth->name = $oauthUser->name;
        $oauth->email = $oauthUser->email;
        $oauth->avatar = $oauthUser->avatar;
        $oauth->token = $oauthUser->token;
        if (property_exists($oauthUser, 'tokenSecret')) {
            $oauth->token_secret = $oauthUser->tokenSecret;
        } else {
            $oauth->token_secret = NULL;
        }
        $oauth->user = print_r($oauthUser->user, TRUE);
        $oauth = $user->oauths()->save($oauth);

        Auth::login($user);
        return Redirect::to('/');
    }
}

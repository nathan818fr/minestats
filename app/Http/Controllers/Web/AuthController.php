<?php

namespace MineStats\Http\Controllers\Web;

use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use MineStats\Http\Controllers\Controller;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    public $redirectTo;

    public function __construct()
    {
        $this->redirectTo = route('serversList');

        $this->middleware('guest', ['except' => 'getLogout']);
    }

    public function username()
    {
        return 'username';
    }

    public function showLoginForm()
    {
        return $this->getLogin();
    }

    public function getLogin()
    {
        return view('view.auth.login');
    }

    public function postLogin(Request $req)
    {
        return $this->login($req);
    }

    public function getLogout($logoutToken)
    {
        if (self::getLogoutToken() == $logoutToken) {
            Auth::logout();
        }

        return redirect($this->redirectPath());
    }

    public static function getLogoutUrl()
    {

        return route('logout', [
            'logoutToken' => self::getLogoutToken()
        ]);
    }

    public static function getLogoutToken()
    {
        /*
         * We do not use directly csrf_token because it would not be safe for display in a url
         */
        return sha1('logout'.csrf_token());
    }
}

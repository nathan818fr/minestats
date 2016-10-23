<?php

namespace MineStats\Http\Controllers\Web;

use Hash;
use Auth;
use Flash;
use Illuminate\Http\Request;
use MineStats\Http\Controllers\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('getAccount');
    }

    public function getAccount()
    {
        return view('view.user.account');
    }

    public function postAccount(Request $req)
    {
        $this->validate($req, [
            'password' => 'required_with:password_confirmation|confirmed|min:8|max:60'
        ]);

        $user = Auth::user();
        if ($req->has('password')) {
            $user->password = Hash::make($req->get('password'));
            $user->must_change_password = false;
            Flash::success(trans('user.account_updated'));
        }
        $user->save();

        return redirect()->route('account');
    }
}
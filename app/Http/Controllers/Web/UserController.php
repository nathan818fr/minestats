<?php

namespace MineStats\Http\Controllers\Web;

use Hash;
use Auth;
use Flash;
use Gate;
use Illuminate\Http\Request;
use MineStats\Http\Controllers\Controller;
use MineStats\Models\Server;
use MineStats\Models\User;

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

    public function getUsersList()
    {
        Gate::authorize('view', User::class);

        return view('view.user.users-list', [
            'users' => User::paginate(10)
        ]);
    }

    public function getUserCreate()
    {
        return $this->getUserEdit(null);
    }

    public function postUserCreate(Request $req)
    {
        return $this->postUserEdit($req, null);
    }

    public function getUserEdit($userId)
    {
        if ($userId === null) {
            Gate::authorize('create', User::class);
            $user = null;
        } else {
            $user = User::findOrFail($userId);
            Gate::authorize('update', $user);
        }

        return view('view.user.user-edit', [
            'user' => $user
        ]);
    }

    public function postUserEdit(Request $req, $userId)
    {
        if ($req->has('delete') && $userId !== null) {
            return $this->deleteUser($userId);
        }

        $rules = [
            'username' => 'required|alpha_dash|min:3|max:20|unique:users,username',
            'password' => 'min:8|max:60',
            'acl'      => 'required|in:'.join(',', array_keys(User::ACL_BY_ID)),
        ];

        if ($userId === null) {
            $rules['password'] .= '|required';
        } else {
            $rules['username'] .= ','.$userId;
        }

        $this->validate($req, $rules);

        if ($userId === null) {
            Gate::authorize('create', User::class);
            $user = new User();
        } else {
            $user = User::findOrFail($userId);
            Gate::authorize('update', $user);
        }

        $user->username = $req->get('username');
        $user->password = Hash::make($req->get('password'));
        $user->must_change_password = $req->has('must_change_password');
        $user->acl = $req->get('acl');
        $user->save();

        if ($userId === null) {
            Flash::success(trans('user.user_created'));
        } else {
            Flash::success(trans('user.user_updated'));
        }

        return redirect()->route('usersList');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        Gate::authorize('delete', $user);

        $user->delete();
        \Flash::error(trans('user.user_deleted'));

        return redirect()->route('usersList');
    }
}
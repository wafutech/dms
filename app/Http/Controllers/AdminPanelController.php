<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class AdminPanelController extends Controller
{
    //
    public function usersWithRoles()
    {
    	/* $users = User::with('roles')       
        ->get();
        return view('admin.users_with_roles',array('title'=>'Users With Roles','users'=>$users));*/
    }
}

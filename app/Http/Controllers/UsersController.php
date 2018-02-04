<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Session;
use DB;

class UsersController extends Controller
{
    //
     function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');

    }

     public function index()
    {
        // Fetch all users
        $users = User::all();
        return view('auth.users.index',array('title'=>'Users','users'=>$users));
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $user= User::findOrFail($id);
        //get user role
        $role = DB::table('role_user')
        ->leftjoin('roles','role_id','roles.id','role_user.role_id')
        ->where('role_user.user_id',$id)->first();

        return view('auth.users.show',array('user'=>$user,'role'=>$role));
    
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $user= User::findOrFail($id);
        return view('auth.users.edit',array('user'=>$user,'title'=>'Edit User'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //

          $user = User::findOrFail($id);
          $input = $request->all();
          $user->fill($input)->save();
            Session::flash('message', 'User successfully updated!');

    return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
         $user = User::findOrFail($id);
    $user->delete();
    Session::flash('message', 'The user entry has been permanently deleted!');

    return redirect()->route('users.index');
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Permission;
use Session;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
    $this->middleware('auth');
    $this->middleware('role:admin');


//$this->middleware('ability:admin,create-users');
    }
    public function index()
    {
        //
        $perms = Permission::all();
        return view('auth.permissions.index',['title'=>'User Permissions',
            'perms'=>$perms]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
         $perm= Permission::findOrFail($id);
        return view('auth.permissions.show',array('perm'=>$perm));
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
        $perm= Permission::findOrFail($id);
        return view('auth.permissions.edit',array('perm'=>$perm,'title'=>'Edit Permission'));
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
         $perm = Permission::findOrFail($id);
          $input = $request->all();
         $perm->fill($input)->save();
            Session::flash('message', 'Permission successfully updated!');

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
         $perm = User::findOrFail($id);
    $perm->delete();
    Session::flash('message', 'The permission has been permanently deleted!');

    return redirect()->route('permissions.destroy');
    }
}

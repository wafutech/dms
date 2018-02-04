<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use DB;

class RolesController extends Controller
{

    public function __construct()
    {
    $this->middleware('auth');
    $this->middleware('role:admin');


    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $roles = Role::all();

        return view('auth.roles.index',array('title'=>'Roles','roles'=>$roles));
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
        $role= Role::findOrFail($id);
        return view('auth.roles.show',array('role'=>$role));
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
         $role= Role::findOrFail($id);
        return view('auth.roles.edit',array('role'=>$role,'title'=>'Edit Roles'));
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
        $role = Role::findOrFail($id);
          $input = $request->all();
         $role->fill($input)->save();
            Session::flash('message', 'Role successfully updated!');

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
         $role = User::findOrFail($id);
    $role->delete();
    Session::flash('message', 'The role has been permanently deleted!');

    return redirect()->route('roles.destroy');
    }
}

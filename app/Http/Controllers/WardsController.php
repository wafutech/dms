<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\Middleware\ErrorBinder;
use Session;
use App\Share;
use App\County;
use Validator;
use DB;
use Carbon\Carbon;
use App\Constituency;
use App\Ward;

class WardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct() 
    {
    $this->constituencies =  Constituency::orderby('const_name','asc')->pluck('const_name','id');
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('locations.wards.create',
            array('title'=>'Add Wards','constituencies'=>$this->constituencies));
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
         $wards = $request->input('wards');
        $const_id = $request->input('const_id');
        $wards =explode(',', $wards);
       
       for($i=0;$i<=count($wards)-1;$i++)
        {
            $ward = New Ward;
            $ward->ward = ucfirst($wards[$i]);
            $ward->constituency_id = $const_id;
            $ward->save();
        }
        $count_const = count($wards);

        $message = $count_const." Wards added successfully!";
         Session::flash('message', $message);
       return redirect()->back();
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
    }
}

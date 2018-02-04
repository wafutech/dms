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


class ConstituencyController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct() 
    {
    $this->counties =  County::pluck('county_name','id');
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
        return view('locations.constituency.create',
            array('counties'=>$this->counties,'title'=>'Add Constituencies'));
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
        $cons = $request->input('const');
        $county_id = $request->input('county_id');
        $constituencies =explode(',', $cons);
       // print_r($constituencies);
       for($i=0;$i<=count($constituencies)-1;$i++)
        {
            $con = New Constituency;
            $con->const_name = ucfirst($constituencies[$i]);
            $con->county_id = $county_id;
            $con->save();
        }
        $count_const = count($constituencies);

        $message = $count_const." Constituencies added successfully!";
         Session::flash('message', $message);
       return redirect()->back();
//}
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

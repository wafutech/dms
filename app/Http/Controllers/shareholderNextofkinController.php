<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\Middleware\ErrorBinder;
use Session;
use App\County;
use App\Shareholdernextofkin;
use Validator;
use App\Member;
use App\Constituency;
use App\Ward;
use DB;


class shareholderNextofkinController extends Controller
{
    //
    

var $counties;
    public function __construct() {
    $this->middleware('auth');
    $this->middleware('role:admin');
    $this->counties = County::pluck('county_name','id');
       


    }
    public function index()
    {
        //
        $nextofkins = DB::table('shareholder_nextofkins')
            ->leftjoin('counties','county_id',
                'counties.id','shareholder_nextofkins.county_id')
            ->leftjoin('constituencies','constituency_id',
                'constituencies.id','members.constituency_id')
            ->leftjoin('wards','ward_id',
                'wards.id','members.ward_id')
            ->get();
           
           
        
        return view('shareholders.index',array('title'=>'Shareholder Next of Kins','nextofkins'=>$nextofkins));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
 return view('shareholders.create',array('title'=>'Add next of kin','counties'=>$this->counties));

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
        $validation_rules = array(
          'member_number'         => 'bail|required|numeric|digits:4|exists:members,member_registration_number|unique:shareholder_nextofkins',
          'mobile_phone'                 => 'required|numeric|digits:10',
          'id_number'                  => 'required|numeric|unique:shareholder_nextofkins',
          'first_name'                => 'required|string',
          'last_name'                => 'required|string',
          'email'      => 'email|unique:shareholder_nextofkins',
          'postal_address'                 => 'required|string',
          'town'         => 'alpha',
          'zip'         => 'numeric|digits:5',
          'phisical_address'         => 'required|string',
          'county'      => 'required',   
          'subcounty'      => 'required|string',
          'ward'      => 'required|string', 
          'relationship'      => 'required|string',    
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
        $nextofkins = Input::all();
        $newnextofkin =  Shareholdernextofkin::create($nextofkins);        
        Session::flash('message', 'Record successfully saved!');
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
        //$nextofkin= Shareholdernextofkin::findOrFail($id);
        $nextofkin = DB::table('shareholder_nextofkins')
            ->leftjoin('counties','county_id',
                'counties.id','shareholder_nextofkins.county_id')
            ->leftjoin('constituencies','constituency_id',
                'constituencies.id','members.constituency_id')
            ->leftjoin('wards','ward_id',
                'wards.id','members.ward_id')
            ->where('shareholder_nextofkins.id',$id)
            ->first();

        return view('shareholders.show',array('nextofkin'=>$nextofkin));
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
        $nextofkin= Shareholdernextofkin::findOrFail($id);
        $constituencies = Constituency::where('county_id',$nextofkin->county_id)->pluck('const_name','id');
       $wards = Ward::where('constituency_id',$nextofkin->constituency_id)->pluck('ward','id');
        return view('shareholders.edit',array('nextofkin'=>$nextofkin,
            'title'=>'Edit','counties'=>$this->counties,'wards'=>$wards,'constituencies'=>$constituencies));
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
        $nextofkin = Shareholdernextofkin::findOrFail($id);
          $input = $request->all();
         $nextofkin->fill($input)->save();
            Session::flash('message', 'Member next of kin successfully updated!');

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
        $nextofkin = Shareholdernextofkin::findOrFail($id);
    $nextofkin->delete();
    Session::flash('message', 'Member next of successfully deleted!');

    return redirect()->route('shareholders.destroy');
    }
}

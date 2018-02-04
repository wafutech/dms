<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Session;
use App\Share;
use App\County;
use Validator;
use DB;
use Carbon\Carbon;
use App\Constituency;
use App\Ward;
use App\Member;

class AjaxRequestsController extends Controller
{
    //
    public function constituencies($id)
    { 
        
        
    $constituencies = Constituency::where('county_id','=',$id)->pluck('const_name','id');
    	
        return $constituencies;

                    
    }

    public function wards($id)
    {
      
       
        
       // $constituencies = Constituency::where('county_id','=',$id)->pluck('const_name','id');
        $wards = DB::table('wards')
                        ->where('constuency_id','=',$id)
                        ->pluck('ward','id');
                        //->orderBy('const_name','asc');
                        return $wards;

        //return json_decode($constituencies);

    }

    public function CountyFilter($id)
    {
        $members = Member::where('county_id',$id)->get();
        return "success";
    }

    public function editMember($id)
    {
        //
        $member= Member::findOrFail($id);
        return $member;
        
       // return view('members.edit',array('member'=>$member,'title'=>'Edit Member Credentials',
          // 'counties'=> $this->counties,'education_level'=>$this->education_level));
    }

    public function filterConstituencies($county)
    {
      $constituencies = Constituency::where('county_id',$county)
      ->orderBy('const_name', 'asc')
      ->pluck('const_name','id');
    // return Response::json($constituencies);
      
    return $constituencies;



    }
    public function filterWards($constituency)
    {
      $wards = Ward::where('constituency_id',$constituency)
      ->orderBy('ward', 'asc')
      ->pluck('ward','id');
      
    return $wards;

    }

      public function getOccupation()
    {
        $query = Input::get('occupation');
        $data = array();
        $results = Member::select('occupation')
           ->where('occupation', $query)
          ->orWhere('occupation', 'LIKE',  '%' . $query . '%')

            //->orderBy('occupation','asc')
            ->take(5)
            ->get();

        foreach ( $results as $result ):
            $data[] = $result->occupation;
        endforeach;

        //var_dump($data);
        return Response::json($data);
    }

      public function getSubcounty()
    {
        $query = Input::get('sub_county');
        $data = array();
        $results = Member::select('sub_county')
           ->where('sub_county', $query)
          ->orWhere('sub_county', 'LIKE',  '%' . $query . '%')

            //->orderBy('occupation','asc')
            ->take(5)
            ->get();

        foreach ( $results as $result ):
            $data[] = $result->sub_county;
        endforeach;

        //var_dump($data);
        return Response::json($data);
    }
    
    public function checkIdNumber($value)
    
    {
        
       $idNumber = Member::where('idnumber',$value)->first();
       if($idNumber)
       
       {
           return 'exists';
       }
       
       return 'available';
    }
    
      public function checkMemberNumber($value)
    
    {
        $memberNumber = Member::where('member_registration_number',$value)->first();
       if(!$memberNumber)
       
       {
           return 'not found';
       }
       
       return 'available';
        
    }
    
    
      public function checkEmail($value)
    
    {
        $memberEmail = Member::where('email',$value)->first();
       if($memberEmail)
       
       {
           return 'exists';
       }
       
       return 'available';
        
    }
}



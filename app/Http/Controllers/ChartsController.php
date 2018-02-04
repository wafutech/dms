<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Member;
use DB;
use App\Share;
use App\User;

class ChartsController extends Controller
{
    
    public function YearlyMemberRegistration()
    {
    	/*$members_2014 = Member::where(DB::raw('YEAR(created_at)',2014)->get();
    	$members_2015 = Member::where(DB::raw('YEAR(created_at)',2015)->get();
    	$members_2016 = Member::where(DB::raw('YEAR(created_at)',2016)->get();
        $members_2017 = Member::where(DB::raw('YEAR(created_at)',2017)->get();*/

    }

    public function MonthlyMemberRegistration()
    {
    	$data = DB::table('members')
      ->select(DB::raw('count(id) as data'),DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
      ->groupBy('year','month')->get();


      return json_decode($data);
    }

     public function ShareReportByMonth($year)
    {
    	
    	

    	$data = DB::table('shares')
      ->select(DB::raw('sum(amount) as shares'),DB::raw(' MONTH(date_paid) month'))
      ->whereYear('date_paid',$year)
      ->groupBy('month')->get();     


      return json_decode($data);
      
    }

     public function ShareReportByMonth2($year)
    {
      
      

      $data = DB::table('shares')
      ->select(DB::raw('sum(amount) as shares'),DB::raw(' MONTH(date_paid) month'))
      ->whereYear('date_paid',$year)
      ->groupBy('month')->get();

      


      return json_decode($data);
      
    }

    public function ShareReportComparisonByYear()
    {
    	$data = DB::table('shares')
      ->select(DB::raw('sum(amount) as shares'),DB::raw('YEAR(date_paid) year'))
      ->groupBy('year')->get();


      return json_decode($data);
    }
    public function sharesByConstituency()
    {
      $constituencies = DB::table('constituencies')
                        ->leftjoin('members','members.constituency_id','constituencies.id','constituencies.id')
                        ->leftjoin('shares','members.member_registration_number','shares.member_number','members.member_registration_number')
                        ->join('counties','constituencies.county_id','counties.id','constituencies.county_id')
                        ->select(DB::raw('const_name'),
                                 DB::raw('sum(shares.amount) as share'))
                        ->where('constituencies.county_id',42)

                        ->groupBy(DB::raw('const_name'))
                        ->get();
                        return json_decode($constituencies);
    }

      public function membersByContstituency()
      {
        $constMembers = DB::table('members')
                        ->join('constituencies','members.constituency_id','constituencies.id','members.constituency_id')
                        ->join('counties','constituencies.county_id','counties.id','constituencies.county_id')
                        ->select(DB::raw('const_name'),DB::raw('count(members.id) as members'))
                        ->where('constituencies.county_id',42)
                        ->groupBy(DB::raw('const_name'))->get();
                        return json_decode($constMembers);


      }

       public function countMaleMembers()
    {
      $maleMembers = DB::table('members')
      ->select(DB::raw('count(id) as malemembers'),DB::raw('YEAR(registration_date) year'))
      ->groupBy('year')
      ->where('sex','Male')
      ->get();
      return $maleMembers;

    }

       public function countFemaleMembers()
    {     

      $femaleMembers = DB::table('members')
      ->select(DB::raw('count(id) as femalemembers'),DB::raw('YEAR(registration_date) year'))
      ->groupBy('year')
      ->where('sex','Female')
      ->get();
      return $femaleMembers;

    }

    

    
    
}

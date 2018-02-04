<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use App\Member;
use DB;
use Validator;

class SearchController extends Controller
{
    //
    public function __construct() 
    {
$this->middleware(['auth']);


     } 
    public function searchMember()
    {

      $validation_rules = array(
          'searchTerm'         => 'required',
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
    	$keyword = Input::get('searchTerm');
      
      $deleted_members= Member::onlyTrashed()->get();
        //Fetch members with incomplete credentials

        $incomplete_members = DB::table('members')->where('idnumber',null)->orWhere('phone_contact',null)->orWhere('year_of_birth',null)->orWhere('name',null)->get(); 

      if(!is_numeric($keyword))
      {
         $members = Member::where('name','LIKE', "%{$keyword}%")
              ->paginate(10);

       // return view('members/index',array('title'=>'Members','members'=>$members,'deleted_members'=>$deleted_members,'incomplete'=>$incomplete_members)); 
      }
      else
      {
         $validation_rules = array(
          'searchTerm'         => 'required|exists:members,member_registration_number',
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
$members = Member::where('member_registration_number',$keyword)->paginate(10);     

 }
      
   
     return view('members/index',array('title'=>'Members','members'=>$members,'deleted_members'=>$deleted_members,'incomplete'=>$incomplete_members)); 

    }

     public function searchMemberAjax(Request $request)
    {
              $search = $request->id;
              $members = Member::where('name','LIKE', "%{$search}%")
              ->get();
              


$deleted_members= Member::onlyTrashed()->get();
        //Fetch members with incomplete credentials

        $incomplete_members = DB::table('members')->where('idnumber',null)->orWhere('phone_contact',null)->orWhere('year_of_birth',null)->orWhere('name',null)->get(); 


        return view('members/membersearch',array('title'=>'Members','members'=>$members,'deleted_members'=>$deleted_members,'incomplete'=>$incomplete_members));

  	  /*$read = "";

    	$keyword = Input::get('search');
    	$members = Member::searchByKeyword($keyword)->simplepaginate(10);
    
     foreach($members as $member){
   $read .="
      <tr class='gradeX even' role='row'>
       <td> 
                                $member->name
                            </td>
                            <td> 
                                $member->sex
                            </td>
                            <td>
                                $member->phone_contact
                            </td>
                            <td> 
                                $member->email
                            </td>
                            <td> 
                                $member->postal_address
                            </td>
                            <td>$member->town</td>
 <td>$member->postal_code</td>
 <td>$member->county_id</td>
 <td>$member->member_registration_number</td>
                            <td> 
                                <a data-id='$member->id' class='edit' href='#'>Edit</a> | 
                                <a data-id='$member->id' class='delete-modal' data-toggle='modal' href='#delete-confirmation-modal'>Delete</a>
                            </td>
                        </tr>";
  }
  return $read;*/


     //return view('members/index',array('title'=>'Members','members'=>$members,'deleted_members'=>0));

    }
}

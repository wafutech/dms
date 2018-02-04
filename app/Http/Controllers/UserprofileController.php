<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\User;
use App\Userprofile;
use App\EducationLevel;
use Auth;
use Session;
use DB;
use Validator;
use Carbon\Carbon;
use App\Userskill;
use App\Member;
use App\Share;

class UserprofileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $education_level;
    public function __construct()
    {
        $this->middleware('auth');
        $this->education_level =  EducationLevel::pluck('level','level');

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
        return view('auth.users.profile.create',array('title'=>'User Profile','education_level'=>$this->education_level));
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
        $user_id = Userprofile::where('user_id',Auth::user()->id)->first();
        if($user_id)
        {
            Session::flash('message','Your already have a profile!');
           return redirect()->back();
            

        }
        $validation_rules = array(
         'user_id'=>'unique:userprofiles',
          'title'         => 'bail|required|string',
          'first_name'         => 'required|alpha',
         'last_name'         => 'required|alpha',
          'sex'      => 'required|alpha',        
          'phone'                => 'required|numeric|digits:10',
          'postal_address'      => 'required',
          'town'         => 'required|alpha',
          'zip'         => 'numeric',
          'avatar'      => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',        
          'education_level'     => 'required|string',
          //'skills'      => 'required',
          'notes'                => 'string',
        
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
     if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      }
      $path ='';
      if($request->hasFile('avatar')) 
      {
        $avatar = $request->file('avatar');
        $path = $avatar->store('images/avatars');
       // $imageName = time().'.'.$request->image_file->getClientOriginalExtension();
        
         $avatarName = Auth::user()->name.Auth::user()->id;
        $request->image_file->move(public_path('images/avatars'), $avatarName);

         
      }

     

//$path = $request->file('avatar')->store('/public/images/avatars');

      //  return $path;
        
      //Save profile into the database

      $profile = New Userprofile;
      $profile->user_id =Auth::user()->id;
      $profile->title = $request->input('title');
      $profile->first_name = $request->input('first_name');
      $profile->last_name = $request->input('last_name');
      $profile->sex = $request->input('sex');
      $profile->phone = $request->input('phone');
      $profile->postal_address = $request->input('postal_address');
      $profile->town = $request->input('town');
      $profile->zip = $request->input('zip');
      $profile->avatar = $path;
      $profile->education = $request->input('education_level');
      $profile->notes = $request->input('notes');
      $profile->save();
      /*$skills = explode(',', $request->input('skills'));
      for($i=0;$i<count($skills);$i++)
      {
        $skill = new Userskill;
        $skill->user_id = $profile->user_id;
        $skill->skill =$skills[$i];
        $skill->save();
      }*/
      $user = DB::table('userprofiles')
      ->leftjoin('users','user_id','users.id','userprofiles.user_id')
      ->leftjoin('userskills','users.id','userskills.user_id','userprofiles.user_id')
      ->where('userprofiles.user_id',Auth::user()->id)
      ->first();
      $skills = Userskill::where('user_id',Auth::user()->id)->get();
$role = DB::table('role_user')
        ->leftjoin('roles','role_id','roles.id','role_user.role_id')
        ->where('role_user.user_id',Auth::user()->id)->first();

      return view('auth.users.profile.user_profile',
        array('title'=>'User Profile','user'=>$user,'skills'=>$skills,'role'=>$role));
    

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
       // $profile= Userprofile::findOrFail($id);
      
        $user = DB::table('userprofiles')
      
      ->leftjoin('users','user_id','users.id','userprofiles.user_id')
      ->leftjoin('userskills','users.id','userskills.user_id','userprofiles.user_id')
      ->where('userprofiles.user_id',$id)
      ->first();
     if($user==null)
     {
        return view('auth.users.profile.create',array('title'=>'User Profile','education_level'=>$this->education_level));

     }

    

      $skills = Userskill::where('user_id',$id)->get();
    
$role = DB::table('role_user')
        ->leftjoin('roles','role_id','roles.id','role_user.role_id')
        ->where('role_user.user_id',$id)->first();
        // Members registered by the logged in user
$members_registered = Member::where('user_id',$id)->get();
$total_members = count($members_registered);
$total_reg_fee = 0;
foreach($members_registered as $member)
{
  $total_reg_fee+=$member->registration_fee;
}

//fetch shares transations and share amount of the logged in user
$shares = Share::where('user_id',$id)->get();
$share_trans = count($shares);
//Calculat share amount received by the current user
$total_shares = 0;
foreach($shares as $share)
{
  $total_shares+=$share->amount;
}
return view('auth.users.profile.user_profile',
        array('title'=>'User Profile','user'=>$user,'skills'=>$skills,'role'=>$role,'membersregistered'=>$total_members,'registration_fee'=>$total_reg_fee,'sharetransaction'=>$share_trans,'total_shares'=>$total_shares));

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
        $profile= Userprofile::findOrFail($id);
        return view('auth.users.profile.edit',array('profile'=>$profile));
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
         $profile = Userprofile::findOrFail($id);
          $input = $request->all();
         $profile->fill($input)->save();
            Session::flash('message', 'User Profile successfully updated!');

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
        $profile = Userprofile::where('user_id',$id);
    $profile->delete();
    $message = 'User profile successfully deleted!';
    return redirect()->back()->with('message',$message);
    }
}

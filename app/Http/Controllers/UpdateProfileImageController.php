<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Userprofile;
use Auth;
use Session;
use Storage;


class UpdateProfileImageController extends Controller
{
    //
      public function __construct() 
    {
$this->middleware(['auth']);
$this->middleware('role:admin');

     } 

    public function ProfileImageUploadForm($id)
    {

    return view('auth.users.profile.edit_profile_image',array('user'=>$id,'title'=>'Change Profile Image'));


    }

    public function ProfileImageUpload(Request $request)
    {
    	//$path = $request->file('avatar')->store('avatars');
    	$avatarName = Auth::user()->name.Auth::user()->id;
    	//delete if file exists
    	Storage::delete($avatarName);
    	//Get file ext
        if($request->file('avatar'))
        {
    	$ext = $request->file('avatar')->getClientOriginalExtension();
              
        //upload image

        //file('avatar') ->store('images/avatars');

    	$path = $request->file('avatar')->storeAs('public', $avatarName.".".$ext); 


    	Userprofile::where('user_id',Auth::user()->id)->update(array('avatar'=>$path));

           return Redirect::route('profile.show',Auth::user()->id)->with('message','Your new profile image successfully updated!');
       }

       /*

       $imagePath = $request->file('image')->store('public');
    $image = Image::make(Storage::get($imagePath))->resize(320,240)->encode();
    Storage::put($imagePath,$image);

    $myTheory->image = $imagePath;*/

    }

   

}

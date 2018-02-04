<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
//use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\Middleware\ErrorBinder;
use Session;
use App\County;
use App\Member;
use App\Shareholdernextofkin;
use App\Http\CustomClass\MembershipNumberGenerator;
use Carbon\Carbon;
use Validator;
use DB;
use App\Constituency;
use App\EducationLevel;

class TrashController extends Controller
{
    //
    public function MembersTrashList()
    {
    	//Lists deleted members from Member class Model
     $deleted_members= Member::onlyTrashed()->get();
     return view('members.trash.members_trash',
     	array('title'=>'Deleted Members',
     		'deleted_members'=>$deleted_members));

    }
    public function RestoreMember($id)
    {
    	//Restore a single model record in Member Class Model
    	$member = Member::withTrashed()->find($id);
    	$member->restore();
    	return redirect()->back()->with('message','Record Restored Successfully!');
    }

    public function RestoreAllMembers()
    {
    	//Restore all trashed records in Member Class Model
    	$members = Member::withTrashed()->restore();
    	return redirect()->back()->with('message','Records Restored Successfully!');
    }
    public function DeleteMemberPermanently($id)
    {
    	$member = Member::withTrashed()->find($id)->forceDelete();
    	    	return redirect()->back()->with('message','Record permanently Deleted');

    }
    public function EmptyMembersTrash()
    {
    	$members = Member::onlyTrashed()->forceDelete();
    	    	return redirect()->back()->with('message','Member Trash Cleared!');


    }

    public function SharesTrashList()
    {
    	  $deleted_shares= Share::onlyTrashed()->get();
     return view('shares.trash.trash',
     	array('title'=>'Deleted Share records',
     		'deleted_shares'=>$deleted_shares));
    	
    }
    public function NextOfKinTrashList()
    {
    	 $delete_kins= Share::onlyTrashed()->get();
     return view('shareholders.trash.trash',
     	array('title'=>'Deleted Shareholder Next of Kins',
     		'delete_kins'=>$delete_kins));
    	
    }
}

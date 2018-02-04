<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Pagination\Paginator;
use App\Report;
use App\Member;
use App\Share;
use DB;
use Carbon\Carbon;
use Validator;
use PDF;
use Excel;
use App\County;
use App\Ward;
use App\Constituency;
use App\MembersGrainInventoryCategory;
use App\MembersGrainInventory;

class InventoryReportsController extends Controller
{
    //
      public function __construct() 
    {
$this->middleware(['auth']);




     } 

    public function inventoryByCategory()
    {
    	   $categories = MembersGrainInventoryCategory::with('inventory')
           ->get();
           if(!$categories)
          {
            return Redirect::back()->withErrors('No Results was found, Try again later!');
          }
        
           return view('reports.inventory.categoriesWithInventory',array('members'=>$categories,'title'=>'Categories With Inventory'));


    }

    public function inventoryByMembers()
    {
    	$members = Member::with('inventory')

    	->get();
        if(!$members)
          {
            return Redirect::back()->withErrors('No Results was found, Try again later!');
          }
    	return view('reports.inventory.membersWithInventory',array('members'=>$members,'title'=>'Members With Inventory'));

    }
}

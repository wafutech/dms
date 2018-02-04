<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\Middleware\ErrorBinder;
use Session;
use Validator;
use App\MembersGrainInventory;
use App\MembersGrainInventoryCategory;


class MembersGrainInventoryCategoryController extends Controller
{
    
      public function __construct() 
    {
$this->middleware(['auth']);



     } 
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $inventory_categories = MembersGrainInventoryCategory::all();
        return view('inventory.categories.index',array('title'=>'Inventory Categories','categories'=>$inventory_categories));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('inventory.categories.create',array('title'=>'Add inventory Category'));

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
          'description'         => 'required',
          'inventory_category_name'      => 'required', 
                   
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      }
        $inventory_categories = Input::all();
        $save =  MembersGrainInventoryCategory::create($inventory_categories);
     $message = "Inventory Category added successfully";
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
        $category= MembersGrainInventoryCategory::findOrFail($id);
        return view('inventory.categories.show',array('category'=>$category,'title'=>'Inventory Category View'));
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
        $category= MembersGrainInventoryCategory::findOrFail($id);
        return view('inventory.categories.edit',array('category'=>$category,'title'=>'Edit Inventory Category'));
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
        $category = MembersGrainInventoryCategory::findOrFail($id);
          $input = $request->all();
         $category->fill($input)->save();
            Session::flash('message', 'Category successfully updated!');

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

          $category = MembersGrainInventoryCategory::findOrFail($id);
    $category->delete();
    Session::flash('message', 'The category entry has been permanently deleted!');

    return redirect()->route('category.destroy');
    }
}

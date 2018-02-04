<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\Middleware\ErrorBinder;
use Illuminate\Http\Request;
use Validator;
use App\MembersGrainInventory;
use App\MembersGrainInventoryCategory;
use Session;


class MembersGrainInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct() 
    {
        $this->middleware('auth');
    $this->categories =  MembersGrainInventoryCategory::pluck('inventory_category_name','id');

    }
    public function index()
    {
        //
         $inventories = MembersGrainInventory::all();
        return view('inventory.index',array('title'=>'Receive Inventory','inventories'=>$inventories));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
 return view('inventory.create',array('title'=>'Add inventory ','categories'=>$this->categories));

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
          'member_id'         => 'bail|required|numeric|exists:members,member_registration_number',
          'inventory_description'         => 'required',
          'inventory_category_id'      => 'required|numeric', 
          'units'      => 'required',  
          'number_of_units'      => 'required|numeric',  
          'unit_cost'      => 'required|numeric',   
          
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      }
        
        $inventories = New MembersGrainInventory;
        $inventories->member_id = $request->input('member_id');
        $inventories->inventory_description = $request->input('inventory_description');
        $inventories->inventory_category_id = $request->input('inventory_category_id');
        $inventories->units = $request->input('units');
        $inventories->number_of_units = $request->input('number_of_units');
        $inventories->unit_cost = $request->input('unit_cost');
        $inventories->amount = $inventories->number_of_units*$inventories->unit_cost;
        $inventories->received_by =1;
        $inventories->save();


     $message = "Inventory  saved successfully";
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
        $inventories= MembersGrainInventory::findOrFail($id);
        return view('inventory.show',array('inventory'=>$inventories,'title'=>'Inventory'));
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
        $inventories= MembersGrainInventory::findOrFail($id);
        return view('inventory.edit',array('inventory'=>$inventories,'title'=>'Inventory','categories'=>$this->categories));
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
         $inventory = MembersGrainInventory::findOrFail($id);
          $input = $request->all();
         $inventory->fill($input)->save();
            Session::flash('message', 'inventory details successfully updated!');

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
         $inventory = MembersGrainInventory::findOrFail($id);
    $inventory->delete();

    return redirect()->back()->with('message','The inventory entry has been permanently deleted!');
    }
}

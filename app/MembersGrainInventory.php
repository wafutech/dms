<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MembersGrainInventory extends Model
{
    //
    protected $guarded = ['id'];
    protected $table ='members_grain_inventory';
    protected $fillable = array('id','member_id','inventory_description',
    	'inventory_category_id','units','number_of_units',
    	'unit_cost','amount','received_by');
    public function received_by()
    {
    	return $this->belongsTo('App\Employee');
    }

    public function Member()
    {
    	return $this->belongsTo('App\Member');
    }


     public function inventory()
    {
    	return $this->belongsTo('App\MembersGrainInventory');
    }

    /*public function getAmountAttribute()
    {
        return $this->amount;
    }*/
}

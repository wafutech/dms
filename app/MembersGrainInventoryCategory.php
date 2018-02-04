<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MembersGrainInventoryCategory extends Model
{
    //
    protected $table ='members_grain_inventory_category';
    protected $fillable = array('inventory_category_name',
    	'description');
    public function inventory()
    {
    	return $this->hasMany('App\MembersGrainInventory','inventory_category_id');
    }

    
}

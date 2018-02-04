<?php
 namespace App;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
		protected $fillable = ['name','display name','description'];

	public function roles()
    {
        return $this->belongsToMany('App\Role');
    }
}
<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class County extends Model
{
    //
    protected $guarded = ['id'];
    protected $table ='counties';
    protected $fillable = array('id','county_name');

   /* public function Member();
    {
    	return $this->hasMany('App/Member','id');
    }*/
    public function constituencies()
    {
    	return $this->hasMany('App\Constituency');
    }

}
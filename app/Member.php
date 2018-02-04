<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    //
        use SoftDeletes;
   
    protected $table ='members';
    protected $primaryKey ='member_registration_number';
    public $incrementing = false;
    protected $dates = ['year_of_birth','registration_date','deleted_at'];
    protected $fillable = array('id','name','year_of_birth','sex',
    	'landsize','idnumber','phone_contact','email','postal_address',
    	'postal_code','town','phisical_address','county_id','sub_county','ward_id',
    	'education_level','occupation','skills','registration_fee','receipt_no',
    	'member_registration_number','user_id','imported','registration_date','constituency_id');
    

    //Define relationship with related data models

public function County()
{
	return $this->belongsTo('App\County');
}

public function Constituencies()
{
    return $this->belongsTo('App\Constituency','id');
}

public function wards()
{
    return $this->belongsTo('App\Ward','id');
}
public function skills()
{
    return $this->hasMany('App\Skill','member_number');
}

public function Share()
{
	return $this->hasMany('App\Share','member_number');
}
public function shareholdernextofkin()
{
    return $this->hasOne('App\shareholdernextofkin','member_number');
}
public function education()
{
    return $this->belongsTo('App\EducationLevel');
}
public function inventory()
{
    return $this->hasMany('App\MembersGrainInventory');
}

//search database
public function scopeSearchByKeyword($query,$keyword)
{
    if($keyword !='')
    {
    $query->where(function($query)
     use ($keyword)
     {
        $query->where("name","like","%keyword%")
        ->orWhere("member_registration_number","like","%$keyword%")
        ->orWhere("email","like","%$keyword%")
        ->orWhere("idnumber","like","%$keyword%")
        ->orWhere("phone_contact","like","%$keyword%")
        ->orWhere("county_id","like","%$keyword%")
        ->orWhere("sub_county","like","%$keyword%")
        ->orWhere("ward","like","%$keyword%");
        });   
    
     

    }
    return $query;

}

}

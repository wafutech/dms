<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class Share extends Model
{
    //
    use SoftDeletes;
    protected $table ='shares';
    protected $primaryKey ='id';
    public $incrementing = true;
    protected $dates = ['date_paid','deleted_at'];
    protected $fillable = array('member_number','amount','employee_id','receipt_no','date_paid','imported');
public function Member()
{
	return $this->belongsTo('App\Member','member_registration_number');
}
public function Employee()
{
	return $this->belongsTo('App\Employee');
}

//date and time mutator
    public function getCreatedAtAttribute($date)
    {
       // return $date->diffForHumans(); // Use whatever you want here to format the date, this is just an example
     return Carbon::parse($date)->format('d-m-Y');

    }

}
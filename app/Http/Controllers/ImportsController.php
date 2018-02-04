<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Report;
use App\Member;
use App\Share;
use DB;
use Carbon\Carbon;
use Validator;
use PDF;
use Excel;
use App\Http\CustomClass\MembershipNumberGenerator;
use Session;


class ImportsController extends Controller
{
    //
      public function __construct() 
    {
$this->middleware(['auth']);


     } 
    public function membersImportForm()
	{
		return view('members.import_members_from_excel',
			array('title'=>'Import  from Excel'));
	}

	public function importMembersFromExcel()
	{
		if(Input::hasFile('excelfile')){
			$path = Input::file('excelfile')->getRealPath();
			$data = Excel::load($path, function($reader) {
			})->get();
			if(!empty($data) && $data->count()){
				foreach ($data as $key => $value) {
        for($i=0;$i<count($value);$i++)
        {
        						$membershipnumber= New MembershipNumberGenerator;

        $membershipnumber= $membershipnumber->MembershipNumber();
    }

					$insert[] = ['name' => $value->name, 
					'year_of_birth' => $value->year_of_birth,
					'sex'=>$value->sex,
					'landsize'=>$value->landsize,
					'idnumber'=>$value->idnumber,
					'phone_contact'=>$value->phone_contact,
					'email'=>$value->email,
					'postal_address'=>$value->postal_address,
					'town'=>$value->town,
					'postal_code'=>$value->postal_code,
					'phisical_address'=>$value->phisical_address,
					'county_id'=>$value->county_id,
           			'sub_county'=>$value->sub_county,
          			'constituency_id'=>$value->constituency_id,
          			 'ward_id'=>$value->ward_id,
					'education_level'=>$value->education_level,
					'occupation'=>$value->occupation,
					'skills'=>$value->skills,
					'registration_fee'=>$value->registration_fee,
					'receipt_no'=>$value->receipt_no,
					'member_registration_number'=>$membershipnumber,
					'employee_id'=>$value->employee_id,
					'registration_date'=>$value->registration_date,
					'age'=>Carbon::now()->diffInYears($value->year_of_birth)];
				}
				if(!empty($insert)){
					DB::table('members')->insert($insert);
 $message= "Records imported successfully";
 Session::flash('message', $message);
       return redirect()->back();				}
			}
		}
		return back();
	}
}

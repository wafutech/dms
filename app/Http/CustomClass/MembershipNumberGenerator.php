<?php
namespace App\Http\CustomClass;
/*
Generates 4 digit number as member registration number
assigned to each new member on registration
*/

class MembershipNumberGenerator
{
  var $numofSerials = 1;
public function MembershipNumber()
{
	//Generate member unique registration number

	$random= "";
	# LENGTH FORMAT IS:
	# XXXX-YYYY-ZZZZ (length = length + 1, so 3 would be 4 characters)
	$length = 3;
	srand((double)microtime()*1000000);
	
	# DATA VARIABLE HOLDS THE CHARS FOR NUMBER GENERATION
	# SIMPLY EDIT  TO HAVE A WIDER RANGE
	# THE WIDER THE RANGE, THE LESS PROBABLE THAT YOU HAVE DUPES

	$data="0123456789";
	

	$num = 0;
	$num2 = $length / 4;
	$num3 = 0;
	for($i = 0; $i < $length + $num2; $i++){
		if($num == 4){
			$num4 = $length + $num2;
			if($num3 == $num4 - 1)	{
			} else {
				$random .= '-';
				$num = 0;
				$num3++;
			}

		} 

		else 
		{
			$random .= strtoupper(substr($data, (rand()%(strlen($data))), 1));
			$num++;
			$num3++;
		}
	}
	 
	return $random;

	for($i = 0; $i < $this->numofSerials; $i++){
	$num = serialnum();
	$membership_number .= $num.", ".$num;
	$serial_number .= "\015\012";

return $membership_number;
}
	

}

}
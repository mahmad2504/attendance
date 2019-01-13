<?php

require 'PhpSpreadsheet/vendor/autoload.php';
require 'calendar.php';
define('DATABASE_FOLDER','../data/database');
define('UPLOAD_FOLDERR','../data/upload');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Style;
$result = new StdClass();

$inst = new StdClass();
$inst->report_month = null;
$inst->employees = array();
$inst->departments = array();
$inst->managers = array();
$inst->working_days = array();
$inst->holidays = array();
$inst->nationalholidays = array();
if(!isset($_SESSION))
	session_start();

if (!isset($_SESSION['access_token'])) {
	header('Location: ../login.php');
	exit();
}

//LoadData('PK02 Absence Report 123118_Sent 010219.xlsx');
//LoadData('Lahore Attendance Report-dec.xlsx');
//return;

if(isset($_GET['year'])&&isset($_GET['month']))
{
	$year = $_GET['year'];
	if(isset($_GET['month']))
		$month = $_GET['month'];
	else
	{
		$result->status = 'FAIL';
		$result->error = "Month paraemeter not given";
		echo json_encode($result);
		return;
	}
	$datestr =  '1 '.$year.' '.$month;
	$date = DateTime::createFromFormat('d Y M',$datestr);
	if($date == null)
	{
		$result->status = 'FAIL';
		$result->error = "Invalid input parameters";
		echo json_encode($result);
		return;
	}
	$infolder = DateTime::createFromFormat('d Y M',$datestr)->format("Y");
	$infile = DateTime::createFromFormat('d Y M',$datestr)->format("M");
	if(file_exists(DATABASE_FOLDER."/".$infolder."/".$infile))
	{
		$inst = unserialize(file_get_contents(DATABASE_FOLDER.'/'.$infolder."/".$infile));
	}
	else
	{
		$result->status = 'FAIL';
		$result->error = "Data Not Found";
		echo json_encode($result);
		return;
	}
}
else if(isset($_GET['file']))
{
	if(file_exists(UPLOAD_FOLDERR."/".$_GET['file']))
		LoadData(UPLOAD_FOLDERR.'/'.$_GET['file']);
	else
	{
		$result->status = 'FAIL';
		$result->error = "Data Not Found";
		echo json_encode($result);
	}
}
else
{
	$infolder = date("Y");
	$infile = date("M");
	if(file_exists(DATABASE_FOLDER."/".$infile))
	{
		$inst = unserialize(file_get_contents(DATABASE_FOLDER.'/'.$infolder."/".$infile));
	}
	else
	{
		$result->status = 'FAIL';
		$result->error = "Data Not Found";
		echo json_encode($result);
		return;
	}
}



function LoadData($file)
{
	global $inst;

	/*$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
	$spreadsheet = $reader->load($file);
	$sheetData = $spreadsheet->getActiveSheet()->toArray();*/

	$retval = LoadInstance($file);
	//var_dump($inst);
	
	if($retval == 1)
		LoadEntryExitData($file);
	
	else if($retval == 2)
		LoadFTORecord($file);
	
	else
	{
		$a =  new StdClass();
		$a->status = 'FAIL';
		$a->message = 'Please Upload Attendance Data First';
		echo json_encode($a);
		exit();
	}
	
	//var_dump($inst);
	
	/*if($sheetData[0][0] == 'Lahore Monthly Attendance Report')
	{
		LoadEntryExitData($file);
	}
	
	if($sheetData[0][0] == 'Name of employee or applicant')
		LoadFTORecord($file);*/
}
function LoadInstance($file)
{
	global $inst;
	$loaded =  false;
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
	$spreadsheet = $reader->load($file);
	$sheetData = $spreadsheet->getActiveSheet()->toArray();
	if($sheetData[0][0] == 'Lahore Monthly Attendance Report')
		return 1;

	else if($sheetData[0][0] == 'Name of employee or applicant')
	{
		foreach($sheetData as $data)
		{
			$bool = ( !is_int($data[18]) ? (ctype_digit($data[18])) : true );
			if( $bool)
			{
				$date  = $data[2];
				$infolder =  DateTime::createFromFormat("m/d/Y", $date)->format("Y");
				$infile = DateTime::createFromFormat("m/d/Y", $date)->format("M");

				if(file_exists(DATABASE_FOLDER.'/'.$infolder."/".$infile))
				{
					//echo "Loading Instance";
					$inst = unserialize(file_get_contents(DATABASE_FOLDER.'/'.$infolder."/".$infile));
					return 2;
				}
				return -1;
				//else
				//{
				//	$inst->report_month = DateTime::createFromFormat("m/d/Y", $date)->format("Y-m-d");
				//}
				
			}
		}
	}
}
function LoadFTORecord($file)
{
	global $inst;

	$loaded =  false;
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
	$spreadsheet = $reader->load($file);
	$sheetData = $spreadsheet->getActiveSheet()->toArray();
	//var_dump($sheetData);
	
	foreach($sheetData as $data)
	{
		$bool = ( !is_int($data[18]) ? (ctype_digit($data[18])) : true );
		if( $bool)
		{
			//echo "-->". $data[1]." ".$data[2]." ".$data[5].'<BR>';
			$badgeno = $data[1];
			$date  = $data[2];
		
			$hours = $data[5];
			if(array_key_exists($badgeno,$inst->employees))
			{
				$employee = $inst->employees[$badgeno];
				$employee->appliedftos = array();
			}
		}
	}
	
	
	
	foreach($sheetData as $data)
	{
		//echo $data[1]." ".$data[5].'<BR>';
		
		$bool = ( !is_int($data[18]) ? (ctype_digit($data[18])) : true );
		
		if( $bool)
		{
			//echo "-->". $data[1]." ".$data[2]." ".$data[5].'<BR>';
			$badgeno = $data[1];
			$date  = $data[2];
		
			$hours = $data[5];
			if(array_key_exists($badgeno,$inst->employees))
			{
				$employee = $inst->employees[$badgeno];
				if($hours >= 8)
				{
					$date = DateTime::createFromFormat('m/d/Y', $date);
					$employee->appliedftos[] = $date->format('Y-m-d');
				}
			}
		}
	}
	$folder = DateTime::createFromFormat("Y-m-d", $inst->report_month)->format("Y");
	$file = DateTime::createFromFormat("Y-m-d", $inst->report_month)->format("M");
	
	//echo $folder.$file."<br>";
	$path = DATABASE_FOLDER."/".$folder;
	if (!file_exists($path)) 
		mkdir($path, 0777, true);
	
	file_put_contents($path."/".$file,serialize($inst));

}
function LoadEntryExitData($file)
{
	global $inst;
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
	$spreadsheet = $reader->load($file);
	$sheetData = $spreadsheet->getActiveSheet()->toArray();
	
	foreach($sheetData as $data)
	{
		//foreach($data as $cell)
		{
			if($inst->report_month  == null)
			{
				$cell = $data[0];
				$cell = "1 ".$cell;
				$date = DateTime::createFromFormat('d Y M', $cell);
				if($date  != null)
				{
					$inst->report_month = $date->format('Y-m-d');
				}
			}
			if($inst->report_month == null)
			{
				continue;
			}
			// Record
			
			// Date
			$cell = $data[1];
			$date = DateTime::createFromFormat('m/d/Y H:i:s A', $cell);
			if($date  != null)
			{
				$date =  $date->format('Y-m-d');
			}
			else
			{
				//echo $cell." is parsed null<br>";
				continue;
			}
			// Badge No
			$cell = $data[5];
			$badgeno = trim($cell);
			
			// Employee name
			$cell = $data[4];
			$name = trim($cell);
			
			//Department Name
			$cell = $data[8];
			$department_name = trim($cell);
			
			//Manager Name
			$cell = $data[9];
			$manager_name = trim($cell);
			
			//Timein
			$cell = $data[10];
			$timein = trim($cell);
			
			//Timeout
			$cell = $data[11];
			$timeout = trim($cell);
			
			//Duration
			//echo  gmdate("H:i:s", strtotime($timeout)-strtotime($timein)).'<br>';

			//$cell = $data[12];  // Retrives wrong data
			//$duration = trim($cell);
			
			$duration = strtotime($timeout)-strtotime($timein); // Work Around
			
			
			if(array_key_exists($badgeno,$inst->employees))
			{
				$employee = $inst->employees[$badgeno];
			}
			else
			{
				$employee = new StdClass();
				$employee->name = $name;
				$employee->attendance = array();
				$employee->appliedftos = array();
				$inst->employees[$badgeno] = $employee;
			}
			$attendance =  new StdClass();	
			$attendance->timein = $timein;
			$attendance->timeout = $timeout;
			$attendance->duration = $duration;
			
			$employee->attendance[$date] = $attendance;

			if(array_key_exists($department_name,$inst->departments))
			{
				$department = $dinst->epartments[$department_name];
			}
			else
			{
				$department = new StdClass();
				$department->name = $department_name;
				$departments[$department_name] = $department;
			}
			$department->employees[$employee->name] = $employee;
			$employee->department = $department;
			
		
			if(array_key_exists($manager_name,$inst->managers))
			{
				$manager = $inst->managers[$manager_name];
			}
			else
			{
				$manager = new StdClass();
				$manager->name = $manager_name;
				
				$inst->managers[$manager_name] = $manager;
			}
			/*if($badgeno == 36981)
			{
				echo $manager->name.'<br>';

			}*/
			
			$manager->employees[$badgeno] = $employee;
			$employee->manager = $manager;
		}
	}
	$calendarconfig = LoadCalendarConfiguration('../calendar.config');
	$report_end = date("Y-m-d", strtotime("+1 months",strtotime($inst->report_month)));


	$month_calendar = new Calendar($calendarconfig,$inst->report_month,$report_end);
	ComputeMonthWorkingDays($month_calendar,$inst->report_month,$report_end );

	//var_dump($inst->holidays);


	MarkFTO($inst->employees,$inst->working_days);
	ComputeAverageInAndOut($inst->employees);
	ApplyAverages($inst->employees);
	
	$folder = DateTime::createFromFormat("Y-m-d", $inst->report_month)->format("Y");
	$file = DateTime::createFromFormat("Y-m-d", $inst->report_month)->format("M");
	
	$path = DATABASE_FOLDER."/".$folder;
	if (!file_exists($path)) 
		mkdir($path, 0777, true);
	
	file_put_contents($path."/".$file,serialize($inst));
}

if($_SESSION['userid'] ==0)
	return array();
$employeeid = $_SESSION['userid'];

$a[] = $inst->report_month;
$a[] = $inst->nationalholidays;

if($_SESSION['admin'] ==1)
{
	GetAllData($inst->managers);
	echo json_encode($a);
	return;
}

if(array_key_exists($employeeid,$inst->employees))
{
	$employee = $inst->employees[$employeeid];
	GetEmployeeData($employee);
}
$employee = $inst->employees[$employeeid];

if(array_key_exists($employee->name,$inst->managers))
{
	$manager = $inst->managers[$employee->name];
	GetManagersData($manager);
}
echo json_encode($a);
return;

PrintManagersData($inst->managers);


function GetManagersData($manager)
{
	global $inst;
	global $a;

	if(1)
	{
		//echo "<h1>".$manager->name."</h1>";
		$m = new StdClass();
		$m->name = $manager->name;
		$m->employees = array();
		foreach($manager->employees as $badge=>$employee)
		{
			$e = new StdClass();
			$e->attendancemissing = 0;
			$e->name = $employee->name;
			$m->employees[] = $e;
			//echo $employee->name." FTO(";
			$fto = array();
			foreach($employee->attendance as $date=>$attendance)
			{
				if($attendance == null)
				{
					$fto[] = $date;
					
				}
			}
			$e->fto = $fto;
			$e->appliedftos = $employee->appliedftos;
			foreach($employee->attendance as $date=>$attendance)
			{
				if($attendance != null)
				{
					$e->attendance[$date] = new StdClass();
					$e->attendance[$date]->timein = $attendance->timein;
					$e->attendance[$date]->timeout = $attendance->timeout;
					if($attendance->duration == 0)
					{
						if(array_key_exists($date,$inst->working_days))
							$e->attendancemissing++;
					}
					$e->attendance[$date]->duration = gmdate("H:i:s",$attendance->duration);
					//echo $date.' '.$attendance->timein.' '.$attendance->timeout.' '.gmdate("H:i:s",$attendance->duration).'<br>';
				}
				
			}
			$e->averagein = $employee->averagein;
			$e->averageout = $employee->averageout;
			$e->averageduration = $employee->averageduration;
			//echo '<br>Average Time-in  '.$employee->averagein.'<br>';
			//echo 'Average Time-out  '.$employee->averageout.'<br>';
			//echo 'Average Duration '.$employee->averageduration.'<br>';
			//echo '<br>';
		}
		$a[] = $m;
	}
	return $a;
}

function GetEmployeeData($employee)
{
	global $inst;
	global $a;

	
	$m = new StdClass();
	$m->name = $employee->manager->name;
	$m->employees = array();
	
	$e = new StdClass();
	$e->attendancemissing = 0;
	$e->name = $employee->name;
	$m->employees[] = $e;
			
	$fto = array();
	foreach($employee->attendance as $date=>$attendance)
	{
		if($attendance == null)
		{
			$fto[] = $date;
			
		}
	}
	$e->fto = $fto;
	$e->appliedftos = $employee->appliedftos; 
	foreach($employee->attendance as $date=>$attendance)
	{
		if($attendance != null)
		{
			$e->attendance[$date] = new StdClass();
			$e->attendance[$date]->timein = $attendance->timein;
			$e->attendance[$date]->timeout = $attendance->timeout;
			if($attendance->duration == 0)
			{
				if(array_key_exists($date,$inst->working_days))
					$e->attendancemissing++;
			}
			$e->attendance[$date]->duration = gmdate("H:i:s",$attendance->duration);
			//echo $date.' '.$attendance->timein.' '.$attendance->timeout.' '.gmdate("H:i:s",$attendance->duration).'<br>';
		}
		
	}
	$e->averagein = $employee->averagein;
	$e->averageout = $employee->averageout;
	$e->averageduration = $employee->averageduration;
	$a[] = $m;

	return $a;
}


function GetAllData($managers)
{
	global $inst;
	global $a;
	/*$result->status = 'FAIL';
		$result->error = "Data Not Found";
		echo json_encode($result);*/
	//$array = array();

	foreach($managers as $manager)
	{
		//echo "<h1>".$manager->name."</h1>";
		$m = new StdClass();
		$m->name = $manager->name;
		$m->employees = array();
		foreach($manager->employees as $badge=>$employee)
		{
			$e = new StdClass();
			$e->attendancemissing = 0;
			$e->name = $employee->name;
			$m->employees[] = $e;
			//echo $employee->name." FTO(";
			$fto = array();
			foreach($employee->attendance as $date=>$attendance)
			{
				if($attendance == null)
				{
					$fto[] = $date;
					
				}
			}
			$e->fto = $fto;
			$e->appliedftos = $employee->appliedftos; 
			foreach($employee->attendance as $date=>$attendance)
			{
				if($attendance != null)
				{
					$e->attendance[$date] = new StdClass();
					$e->attendance[$date]->timein = $attendance->timein;
					$e->attendance[$date]->timeout = $attendance->timeout;
					if($attendance->duration == 0)
					{
						if(array_key_exists($date,$inst->working_days))
							$e->attendancemissing++;
					}
					$e->attendance[$date]->duration = gmdate("H:i:s",$attendance->duration);
					//echo $date.' '.$attendance->timein.' '.$attendance->timeout.' '.gmdate("H:i:s",$attendance->duration).'<br>';
				}
				
			}
			$e->averagein = $employee->averagein;
			$e->averageout = $employee->averageout;
			$e->averageduration = $employee->averageduration;
			//echo '<br>Average Time-in  '.$employee->averagein.'<br>';
			//echo 'Average Time-out  '.$employee->averageout.'<br>';
			//echo 'Average Duration '.$employee->averageduration.'<br>';
			//echo '<br>';
		}
		$a[] = $m;
	}
}

function ApplyAverages($employees)
{
	foreach($employees as $employee)
	{
		foreach($employee->attendance as $date=>$attendance)
		{
			if($attendance != null)
			{
				if($attendance->duration == 0)
				{
					$employee->attendance[$date]->computed =  new StdClass();
					$employee->attendance[$date]->computed->timein = $employee->averagein;
					$employee->attendance[$date]->computed->timeout = $employee->averageout;
					$employee->attendance[$date]->computed->duration =  $employee->averageduration;
				}
			}
		}
	}
}
function ComputeMonthWorkingDays($calendar,$start,$end)
{
	global $inst;
	$begin = new DateTime($start);
	$end = new DateTime($end);
	$interval = DateInterval::createFromDateString('1 day');
	$period = new DatePeriod($begin, $interval, $end);
	foreach ($period as $dt) 
	{
		if(($dt->format("l")=='Saturday')||($dt->format("l")=='Sunday'))
		{
			$inst->holidays[$dt->format("Y-m-d")] = $dt->format("l");
		}
		else
		{
			$isitholiday = 0;
			foreach($calendar->calendars['pakistan'] as $date=>$holiday)
			{
				if($dt->format("Y-m-d") == $date)
				{
					$inst->nationalholidays[$date] = $holiday;
					$inst->holidays[$date] = $holiday;
					$isitholiday = 1;
					break;
				}
			}
			if($isitholiday == 0)
				$inst->working_days[$dt->format("Y-m-d")] = $dt->format("Y-m-d");
		}
	}
}
function ComputeAverageInAndOut($employees)
{
	foreach($employees as $employee)
	{
		$acc_timeins = 0;
		$acc_timeouts = 0;
		$count = 0;
		foreach($employee->attendance as $date=>$attendance)
		{
			if($attendance != null)
			{
				if($attendance->duration > 0)
				{
					sscanf($attendance->timein, "%d:%d:%d", $hours, $minutes, $seconds);
					$acc_timeins += $hours * 3600 + $minutes * 60 + $seconds;
					sscanf($attendance->timeout, "%d:%d:%d", $hours, $minutes, $seconds);
					$acc_timeouts += $hours * 3600 + $minutes * 60 + $seconds;
					$count++;
				}
			}
		}
		
		if($count > 0)
		{
			//echo "Average for ".$employee->name."<br>";
			$employee->averagein = gmdate("H:i:s", round($acc_timeins/$count));
			$employee->averageout = gmdate("H:i:s", round($acc_timeouts/$count));
			$employee->averageduration = gmdate("H:i:s", round($acc_timeouts/$count)-round($acc_timeins/$count)); 
			//echo gmdate("H:i:s", round($acc_timeins/$count)).'<br>';
			//echo gmdate("H:i:s", round($acc_timeouts/$count)).'<br>';
		}
		else
		{
			//echo "Average for ".$employee->name." is none<br>";
			$employee->averagein = '-';
			$employee->averageout = '-';
			$employee->averageduration = 0;
		}	
	}
}
function MarkFTO($employees,$working_days)
{
	foreach($employees as $employee)
	{
		/*if('Ahmad, Mumtaz' == $employee->name)
			echo $employee->name.'<br>';
		
		var_dump($working_days);*/
		foreach($working_days as $date)
		{
			/*if('Ahmad, Mumtaz' == $employee->name)
				echo $date.'<br>';*/
			if(array_key_exists($date,$employee->attendance))
			{
				// the person came that day
			}
			else
			{
				//echo $date." is holiday<br>";
				$employee->attendance[$date] = null;
			}
		}
	}
}

function LoadCalendarConfiguration($conffile)
{
	if(!file_exists($conffile))
	{
		$msg =  "Configuration for $conffile not found";
		echo $msg;
		exit();
	}
	//$xmldata = file_get_contents(getcwd()."//".$conffile);
	$conf = json_decode(file_get_contents($conffile));
	return $conf;
}

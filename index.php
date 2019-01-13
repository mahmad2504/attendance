<?php

define('DATABASE_FOLDER','data/database');

session_start();
if (!isset($_SESSION['access_token'])) {
	header('Location: login.php');
	exit();
}
	
$years = ReadDirectory(DATABASE_FOLDER,1);
usort($years,"compare_years");
$database = array();
foreach($years as $year)
{
	$months = ReadDirectory(DATABASE_FOLDER.'/'.$year);
	if( count($months)>0)
	{
		$database[$year] = $months;
		usort($database[$year],"compare_months");
	}
}

$report_year = key($database);
$report_month = '';
if($report_year != null)
	$report_month = $database[$report_year][0];
else
	$report_year = '';

function compare_years($a, $b) {
	$a=$a."-1-1";
	$b=$b."-1-1";
	
	//echo $a."  ".strtotime($a)."<br>";
	//echo $b."  ".strtotime($b)."<br>";
	
	return strtotime($a)<strtotime($b);
    //return $monthA["month"] - $monthB["month"];
}
function compare_months($a, $b) {
	global $year;

	$a=$year."-".$a."-1";
	$b=$year."-".$b."-1";
	
	//echo $a."  ".strtotime($a)."<br>";
	//echo $b."  ".strtotime($b)."<br>";
	
	return strtotime($a)<strtotime($b);
    //return $monthA["month"] - $monthB["month"];
}
function ReadDirectory($directory,$lookfordir=0)
{
	$files = array();
	$dir = opendir($directory); // open the cwd..also do an err check.
	while(false != ($file = readdir($dir))) 
	{
		if(($file != ".") and ($file != "..")) 
		{
			//echo $file." ".is_dir($directory.$file).EOL;
			//echo  is_dir($directory."//".$file).EOL;
			if($lookfordir==1)
			{
				if(is_dir($directory."//".$file))
					$files[] = $file; // put in array.
			}
			else
			{
				//echo $file.'<BR>';
				if(!is_dir($directory."//".$file))
					$files[] = $file; // put in array.
			}
		}
		//natsort($files); // sort.
	}
	return $files;
}
?>
<html>
    <head>
        <title>Attendance</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
	
		<link rel="icon" href="assets/images/icon.png">
		<link rel="stylesheet" href="assets/css/bootstrap.min.css" crossorigin="anonymous">
		
        <script src="assets/js/jquery.min.js" type="text/javascript" crossorigin="anonymous"></script>
		<script src="assets/js/popper.min.js" type="text/javascript" crossorigin="anonymous"></script>
		<script src="assets/js/bootstrap.min.js" type="text/javascript" crossorigin="anonymous"></script>
		
        <!-- INCLUDES -->
        <link rel="stylesheet" href="assets/css/bootstrap-table-expandable.css">
        <script src="assets/js/bootstrap-table-expandable.js"></script>
		<script src="assets/js/app.js"></script>
		<script src="assets/js/bs-custom-file-input.min.js"></script>
		<script src="assets/js/jquery.ajaxfileupload"></script>
		<script src="assets/js/bootstrap-waitingfor.js"></script>
		<script src="assets/js/font-awesone.js"></script>
		<script>
		var userid = <?php echo $_SESSION['userid']; ?>;
		var admin = <?php echo $_SESSION['admin']; ?>;
		var report_year = '<?php echo $report_year;?>';
		var report_month = '<?php echo $report_month;?>';
		var years =  [<?php $del='';
		foreach($years as $year)
		{
			echo $del."'".$year."'";
			$del = ",";
		}
		?>];
		var months = [];
		<?php 
			for($i=0;$i<count($years);$i++)
			{
				echo 'months['.$i.']=[';
				$del = '';
				foreach($database[$year] as $month)
				{
					echo $del."'".$month."'";
					$del = ",";
				}
				echo '];';
			}
		?>
		</script>
    </head>
    <body>
	
	<div class="container">
			<div style="margin-top: 22px;" class="row">
				<div class="col-lg-1">
					<img style="width: 30;" src="<?php echo $_SESSION['picture'] ?>">
				</div>
				<div class="col-lg-5">
					<h3 class="float-left">Attendance Report </h3>
				</div>
				<div class="col-lg-6">
					<button id="logout" type="button" class="btn btn-outline-success float-right">Logout</button>
				</div>
			</div>
			<hr/>
			<div class="row">
				<div class="form-group col-lg-2">
					<div class="text-muted">Years</div>
					<div class="years"></div> 
				</div>
			
				<div class="form-group col-lg-2">
					<div class="text-muted">Months</div>
					<div class="months"></div> 
				</div>
		
				<div style="margin-top: 22px;" class="form-group col-lg-3">
					<input id="reload" class="btn btn-primary" type="submit" value="Load">
				</div>

				<div style="margin-top: 10px;" class="form-group col-lg-5">  
					 <div id="uploaddv" class="input-group mt-3">
						<div class="custom-file">
						  <input  id="file" type="file" class="custom-file-input" accept=".xlsx">>
						  <label class="custom-file-label" for="inputGroupFile01">Choose Excel Sheet (.xlsx)</label>
						</div>
						&nbsp&nbsp
						<input id="upload" class="btn btn-primary" type="submit" value="Upload">
					</div>
				</div>
			</div>
		
			<div id="holidays">
			</div>
		
			<div id="container">
			</div>
	</div>
	
	
	<hr/>
	<footer class="footer">
       <div class="container text-center">
        <span class="text-muted" style="font-size:10px">Comments and suggestions </span>
		<a href="mailto:mumtaz_ahmad@mentor.com">
		<i class="fa fa-envelope fa-1x" ></i>
		</a>
		<span class="text-muted" style="font-size:10px">mumtaz_ahmad@mentor.com</span>
      </div>
    </footer>
    </body>
</html>

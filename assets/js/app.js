var holidays = null;
var report_date  = null;
var reportmonth = null;
var reportyear = null;
var daysinmonth =0;

$( document ).ready(function() {
  console.log('Ready');
  if(admin == 0)
  {
	  $('#uploaddv').empty();
  }
  PopulateYearList();
  PopulateMonthList();
			  
  bsCustomFileInput.init();
  
  Load();
  $('#upload').on('click', UploadFile);
  $('#reload').on('click', Load);
  $('#logout').on('click', Logout);
	

})
function Logout()
{
	window.location.href = 'logout.php';
}
function PopulateYearList()
{
	var list = '<select id="yearid" style="width:100px;" class="form-control onchange="OnYearChange()" >';
	for(var i=0;i<years.length;i++)
	{
		if(report_year == years[i])
			list += '<option value="'+years[i]+'" selected>'+years[i]+'</option>';
		else
			list += '<option value="'+years[i]+'">'+years[i]+'</option>';
	}
	list += '</select>';
	
	$(".years").empty();
	$(".years").append(list);
}
function PopulateMonthList()
{
	var list = '<select id="monthid" style="width:100px;" class="form-control " onchange="OnMonthChange()" >';
	for(var i=0;i<years.length;i++)
	{
		//console.log(report_year);
		//console.log(years[i]);
		if(report_year == years[i])
		{
			for(var j=0;j <months[i].length;j++)
			{
				//console.log(report_month,months[i][j]);
				if(report_month == months[i][j])
					list += '<option value="'+months[i][j]+'" selected>'+months[i][j]+'</option>';
				else
					list += '<option value="'+months[i][j]+'">'+months[i][j]+'</option>';
			}
		}
	}
	list += '</select>';
	$(".months").empty();
	$(".months").append(list);
}
function OnMonthChange()
{
	console.log("On month change");
	report_month =$('#monthid').val();
	console.log(report_month);
}
function OnYearChange()
{
	report_year = $('#yearid').val();
	console.log(report_year);
	PopulateYearList();
	PopulateMonthList();
}


function Load()
{
	var params = {
	  year:report_year,
	  month:report_month
	};
	console.log(report_year);
	console.log(report_month);
	GetResource(0,'src/data.php',params,successcb);
}
function ShowCloseTaskError(errors)
{
	setTimeout( function(){ 
		setTimeout( function(){ waitingDialog.hide();waitingDialog.show('Please.....');}, 2000 );
	}, 100 );
}

function UploadFile()
{
	waitingDialog.show('Uploading.....')
	console.log("uploading");
	var file_data = $('#file').prop('files')[0];
	var form_data = new FormData();
    form_data.append('file', file_data);
	$.ajax({
		url: 'src/upload.php', // point to server-side PHP script 
		dataType: 'text', // what to expect back from the PHP script
		cache: false,
		contentType: false,
		processData: false,
		data: form_data,
		type: 'post',
		success: function (response) {
			console.log('success'); // display success response from the PHP script
			console.log(response);
			window.location.href = 'index.php';
			setTimeout( function(){ waitingDialog.hide();}, 2000 );
		},
		error: function (response) {
			console.log('error'); // display error response from the PHP script
			console.log(response);
			setTimeout( function(){ waitingDialog.hide();}, 2000 );
		}
	});
}

function IsItWeekend(year,month,day)
{
	var myDate = new Date();
	myDate.setFullYear(year);
	myDate.setMonth(month-1);
	myDate.setDate(day);
	//console.log(myDate.getDay());
	if(myDate.getDay() == 6 || myDate.getDay() == 0) 
		return true;
	return false;
}

function IsItHoliday(year,month,day)
{
	//console.log(year,month,day);
	date1 = (new Date(year+"-"+month+'-'+day)).getTime();
	for(var i in holidays) {
		date2= (new Date(i)).getTime();
		if(date1 == date2)
			return true;
	}
	return false;

}


function daysInMonth (month, year) {
    return new Date(year, month, 0).getDate();
}

function GetAttendanceData(employee,datestr)
{
	//console.log(datestr);
	var attendance = employee.attendance;
	var attendancedata = employee.attendance[datestr];
	if(typeof attendancedata == 'undefined')
	{
		//console.log("Not found for "+datestr);
		return null;
	}
	return attendancedata;
}
function AppendDataList(start,end,employee)
{
	var offday=0;
	var html='';
	
	for(var i=start;i<=end;i++)
	{
		var html_this='';
		if(i < 10)
			i = '0'+i;
		offday=0;
		if(IsItWeekend(reportyear,reportmonth,i))
		{
			offday=1;
			html_this ='<li><span class="badge badge-Info">'+i+'</span>';
		}
		else
		{
			if(IsItHoliday(reportyear,reportmonth,i))
			{
				offday=1;
				html_this ='<li><span class="badge badge-Info">'+i+'</span>';
			}
			else 
				html_this ='<li><span class="badge badge-Secondary">'+i+'</span>';
		}
		attendancedata = GetAttendanceData(employee,reportyear+'-'+reportmonth+'-'+i);
		if(attendancedata == null)
		{

			if(offday==0)
			{
				html_this='<li><span class="badge badge-danger">'+i+'</span>';
				//html_this +='<span>No data</span>';
			}
			
			//console.log(employee.name);
			//console.log(reportyear+'-'+reportmonth+'-'+i);
			//console.log(offday);
		}
		else 
		{	
			//console.log(attendancedata.duration);
			if(attendancedata.duration == '00:00:00' && offday==0)
			{
				//console.log(employee.name);
				html_this ='<li><span class="badge badge-warning">'+i+'</span>';
			}
			html_this += '<span style="font-size:10px;">  '+attendancedata.duration.slice(0,5)+'</span>'
		}
		html_this +='</li>';
		html += html_this;
	}

	
	return html;
}

function successcb(response)
{
	var response = JSON.parse(response);
	holidays = response[1];
	report_date = new Date(response[0]);
	console.log(report_date);
	
	reportmonth = report_date.getUTCMonth()+1;
	if(reportmonth < 10)
		reportmonth = '0'+reportmonth;
		
	reportyear = report_date.getUTCFullYear();
	daysinmonth = daysInMonth(report_date.getUTCMonth()+1,report_date.getUTCFullYear());
	$("#container").empty();
	
	//console.log(holidays);
	//console.log(report_datess);
	
	table = '<table class="table table-hover table-expandable table-sticky-header">';
	table += '<thead>';
	table += '<tr>';
		table +='<th style="width: 20%">Name</th>';
		table +='<th style="width: 20%">Missing</th>';
		table +='<th style="width: 20%">Partial</th>';
		table +='<th style="width: 15%">In Time</th>';
		table +='<th style="width: 15%">Out Time</th>';
		table +='<th style="width: 20%">Duration</th>';
	table +='</tr>';
	table +='</thead>';
	table +='</table>';
	$("#container").append(table);
	
	for (var i = 2; i < response.length; i++)
	{
		var manager = response[i];

		var heading  = '<nav class="navbar navbar-light bg-light"><a class="navbar-brand" href="#"><strong>Team '+manager.name+'</strong></a></nav>';
		//heading  += '<div class="alert alert-primary">'+
		//	heading  += '<span class="fa fa-gift mr-1"></span>'+
		//		Checkout out my full collection of<a href="/" class="alert-link">Bootstrap themes and templates</a></div></div>';
		
		
		//var heading  = '<p>Manager <a href="#">'+manager.name+'</a></p>'; 
		$("#container").append(heading);
		var employees = manager.employees;
		for (var j = 0; j < employees.length; j++)
		{
			//console.log(employees[j]);
			var employeename = employees[j].name;
			var ftocount =  employees[j].fto.length;
			var missing =  employees[j].attendancemissing;
			var averagein = employees[j].averagein.slice(0,5);
			var averageout = employees[j].averageout.slice(0,5); 
			var averagedur = employees[j].averageduration;
			if(averagedur == 0)
				averagedur = '00:00';
			else
				var averagedur = employees[j].averageduration.slice(0,5); 
			var table;
			table = '<table id="attendance" class="table table-hover table-expandable table-sticky-header">';
				
				table +='<tbody>';
					table +='<tr>';
						table +='<td style="width: 20%">'+employeename+'</td>';
						if(ftocount > 0)
							badge='<a href="#"> <span class="badge badge-Danger">'+ftocount+'</span></a>';
						else
							badge='';
						
						table +='<td style="width: 20%">'+badge+'</td>';
						if(missing > 0)
							badge='<a href="#"> <span class="badge badge-Warning">'+missing+'</span></a>';
						else
							badge = '';
						table +='<td style="width: 20%">'+badge+'</td>';
						
						table +='<td style="width: 15%">'+averagein+'</td>';
						table +='<td style="width: 15%">'+averageout+'</td>';
						table +='<td style="width: 20%">'+averagedur+' Hours</td>';
					table +='</tr>';
					table +='<tr style="display: none;">';
						table +='<td colspan="5">';
							table +='<p>Manager '+manager.name+'</p>';
							table +='<div class="row">';
							table +='<div class="col-sm">';
							table +='<ul">';
								table +=AppendDataList(1,8,employees[j]);
							table +='</ul>';
							table +='</div>';
							
							table +='<div class="col-sm">';
							table +='<ul">';
								table +=AppendDataList(9,16,employees[j]);
							table +='</ul>';
							table +='</div>';
							
							table +='<div class="col-sm">';
							table +='<ul">';
								table +=AppendDataList(17,24,employees[j]);
							table +='</ul>';
							table +='</div>';
							
							table +='<div class="col-sm">';
							table +='<ul">';
								table +=AppendDataList(25,daysinmonth,employees[j]);
							table +='</ul>';
							table +='</div>';
							
							table +='</div>';
						table +='</td>';
					table +='</tr>';
	
				table +='</tbody>';
			table +='</table>';
			$("#container").append(table);
		}
		 //console.log(manager);
	}
	Decorate(jQuery); 
	console.log('done');
}
function GetResource(identity,cmd,cparam,successcb) 
{
	var identity = identity;
	var param = '';
	var del = '';
	Object.keys(cparam).forEach(function(key) 
	{
		param += del+key+"="+cparam[key];
		del='&';
	});
	console.log(param);
	loc = window.location.href;
	if(cmd != null)
	{
		var parts = window.location.href.split('/');
		var loc = '';
		var del = ''
		for(var i=0;i<parts.length-1;i++)
		{
			loc = loc+del+parts[i];
			del ='/';
		}
		loc = loc+"/"+cmd;
	}

	var url = loc.split('?')[0]+"?"+param;
	$.ajax(
	{     
		headers: { 
			Accept : "text/json; charset=utf-8",
			"identity":identity,
		},
		type: "POST",
		url : url,	
		data: { }, // Our valid JSON string
		success : function(d) 
		{
			//console.log(d);
			successcb(d);
		},
		complete: function() {},
		error: function(xhr, textStatus, errorThrown) 
		{
			console.log('ajax loading error...');
			return false;
		}
	})
}
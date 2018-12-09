<?php
include 'iCal.php';
class Calendar
{
	public $calendars = array();
	function __construct($conf,$startdate,$enddate)
	{
		foreach($conf as $country=>$url)
		{
			$country = strtolower($country);
			$response = get_headers($url);
			$this->calendars[$country] = array();
			if(strpos($response[0],'200')!=FALSE)
			{
				$iCal = new iCal($url);
				
				$events = $iCal->eventsByDateBetween($startdate,$enddate);
				//echo "Event for ".$country.'<br>';
				foreach ($events as $date => $event)
				{
					foreach ($event as $evt)
					{
				
						$evt->dateStart = date('Y-m-d',strtotime($evt->dateStart));
						
						$begin = new DateTime($evt->dateStart);
						$end = new DateTime($evt->dateEnd);

						$interval = DateInterval::createFromDateString('1 day');
						$period = new DatePeriod($begin, $interval, $end);

						foreach ($period as $dt) {
							//echo $dt->format("Y-m-d")."<br>";
							$this->calendars[$country][$dt->format("Y-m-d")]=$evt->summary;
						}

					}
					
				}
			}
			else
				echo $country." calendar not found<br>";
		}
	}
	function Get($country)
	{
		foreach($this->calendars as $ctry=>$cal)
		{
			if($ctry == strtolower($country))
				return $this->calendars[$ctry];
		}
	}
}


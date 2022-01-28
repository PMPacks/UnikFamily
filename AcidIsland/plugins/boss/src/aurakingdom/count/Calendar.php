<?php 

namespace aurakingdom\manager;

class Calendar {

	public static function setTimezone(string $timezone) {
		date_default_timezone_set($timezone);
	}

	public static function ms2date($ms) {
		$date = \DateTime::createFromFormat("U.u", $ms);
		return $date->format("d/m/Y");
	}
	
	public static function getTimezone() {
		return date_default_timezone_get();
	}
	
	public static function isWeekend() {
		return date("l") == "Saturday" || date("l") == "Sunday";
	}
	
	public static function getDateToday() {
		return date("m/d/Y");
	}
	
	public static function isTodayHoliday() {
	 //soon	 
	}
	
	public static function getSeason() {
		$seasons = [
			"/12/21" => "Winter",
			"/09/21" => "Autumn",
			"/06/21" => "Summer",
			"/03/21" => "Spring",
			"/01/01" => "Winter"
		];
		foreach($seasons as $key => $value) {
			$season = date("Y") . $key;
			if(strtotime("now") > strtotime($season)) {
				return $value;
			}
		}
		return false;
	}
}
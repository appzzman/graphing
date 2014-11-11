<?php 

date_default_timezone_set('America/Chicago');

$mysqlnd = function_exists('mysqli_fetch_all');

	if (!$mysqlnd) {
	 die("You have to enable it!");
	}

 //$con = mysqli_connect("localhost","root","root","beacons");

 $con=mysqli_connect('ns2.oxen.arvixe.com','janek211_ibeacon','stany','janek211_ibeacons');
  if(!$con){
	  die("Connection error: " . mysqli_connect_errno());
	 }

 if(!isset($_REQUEST["start"])&&!isset($_REQUEST["stop"])){
  $datetime = new DateTime('2000-01-01');
  $timeline_start=$datetime->format('Y-m-d');
  $datetime = new DateTime('tomorrow');
  $timeline_stop = $datetime->format('Y-m-d');
	$user = $_REQUEST['user'];
}
$clear = false;
if(isset($_REQUEST["ccc"])){
	$clear = true;
}

if(isset($_REQUEST["start"])&&isset($_REQUEST["stop"])){
	 $timestamp1= $_REQUEST["start"];
	 $dt1 = new DateTime($_REQUEST["start"]);
	 $timeline_start = $dt1->format('Y-m-d H:i:s');;
	
	 $dt2 = new DateTime($_REQUEST["stop"]);
	 $timeline_stop = $dt2->format('Y-m-d H:i:s');;
	
	 $user = $_REQUEST['user'];
}
	
	function clearTables(){
		global $con;
		try{
		 mysqli_multi_query($con,"DELETE FROM `wp__region_events` WHERE 1; DELETE FROM `wp__override_events` WHERE 1; DELETE FROM `wp__proximity_events` WHERE 1; DELETE FROM `wp__scan_events` WHERE 1;DELETE FROM `wp__session_events` WHERE 1");
		}
		catch(Exception $e){
			echo $e->getMessage();
			die("Didnt work");
		}
		die("tabula rasa");
	}
	
	if($clear)	clearTables();
	


	function getArray($con, $query){
		$result;
		try{
			$result = mysqli_query($con,$query);
		}	
		catch(Exception $e){
			echo $e->getMessage();
		}
		if(!$result) return array();
	
		$array = mysqli_fetch_all($result, MYSQLI_BOTH);		
		
		return $array;						
	}
	
	function getIbeaconTitle($bid,$arrayofibeacons){
			for($i=0;$i<count($arrayofibeacons);$i++)
		{
			$id= $arrayofibeacons[$i][0];	
			$title= $arrayofibeacons[$i][1];
			if($id == $bid){
				return $title;
			}
		}
			return "Beacon";
		}
	
	 function timeDifference($date_start, $date_end){
	$to_time = strtotime($date_end);
	$from_time = strtotime($date_start);
	
	$number_of_minutes =  round(abs($to_time - $from_time) / 60,2);
	return $number_of_minutes;
 }
	
	
	$arrayofusers = getArray($con,"SELECT * FROM `wp_users`"); ///getUsers();
	$arrayofibeacons =  getArray($con,"SELECT `ID`,`post_title` FROM `wp_posts` WHERE `post_type` = 'ibeacon'");
	
	$proximityArray =getArray($con,"SELECT * FROM wp__proximity_events WHERE event_date > '$timeline_start' AND event_date <'$timeline_stop' AND user ='$user' ORDER BY event_date ");//  getProximityEvents($timeline_start, $user);
	$regionsArray = getArray($con,"SELECT * FROM `wp__region_events` WHERE user = $user");
	$sessionArray = getArray($con,"SELECT * FROM `wp__session_events` WHERE user = $user");
	$scansArray =  getArray($con,"SELECT * FROM `wp__scan_events` WHERE user = $user");
	$overrideArray =  getArray($con,"SELECT * FROM `wp__override_events` WHERE user = $user");
	

	$all_events = array_merge($regionsArray,$sessionArray,$overrideArray,$scansArray);	

 ?>
<?php 
date_default_timezone_set('America/Chicago');

    class Events{
     
	public $con;
	public $user;
	public $timeline_start;
	public $timeline_stop;
	
	  function __construct($start,$stop,$user){
	     //check mysql native drivers version
	      $mysqlnd = function_exists('mysqli_fetch_all');
     
	if (!$mysqlnd) {
	 die("You have to enable it!");
	}
	     
	     $this->con=mysqli_connect('ns2.oxen.arvixe.com','janek211_ibeacon','stany','janek211_ibeacons');
	     if(!$this->con){
		 die("Connection error: " . mysqli_connect_errno());
	      }

	      $this->user = $user;
	      $this->timeline_start = $start;
	      $this->timeline_stop = $stop;  
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
	
	
       
	public function getProximityArray($json=false){
	  $proximityArray =$this->getArray($this->con,"SELECT * FROM wp__proximity_events WHERE event_date > '$this->timeline_start' AND event_date <'$this->timeline_stop' AND user ='$this->user' ORDER BY event_date ");
	   if($json == true) return json_encode($proximityArray);	   
	   return $proximityArray;
	}
	
	public function getRegionsArray($json=false){
	  $regionsArray = $this->getArray($this->con,"SELECT * FROM `wp__region_events` WHERE user = $this->user");
	   if($json == true) return json_encode($regionsArray);	   
	   return $regionsArray;
	}
  	
	public function getSessionsArray($json=false){
	  $array = $this->getArray($this->con,"SELECT * FROM `wp__session_events` WHERE user = $this->user");
	   if($json == true) return json_encode($array);	   
	   return $array;
	}
	
	public function getScansArray($json=false){
	  $array = $this->getArray($this->con,"SELECT * FROM `wp__scan_events` WHERE user = $this->user");
	   if($json == true) return json_encode($array);	   
	   return $array;
	}
       
       public function getUsers($json=false){
	 $array = $this->getArray($this->con,"SELECT * FROM `wp_users`");
	   if($json == true) return json_encode($array);	   
	   return $array;
       }
       
       public function getOverrides($json=false){
	 $array =  $this->getArray($this->con,"SELECT * FROM `wp__override_events` WHERE user = $this->user");
	   if($json == true) return json_encode($array);	   
	   return $array;
       }
       
       public function getBeacons($json=false){
	   $array =  $this->getArray($this->con,"SELECT `ID`,`post_title` FROM `wp_posts` WHERE `post_type` = 'ibeacon'");
	   if($json == true) return json_encode($array);	   
	   return $array;
	
       }
       
       public function getAllEvents($json=false){
	 $array = array_merge($this->getOverrides, $this->getProximityArray, $this->getRegionsArray, $this->getScansArray, $this->getSessionsArray);
	if($json) return json_encode($array);
	 return $array; 
       }
       
       function clearTables(){
	
		try{
		 mysqli_multi_query($this->con,"DELETE FROM `wp__region_events` WHERE 1; DELETE FROM `wp__override_events` WHERE 1; DELETE FROM `wp__proximity_events` WHERE 1; DELETE FROM `wp__scan_events` WHERE 1;DELETE FROM `wp__session_events` WHERE 1");
		}
		catch(Exception $e){
			echo $e->getMessage();
			die("Didnt work");
		}
		die("tabula rasa");
	}
}

 


?>
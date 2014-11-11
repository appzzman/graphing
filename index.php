<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>iBeacons Stations</title>
<link rel="stylesheet" href="style/rickshaw.min.css">
<link rel="stylesheet" type="text/css" href="style/jquery.datetimepicker.css">
<link rel="stylesheet" type="text/css" href="style/style.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery.datetimepicker.js" type="text/javascript"></script>
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/rickshaw/1.4.6/rickshaw.min.js"> </script>
<script src="js/beaconevent.js" type="text/javascript"> </script>
<script src="js/d3.layout.min.js" type="text/javascript"></script>
</head>

<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
require_once(dirname(__FILE__) . '/data.php'); 

 $timeline_start = null;
 $timeline_stop = null;
 $user = -1;
//process request
if(isset($_REQUEST['user']))
{
	 $user = $_REQUEST['user'];	
	
}

if(!isset($_REQUEST["start"])&&!isset($_REQUEST["stop"])){
  $datetime = new DateTime('2000-01-01');
  $timeline_start=$datetime->format('Y-m-d');
  $datetime = new DateTime('tomorrow');
  $timeline_stop = $datetime->format('Y-m-d');
 
}
if(isset($_REQUEST["start"])&&isset($_REQUEST["stop"])){
	 $timestamp1= $_REQUEST["start"];
	 $dt1 = new DateTime($_REQUEST["start"]);
	 $timeline_start = $dt1->format('Y-m-d H:i:s');;
	 $dt2 = new DateTime($_REQUEST["stop"]);
	 $timeline_stop = $dt2->format('Y-m-d H:i:s');;	
}

$event = new Events($timeline_start,$timeline_stop,$user);

if(isset($_REQUEST["ccc"])){
	$event->clearTables();
}
?>


<body>
<div style="margin:auto; width:80%; text-align:center">
<h1>iBeacon Data</h1>
<div style="text-align:left">
<form action="index.php">
  Data for:
  <select id="user" name="user">
    <option value="Select User">Select an User</option>
    <?php
	$users = $event->getUsers();
	for($i=0;$i<count($users);$i++){
			
			 ?>
    <option value="<?php echo $users[$i][0]; ?>" <?php if($users[$i][0]==$_REQUEST["user"]){echo " selected";}?> ><?php echo $users[$i][1];?></option>
    <?php
			}	
			?>
  </select>
  <p>Select Start Date:
    <input class="datetime" name="start" value="<?php echo $event->timeline_start; ?>">
  </p>
  <p>Select Stop Date:
    <input class="datetime"  name="stop" value="<?php echo $event->timeline_stop; ?>">
  </p>
  <input type="submit">
</form>
<br />
<br />

<script type="text/javascript">
	var proximity_events = <?php echo $event->getProximityArray(true) ?>;
	var beacons = <?php echo $event->getBeacons(true) ?>;
	
	console.log(beacons);
	function getProximityEvents(beacon){
	var events = [];
	for(var i =0; i<proximity_events.length; i++){
		var duration = 0;
		var start = 0;
		var proximity = 0;
		
		var element = proximity_events[i];
		//console.log(element.event_date);
			
		var start = Date.parse(element.event_date+" -0600")/1000;
		
		
		events.push(new BeaconEvent(5/60,element.proximity, start ,element.beacon_id));
		
	}
	return events;
 }
	
	 
	 function check(beacons, bid){
		 
	 for(var i=0; i<beacons.length; i++)
		{
				var b = beacons[i];
				if(b.ID === bid) return b;
		}	
		return null;
	 }
	
	function getBeaconProximity(events, beacons,data){

		var palette = new Rickshaw.Color.Palette();
		for(var i=0; i<events.length; i++){
		var b = events[i];
		var element = null;

		for(var j=0; j<data.length; j++)
		{
			var beacon = data[j];
			if(beacon.id === b.beaconId) element = beacon;
		}			
	
		if(element)
		{
		 	 if(!(b.start||b.proximity)) console.log("undefi");			 
			 element.data.push({x:b.start, y: b.beaconId * (b.proximity + 1)*10});			 
		}
		else{
			console.log("Error! Element must exist");
			return;
			}
		}			
		return data;
	}	
</script>
<div id="chart_container">
  <?php 
	foreach($event->getBeacons() as $beacon ){
	?>
  <div id="y_axis" ></div>
  <div class="chart" id="chart<?php echo $beacon["ID"]; ?>"></div>
  <?php
	}
	$date_start = strtotime($timeline_start);
	$date_stop = strtotime($timeline_stop);
	$difference= $date_stop - $date_start;
	$interval = $difference/1000.0;		
	
	?>
<div id="legend_container">
		<div id="smoother" title="Smoothing"></div>
		<div id="legend"></div>
</div>
<div id="slider"></div>
</div>



<script type="text/javascript">


var events= getProximityEvents()

var data = [];
//preprocessing
var palette = new Rickshaw.Color.Palette();


for(var i=0; i<beacons.length; i++){
	var beacon = beacons[i];
	var _element ={"id":beacon.ID, "name":beacon.post_title, "data":[],color:palette.color()}
	var _data = []
	for(var j=0;j<=1000;j++)	
	{       var time = <?php echo $date_start; ?>+j*<?php echo $interval; ?>;
		if(j==0) {_data.push({x:time,  y: 100})}
		else{
			_data.push({x:time,  y: 1});										
		}
	
	}
	_element.data = _data;
	data.push(_element);
}

/*
console.log(events);
console.log(data);
console.log(beacons);
*/

data = getBeaconProximity(events, beacons, data);

//Fixing timelines
for(i=0; i<data.length; i++){
	var d = data[i];
	d.data.splice(data.length,0, {x:<?php echo $date_stop; ?>,y:0});
	var newdata= d.data.sort(function sorting(a,b){
		if(a.x<b.x) return -1;
		if(a.x>b.x) return 1;
		if(a.x=b.x) return 0;
	});
	d.data = newdata;
}

<?php
	$beacons = $event->getBeacons();
	for($i=0;$i< count($beacons);$i++){
				$beacon = $beacons[$i];
				$name = "chart".$beacon["ID"];
			?>	
			var <?php echo $name?>= new Rickshaw.Graph({
       element: document.querySelector("#<?php echo $name?>"),
	 	  renderer: 'bar',
		  width: 900, 
   		  height: 100, 	
		 series: data});
			
			  new Rickshaw.Graph.Axis.Time( { graph:  <?php echo $name?> } );
	 <?php echo $name?>.render();
				
			<?php
	}
?>
	
</script> 
</body>
</html>

<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

require_once(dirname(__FILE__) . '/data.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
<link rel="stylesheet" href="rickshaw.min.css">
<link rel="stylesheet" type="text/css" href="jquery.datetimepicker.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
<script src="jquery.datetimepicker.js" type="text/javascript"></script>
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/rickshaw/1.4.6/rickshaw.min.js"> </script>
<script src="beaconevent.js" type="text/javascript"> </script>
<script src="d3.layout.min.js" type="text/javascript"></script>
<style>
#chart_container
{
	position: relative;
	font-family: Arial, Helvetica, sans-serif;
}
.chart
{
	position: relative;
	left: 40px;
}
.y_axis
{
	position: absolute;
	top: 0;
	bottom: 0;
	width: 40px;
}
</style>
</head>
<body>
<div style="margin:auto; width:80%; text-align:center">
<h1>iBeacon Data</h1>
<div style="text-align:left">
<form action="index.php">
  Data for:
  <select id="user" name="user">
    <option value="Select User">Select an User</option>
    <?php
		for($i=0;$i<count($arrayofusers);$i++){
			
			 ?>
    <option value="<?php echo $arrayofusers[$i][0]; ?>" <?php if($arrayofusers[$i][0]==$_REQUEST["user"]){echo " selected";}?> ><?php echo $arrayofusers[$i][1];?></option>
    <?php
			}	
			?>
  </select>
  <p>Select Start Date:
    <input class="datetime" name="start" value="<?php echo $timeline_start; ?>">
  </p>
  <p>Select Stop Date:
    <input class="datetime"  name="stop" value="<?php echo $timeline_stop; ?>">
  </p>
  <input type="submit">
</form>
<br />
<br />
<script type="text/javascript">
	
	function getProximityEvents(beacon){
        var events = [];
	
			
	<?php		
			//getting all proximity events	to javascript array
	 for($i=0;$i<count($proximityArray);$i++){
	 $duration = 0;
	 $start = 0;
	 $proximity = $proximityArray[$i][1];
	 
	 if($i<	count($proximityArray)-1)
	 {
		$date_start = $proximityArray[$i][2];
		$date_end = $proximityArray[$i+1][2]; 
		//$start =     timeDifference($timeline_start,$date_start);
		$type = $proximityArray[$i][4];
		$date_start = strtotime(	$date_start);
		
		
		$duration = 5/60;
			//duration,proximity,start,type	
	 ?> 
			events.push(new BeaconEvent(<?php echo $duration.",".$proximity.",".$date_start.",".$type; ?>));
	 <?php
	 }

	}
	
	?>	
	
	 return events;
	}
	 
	 
	 function check(beacons, bid){
		 
	 for(var i=0; i<beacons.length; i++)
		{
				var b = beacons[i];
			/*
				console.log(b);
				console.log(bid);
				console.log(beacons);
			*/
				if(b.id === bid) return b;
				
		}	
		return null;
	 }
	
	function getBeaconProximity(events, beacons){
		var data = [];	
		var palette = new Rickshaw.Color.Palette();
		//console.log(events);

		for(var i=0; i<events.length; i++){
		var b = events[i];
	
		var element = check(data,b.beaconId);
		
		if(element)
		{
		 	 if(!(b.start||b.proximity)) console.log("undefi");
			 
			 element.data.push({x:b.start, y: (b.proximity + 1)*10});	
		}
		else{
				var _b =  check(beacons, b.beaconId);
				
				element ={"id":_b.id, "name":_b.id, "data":[],color:palette.color()}	
				element.data.push({x:b.start, y: (b.proximity + 1)*10});	
			  data.push(element);
		}
	
   	
	
	/*			
		var beacon_data = null;
		if(d>0)
		{
			beacon_data = d[0];
		}
		else{
			//create a new data entry		
			var _beacons= beacons.filter(exists);
			var _b=_beacons[0];	
			beacon_data ={"id":_b.id, "name":_b.title, "data":[]}	
			console.log(beacon_data);		
			data.push(beacon_data);			
		}
			console.log("Data");
			console.log(data);
			
		  beacon_data.data.push({x:b.start, y: (b.proximity + 1)*10});
	*/
				
		}
		
		console.log(data);
		
		return data;
	}
	
	
</script>
<div id="chart_container">
  <?php 
	foreach($arrayofibeacons as $beacon ){
	?>
  <div id="y_axis" ></div>
  <div class="chart" id="chart<?php echo $beacon["ID"]; ?>"></div>
  <?php
	}
	?>
</div>
<script type="text/javascript">
	var beacons=[];
	<?php 
	foreach($arrayofibeacons as $beacon ){
	?>
  	beacons.push({"id": <?php echo $beacon["ID"]; ?>, "title":"<?php echo htmlspecialchars($beacon["post_title"]); ?>"});
	
	
	<?php	
	}
	
		
?>
var  events = getProximityEvents()
var data = getBeaconProximity(events, beacons);
//var grapsh = [];

<?php 
	for($i=0;$i< count($arrayofibeacons);$i++){
				$beacon = $arrayofibeacons[$i];
				$name = "chart".$beacon["ID"];
			?>	
			var <?php echo $name?>= new Rickshaw.Graph({
       element: document.querySelector("#<?php echo $name?>"),
	 	  	renderer: 'bar',
    		width: 1000, 
   		  height: 100, 	
			  series: [{color: data[<?php echo $i; ?>].color,data:data[<?php echo $i; ?>].data}]});
			
			  new Rickshaw.Graph.Axis.Time( { graph:  <?php echo $name?> } );
	 <?php echo $name?>.render();
				
			<?php
	}
	
	
?>
	


 </script> 
<script> 


/*
var graph1 = new Rickshaw.Graph( {
    element: document.querySelector("#chart1"),
    renderer: 'bar',
    width: 1000, 
    height: 100, 
    series: [{color: 'steelblue',data:data[0].data}]
});

var graph2 = new Rickshaw.Graph( {
    element: document.querySelector("#chart2"),
    renderer: 'bar',
    width: 1000, 
    height: 100, 
    series: [{color: 'orange',data:data[1].data}]
});

 var x_axis1 = new Rickshaw.Graph.Axis.Time( { graph: graph1 } );
 var x_axis2 = new Rickshaw.Graph.Axis.Time( { graph: graph2 } );
 var y_axis = new Rickshaw.Graph.Axis.Y( {
        graph: graph1,
        orientation: 'left',
        tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
        element: document.getElementById('y_axis'),
} );
 
 
graph1.render();
graph2.render();

*/

</script>
</body>
</html>

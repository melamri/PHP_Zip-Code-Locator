<html>
<head>
	<!-- Ikbel Amri -->
	<title>THE ZIP CODE LOCATOR</title>
	<style>
		div {padding:3; margin: auto; width:850; font: 14px verdana, sans-serif; border:2 solid darkred; border-radius:10; background: lightgrey}
		div.ex1 { height: auto}
		div.ex2 { width: 835; height: 36; background: #F5DA81; font: 12px}
		input.ex11  {border-radius:7; border: 1 solid rgba(212,208,199,1); background: rgba(47,79,78,1); color: rgba(188,158,41,1)}
		#tableInf { border-collapse:collapse; }
		td {border: 1px solid green; font: 13px verdana, sans-serif; padding-left:15; padding-right:15}
		td.zipC {color:yellow}
		td.cityC {color:darkblue}
		td.stateC {color:darkblue}
		td.latC {color:darkred}
		td.lonC {color:darkred}
		td.timeC {color:black}
	</style>
</head>

<body>
	<div class="ex1">
		<div class="ex2">
			<h3 style="color:darkred" font="bold">&nbsp;THE COM214 ZIP CODE LOCATOR</h3>
			&emsp;&emsp;&emsp;
			<form style='display:inline' Name = "DBTestForm" Method ="GET" ACTION = "zipcodelocator.php">
				<input class="ex11" type="submit" name="buttonCreate" value="Create&emsp;DB" />
				<input class="ex11" type="submit" name="buttonDrop" value="Drop&emsp;DB" />
			</form>
		</div>
	</div>
	<div align="center">
    	<article>
            <canvas id="myCanvas" width="824" height="430">
                        Your browser does not support the canvas element.
            </canvas>
        </article>
	</div>
	<div class="ex1">
		<div class="ex2">
			<form style='display:inline' Name = "bottomForm" METHOD="GET" ACTION = "zipcodelocator.php">
				&nbsp;LATITUDE:   <input type="text" id="xpos" name="xpos" readonly>
				LONGITUDE:  <input type="text" id="ypos" name="ypos" readonly> 
				<input class="ex11" type="submit" name="buttonListZips" value="List Nearby Zip Codes"  />
				Items per page:
				<select type="submit" name="num" id="num">
					<option value="5">5</option>
					<option value="10">10</option>
					<option value="15">15</option>
					<option value="25">25</option>
				</select>
			</form>
		</div>
	<div style='width:835' align="center">
		<?php
			function latLonToMiles($lat1, $lon1, $lat2, $lon2){  //haversine formula
				$R = 3961;  // radius of the Earth in miles
				$dlon = ($lon2 - $lon1)*M_PI/180;
				$dlat = ($lat2 - $lat1)*M_PI/180;
				$lat1 *= M_PI/180;
				$lat2 *= M_PI/180;
				$a = pow(sin($dlat/2),2) + cos($lat1) * cos($lat2) * pow(sin($dlon/2),2) ;
				$c = 2 * atan2( sqrt($a), sqrt(1-$a) ) ;
				$d = $R * $c;
				$d = round($d, 4);
				return $d;	
			}
            $db_conn = mysql_connect("localhost", "root");
            if (!$db_conn)
                die("Unable to connect: " . mysql_error()); 
			
			// creating the database on buttonCreate click
			if (isset($_GET['buttonCreate'])) {
				if (mysql_query("CREATE DATABASE zipsDB", $db_conn)){
					echo "Database ready";
					mysql_select_db("zipsDB", $db_conn);
					$cmd = "CREATE TABLE zlist (
						zip int(5) NOT NULL PRIMARY KEY,
						city varchar(30),
						state varchar(3),
						lat float(7,4),
						lon float(7,4),
						time int(3)
					)";
				}
				else
					echo "Unable to create database: DB already created.";
			}
			if (isset($_GET['buttonListZips'])) {
				if (($_GET['xpos'])>0) {
					mysql_query( "DROP DATABASE zipsDB", $db_conn );
					mysql_query("CREATE DATABASE zipsDB", $db_conn);
					mysql_select_db("zipsDB", $db_conn);
					$cmd = "CREATE TABLE zlist (
						zip int(5) NOT NULL PRIMARY KEY,
						city varchar(35),
						state varchar(3),
						lat float(7,4),
						lon float(7,4),
						time int(3)
					)";
					mysql_query($cmd);
					
					$cmd = "LOAD DATA LOCAL INFILE 'zip_codes_usa.csv' INTO TABLE zlist
							FIELDS TERMINATED BY ','";
					mysql_query($cmd);
					
					$circLat = $_GET['xpos'];
					$circLon = $_GET['ypos'];
					$numEnt = $_GET['num'];
					
					//$cmd = "SELECT *,
					//		SQRT(POW(($circLat-lat),2)+POW(($circLon-lon),2)) as dist
					//		FROM zlist ORDER BY dist ASC limit $numEnt ";
							
					$cmd = "SELECT *,
							SQRT(POW(($circLat-lat),2)+POW(($circLon-lon),2)) as dist
							FROM zlist ORDER BY dist ASC limit $numEnt ";
							
					$result =  mysql_query($cmd);	
					echo "<table id='tableInf'>".PHP_EOL;	
					echo( "<tr><td>Zip Code</td><td>City</td><td>State</td><td>Lat</td><td>Lon</td><td>Distance (miles)</td><td>Time Difference (ET)</td></tr>" . PHP_EOL ); 	
					while($row = mysql_fetch_array($result)){
						 echo( "<tr><td class='zipC'>" .$row['zip'] . "</td> <td class='cityC'>" .$row['city']. "</td> <td class='stateC'>" 
						 .$row['state']."</td><td class='latC'>" .$row['lat']."</td> <td class='lonC'>" 
						 .$row['lon']. "</td> <td>" .(latLonToMiles($row['lat'], $row['lon'], $circLat, $circLon)).
						 "</td> <td class='timeC'>" .$row['time']. "</td></tr>" . PHP_EOL ); 
					}
					echo " </table>".PHP_EOL;
					
					echo "<script> num.value = " . $numEnt. "</script>";
				}
				else
					echo "Cannot list Zip Codes. Please click on the map to locate position.";
			}
			
			// deleting the database on buttonDrop click
			if (isset($_GET['buttonDrop'])) {
				if(mysql_query( "DROP DATABASE zipsDB", $db_conn ))
					echo "Database deleted successfully\n";	
				else 
					echo 'Unable to delete database: There is no DB to be deleted.';	
			}
                
            mysql_close($db_conn);
        ?>     
    </div>
	
	</div>
	<script type="text/javascript">   
		
		var canv=document.getElementById("myCanvas");
		var c=canv.getContext("2d");      
		
		function draw() {  
			// var canv=document.getElementById("myCanvas");
			// var c=canv.getContext("2d");      
			var img = new Image();  
			var w, h;
			
			img.onload = function(){  			  	  
				w=canv.width;		// resize the canvas to the new image size
				h=canv.height;		
				c.drawImage(img, 0, 0, w, h ); 	
				
				if( sessionStorage.getItem("storedX"))
					drawCircle(sessionStorage.storedX, sessionStorage.storedY);		
					xpos.value = sessionStorage.storedtx;
					ypos.value = sessionStorage.storedty;
			} 
		img.src = 'zip_codes_map.png';
		}
		  		  
		// method to draw the circle when clicked
		function drawCircle(x, y) {
			// var canv=document.getElementById("myCanvas");
			// var c=canv.getContext("2d");
			
			c.beginPath();
			c.arc(x,y,15,0,2*Math.PI);
			c.fillStyle = 'rgba(255,255,255,0.3)';
			// painting semi transparent white fill by changing the alpha value of the rgb color
			c.strokeStyle = "black";
			c.fill();
			c.stroke();
			c.closePath();
			
			c.beginPath();
			c.arc(x,y,2,0,2*Math.PI);
			c.fillStyle = 'blue';
			c.strokeStyle = "white";
			c.fill();
			c.stroke();
			c.closePath();
		}
		
		function getMousePos(canvas, events){
    		var obj = canvas;
    		var top = 0, left = 0;
			var mX = 0, mY = 0;
   			while (obj && obj.tagName != 'BODY') { //accumulate offsets up to 'BODY'
        		top += obj.offsetTop;
        		left += obj.offsetLeft;
        		obj = obj.offsetParent;
    		}
    		mX = events.clientX - left + window.pageXOffset;
    		mY = events.clientY - top + window.pageYOffset;
    		return { x: mX, y: mY };
		}
		
		window.onload = function main(){
		//if(typeof(Storage)!=="undefined") {
			// sessionStorage.clicked = false;
		//}
		
		draw();
    		var canvas = document.getElementById('myCanvas'); 
    		canvas.addEventListener('mousedown', function(events){
        		var mousePos = getMousePos(canvas, events);
   		  		var tx = document.getElementById("xpos");
				var xRatio = (49.0000-25.9567)/(409-6);
				// chose 2 points on a vertical straight line and calculated the ratio
				// between the differences in longitude and in pixels between the same points
		  		tx.value = (49.26780 - mousePos.y * xRatio).toFixed(4);
		  		//tx.value = mousePos.y;
				// added longitude origin point and deducted the ratio * position in pixels from it
				// use .toFixed(4) to display 4 decimal places
    		  	var ty = document.getElementById("ypos");
				var yRatio = (123.322418-95.153202)/(425-42);
		  		ty.value = (-126.4629 + mousePos.x * yRatio).toFixed(4);
				//ty.value = mousePos.x;

				if(typeof(Storage)!=="undefined") {
					sessionStorage.storedX = mousePos.x;
					sessionStorage.storedY = mousePos.y;
					sessionStorage.storedtx = tx.value;
					sessionStorage.storedty = ty.value;
				}
				sessionStorage.clicked = true;
				draw();

			}, false);

		}
	</script> 
</body>
</html>
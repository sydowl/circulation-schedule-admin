<?php
require_once('../config/config.php');
$loggedin =  $_SERVER['REMOTE_USER'];
$login_url ="https://weblogin.reed.edu?cosign-vm-library&https://vm.library.reed.edu/Circ/circschedroot/circulation-schedule-admin/falladmin.php";
$logout_url="https://weblogin.reed.edu/cgi-bin/logout";
$next_url="https://vm.library.reed.edu/Circ/circschedroot/circulation-sched
ule-admin/falladmin.php";
if ($loggedin == "" || $loggedin == " ") 
{
header("Location: $login_url");
}

//Only Access Services staff and circulation leads may login

$authorized = array("goodellg", "nicegan", "rdempsy","buccis","phanj","aveltkam","emzhang","eolson","aarpaia","holmesj","sydowl","mcdaniel","willingt","vanbuskb","bkelley","alwinee");
if(!in_array($loggedin, $authorized)) {
$cookie=$_SERVER[ 'COSIGN_SERVICE' ];
setcookie( $cookie, "null", time()-1, '/', "", 1 );
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Circulation Schedule Administration</title>
<link rel="stylesheet" type="text/css" href="../css/circcal.css">
<script type="text/javascript" src="../js/circsched.js"></script>
</head>
<body>

<?php

//The column headers of each calendar.

function column_design_and_header($title, $begin_date, $end_date)
{
echo '<P id="h">'.$title.'<br />
'.$begin_date.' - '.$end_date.'</P><P>
<table border="0" style="font-size:10pt;">
<colgroup span="3" style="background-color:white;"></colgroup>
<colgroup span="2" style="background-color:#FFCC33;"></colgroup>
<colgroup span="2" style="background-color:#FFFF99;"></colgroup>
<colgroup span="2" style="background-color:#FFCC33;"></colgroup>
<colgroup span="2" style="background-color:#FFFF99;"></colgroup>
<colgroup span="2" style="background-color:#FFCC33;"></colgroup>
<colgroup span="2" style="background-color:#FFFF99;"></colgroup>
<colgroup span="2" style="background-color:#FFCC33;"></colgroup>

<tr><td colspan="3" id="h">Time</td><td colspan="2" id="h">Monday</td><td colspan=2 id="h">Tuesday</td><td colspan=2 id="h">Wednesday</td><td colspan=2 id="h">Thursday</td><td colspan=2 id="h">Friday</td><td colspan=2 id="h">Saturday</td><td colspan=2 id="h">Sunday</td></tr><form>';
}


//MAIN SCHEDULE THIS WEEK

 if($mysqli->errno){
	printf("Could not connect: %s", $mysqli->connect_error);
	exit();
}
$begin_date_query = "SELECT `start_date` FROM `date_ranges` WHERE `week` LIKE \"fallthisweek\";";
$begin_dateG = $mysqli->query($begin_date_query)->fetch_object()->start_date;
//The UI and Google Calendar need different date formats
$begin_date = (new DateTime($begin_dateG))->format('m-d-Y');
$end_date_query = "SELECT `end_date` FROM `date_ranges` WHERE `week` LIKE \"fallthisweek\";";
$end_dateG = $mysqli->query($end_date_query)->fetch_object()->end_date;
$end_date = (new DateTime($end_dateG))->format('m-d-Y');
$query = "SELECT * FROM `fall_schedule` ORDER BY `fall_schedule`.`start_time`, `fall_schedule`.`shift_num` ASC;";
$sched=$mysqli->query($query);

//$mysqli->close();

echo "<p align='right'><a href=".$logout_url."?".$login_url."?".$next_url."> LOGOUT</a></p>";

echo '<p><strong>Instructions: </strong>
To split a two-hour shift into two one-hour shifts, click the "+" button.
<br />To merge two one-hour shifts into a two-hour shift, delete the text in the second one-hour shift and click Refresh.</p><p>&nbsp;</p>';

$title = "THIS WEEK";
column_design_and_header($title, $begin_date, $end_date);

$i=0;
$shift_t="07:00";
$tabindexwk = 1;
$tabindexdy = 1;
$begin_dateG = '"'.$begin_dateG.'"';
$table = '"fall_schedule"';

while ($row = $sched->fetch_assoc()) {
    $id=$row["schedule_id"];
    $shift_num=$row["shift_num"];
    $shift_num = '"'.$shift_num.'"';
    $startG=$row["start_time"];
    if ($startG == "24:00:00") {$startG = "00:00:00"; $start = "12:00:00";}
    $start=DATE("h:i",STRTOTIME("$startG"));
    $start_v = '"'. $startG .'"';
    $endG=$row["end_time"];
    if ($endG == "24:00:00") {$endG = "12:00:00";}
    $end=DATE('h:i',STRTOTIME("$endG"));
    $end_v = '"'.$endG.'"';
    $worker=$row["username"];
    $day_shift=$row["day_shift"];
    $hours=$end - $start; 
    $add_button = false;
    $tabgcolor="white";

//Check to see if we need to start the next row
     
    if ($shift_t != $start) {
        if ($i>0 && !($i % 14)) {
                echo '<td>'.$shift_t.' - '.$start.'</td>';
		$tabindexdy += 2;
		$tabindexwk = $tabindexdy;
        }

        $shift_t = $start;
        echo '</tr><tr>';
        echo '<td>'.$start.'</td>';
        echo "<td>-</td>";
        echo '<td>'.$end.'</td>';
    }    

//Students loccing in to their own schedule will see opentmp and be able to take that shift.
        
    if ($worker == "opentmp") {
        $tabgcolor="red";
       
    }

    if ($worker == "") {
	$hours = 0;
        echo '<td align=left><button tabindex='.$tabindexwk.' onclick="addTimeSlot ('.$id.','.$table.')">+</button></td>';
	if (($tabindexwk % 2) == 0) { $tabindexwk += 38; }
	$tabindexwk += 1;
    }  
    
          
        if ($hours != 0) {
            
echo "<td rowspan=1><textarea style='background-color:".$tabgcolor."' id='namebox'  tabindex=\"$tabindexwk\" rows=1 cols=7 onchange='updateUser(this.value, $shift_num, $table, $begin_dateG, $start_v, $end_v)'>$worker</textarea></td>";

	if (($tabindexwk % 2) == 0) { $tabindexwk += 38; }
	$tabindexwk += 1;
     }
        

echo '</form>';
$i++;
}
echo '<td>'.$shift_t.' - '.$end.'</td>'
?>
</tr>
</table><BR>
<input type="button" value="Refresh" onclick="window.location.reload(false);" />

<?php

//NEXT WEEK
$begin_date_query = "SELECT `start_date` FROM `date_ranges` WHERE `week` LIKE \"fallnextweek\";";
$begin_dateG = $mysqli->query($begin_date_query)->fetch_object()->start_date;
$begin_date = (new DateTime($begin_dateG))->format('m-d-Y');
$end_date_query = "SELECT `end_date` FROM `date_ranges` WHERE `week` LIKE \"fallnextweek\";";
$end_dateG = $mysqli->query($end_date_query)->fetch_object()->end_date;
$end_date = (new DateTime($end_dateG))->format('m-d-Y');
$query = "SELECT * FROM `fall_schedule_2` ORDER BY `fall_schedule_2`.`start_time`, `fall_schedule_2`.`shift_num` ASC;";
$sched=$mysqli->query($query);

//$mysqli->close();

$title = "NEXT WEEK";
column_design_and_header($title, $begin_date, $end_date);

$i=0;
$shift_t="07:00";
$flag = 0;
$tabindexdy = $tabindexwk;
$begin_dateG = '"'.$begin_dateG.'"';
$table = '"fall_schedule_2"';

while ($row = $sched->fetch_assoc()) {
   $id=$row["schedule_id"];
  $shift_num=$row['shift_num'];
  $shift_num = '"'.$shift_num.'"';
  $startG=$row["start_time"];
	if ($startG == "24:00:00") {$startG = "00:00:00"; $start = "12:00:00";}
  $start=DATE("h:i",STRTOTIME("$startG"));
  $start_v = '"' .$startG. '"';
  $endG=$row["end_time"];
	if ($endG == "24:00:00") {$endG = "12:00:00";}
  $end=DATE('h:i',STRTOTIME("$endG"));
  $end_v = '"' . $endG  . '"';
  $worker=$row["username"];
  $day_shift=$row["day_shift"];
  $hours=$end - $start;
  $add_button = false;
  $tabgcolor="white";

    if ($worker == "hidetimes") {
        break;
    }

    if ($shift_t != $start) {
        if ($i>0 && !($i % 14)) {
                echo '<td>'.$shift_t.' - '.$start.'</td>';
		 $tabindexdy += 2;
                $tabindexwk = $tabindexdy;
        }

        $shift_t = $start;
        echo "</tr><tr>";
        echo '<td>'.$start.'</td>';
        echo "<td>-</td>";
        echo '<td>'.$end.'</td>';
    }

    if ($worker == "opentmp") {
        $tabgcolor="red";

    }

    if ($worker == "") {
        $hours = 0;
        echo '<td align=left><button tabindex='.$tabindexwk.' onclick="addTimeSlot ('.$id.','.$table.')">+</button></td>';
        if (($tabindexwk % 2) == 0) { $tabindexwk += 38; }
        $tabindexwk += 1;
    }


        if ($hours != 0) {

echo "<td rowspan=1><textarea style='background-color:".$tabgcolor."' id='namebox' tabindex=\"$tabindexwk\" rows=1 cols=7 onchange='updateUser(this.value, $shift_num, $table, $begin_dateG, $start_v, $end_v)'>$worker</textarea></td>";

        if (($tabindexwk % 2) == 0) { $tabindexwk += 38; }
        $tabindexwk += 1;
     }
echo '</form>';

$i++;
}
 echo '<td>'.$shift_t.' - '.$end.'</td>';
?>
</tr>
</table><br />
<input type="button" value="Refresh" onclick="window.location.reload(false);" />

<?php

//TWENTYFOUR HOUR READING WEEK SCHEDULE
$database="circ_schedule";
$username="circ_schedule";
$password="GGBI0Je7PlaC";
$mysqli = new mysqli('drbd.reed.edu',$username,$password,$database, 3306);
 if ($mysqli->connect_errno) {
  printf("Could not connect: %s", $mysqli->connectr_error);
 exit();
}

$begin_date_query = "SELECT `start_date` FROM `date_ranges` WHERE `date_id` = 6;";
$begin_date = $mysqli->query($begin_date_query)->fetch_object()->start_date;
$end_date_query = "SELECT `end_date` FROM `date_ranges` WHERE `date_id` = 6;";
$end_date = $mysqli->query($end_date_query)->fetch_object()->end_date;
$query = "SELECT * FROM `twentyfour` ORDER BY `twentyfour`.`start_time`, `twentyfour`.`shift_num` ASC;";
$sched = $mysqli->query($query);
$mysqli->close();


?>
<a name="ReadWeek"></a>
<tr>
    
<?php
$shift_t = 0;
$i=0;
$tabindexdy = $tabindexwk;
$table='"twentyfour"';

while ($row = $sched->fetch_assoc()) {
    $id=$row["schedule_id"];
 $shift_num=$row['shift_num'];
$shift_num = '"'.$shift_num.'"';
    $start=$row["start_time"];
    //$start=substr($start,0,-3);
    $start=DATE("h:i",STRTOTIME("$start"));
    $startplusone=$start + 1;
    $startplustwo=$start + 2;
    $end=$row["end_time"];
    //$end=substr($end,0,-3);
    $end=DATE("h:i",STRTOTIME("$end"));
    $worker=$row["username"];
    $day_shift=$row["day_shift"];
    //$hours=$end - $start; 
    $hours=1;
    $add_button = false;
    $tabgcolor="white";
   
    if ($worker == "hidetimes") {
        break;
    }

    if (!$shift_t) {

$title = "READING WEEK";
column_design_and_header($title, $begin_date, $end_date);

	}	
     
    if ($shift_t != $start) {
	 if ($i>0 && !($i % 14)) {
                echo '<td>'.$shift_t.' - '.$start.'</td>';
                $tabindexdy += 2;
                $tabindexwk = $tabindexdy;
        }
        $shift_t = $start;
        echo "</tr><tr>";
        echo '<td>'.$start.'</td>';
        echo "<td>-</td>";
        echo '<td>'.$end.'</td>';
         
    }    
    
        
    if ($worker == "opentmp") {
        $tabgcolor="red";
       
    }

    if ($worker == "") {
      $hours = 0;
       echo '<td align=left><button tabindex='.$tabindexwk.' onclick="addTimeSlot ('.$id.','.$table.')">+</button></td>';
        if (($tabindexwk % 2) == 0) { $tabindexwk += 38; }
        $tabindexwk += 1;
    }  
    
          
        if ($hours != 0) {   
//    echo '<td rowspan=1><textarea style="background-color:'.$tabgcolor.'" id="namebox"  tabindex='.$tabindexwk.' rows=1 cols=7 onchange="updateUser (this.value,'.$shift_num.','.$table.')">'.$worker.'</textarea></td>';

echo "<td rowspan=1><textarea style='background-color:".$tabgcolor."' id='namebox' tabindex=\"$tabindexwk\" rows=1 cols=7 onchange='updateUser(this.value, $shift_num, $table)'>$worker</textarea></td>";

        if (($tabindexwk % 2) == 0) { $tabindexwk += 38; }
        $tabindexwk += 1;
        }
    
echo '</form>';


$i++;
}
if ($i) { echo '<td>'.$shift_t.' - '.$end.'</td>';}

?>


</tr>
</table>
<form>
<p></p>
<input type="button" value="Show Reading Week" onnlick="updateUser('open',1,'twentyfour','true');"></input>
<B>Dates: </B><textarea style="background-color:white" id="dates" rows=1 cols=10 onchange="ReadingDates(this.value,'start_date')"></textarea> - 
<textarea style="background-color:white" id="dates" rows=1 cols=10 onchange="ReadingDates(this.value,'end_date')"></textarea>
<input type="button" value="Hide Reading Week" onclick="updateUser('hidetimes',1,'twentyfour', 'true');" />
&nbsp;<input type="button" value="Refresh" onclick="window.location.reload(false);" /><BR>
</form>
</body>
</html>

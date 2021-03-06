<?php

require_once 'common/database.php';
require_once 'common/header.php';
require_once 'common/params.php';
require_once 'common/shiftInfo.php';
require_once 'common/stationInfo.php';
require_once 'common/workstationStatus.php';
require_once 'common/version.php';

Time::init();

session_start();

Authentication::authenticate();

if (!(Authentication::isAuthenticated() &&
      Authentication::checkPermissions(Permission::WORKSTATION_SUMMARY)))
{
   header('Location: index.php?action=logout');
   exit;
}

function renderStationSummaries($shiftId)
{
   echo "<div class=\"flex-horizontal main summary\">";
   
   $result = FlexscreenDatabase::getInstance()->getStations();
   
   while ($result && ($row = $result->fetch_assoc()))
   {
      $stationId = $row["stationId"];
      
      renderStationSummary($stationId, $shiftId);
   }
   
   echo "</div>";
}

function renderStationSummary($stationId, $shiftId)
{
   $url= "workstation.php?stationId=" . $stationId;

   echo "<a href=\"$url\"><div id=\"workstation-summary-$stationId\" class=\"flex-vertical station-summary-div\">";
   
   $stationInfo = StationInfo::load($stationId);
   
   $workstationStatus = WorkstationStatus::getWorkstationStatus($stationId, $shiftId);
   
   if ($stationInfo && $workstationStatus)
   {
      echo 
<<<HEREDOC
      <div class="flex-horizontal" style="justify-content: flex-start;">
         <div class="station-label">{$stationInfo->getLabel()}</div>
         <!--div class="flex-horizontal hardware-button-led"></div-->
      </div>

      <div class="flex-vertical">
         <div class="stat-label">Today's screen count</div>
         <div class="large-stat urgent-stat count-div"></div>
      </div>
      
      <div class="stat-label">Average time between screens</div>
      <div class="medium-stat average-count-time-div"></div>
      
      <div class="stat-label">Time of last screen</div>
      <div class="medium-stat update-time-div"></div>
HEREDOC;
   }
      
   echo "</div></a>";
}
?>

<html>

<head>

   <meta name="viewport" content="width=device-width, initial-scale=1">
   
   <title>Workstation Summary</title>
   
   <!--  Material Design Lite -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
   
   <link rel="stylesheet" type="text/css" href="css/flex.css<?php echo versionQuery();?>"/>
   <link rel="stylesheet" type="text/css" href="css/flexscreen.css<?php echo versionQuery();?>"/>
   <link rel="stylesheet" type="text/css" href="css/workstationSummary.css<?php echo versionQuery();?>"/>
   
</head>

<body onload="update()">

<div class="flex-vertical" style="align-items: flex-start;">

   <?php Header::render(true);?>
   
   <?php if (!isKioskMode()) {include 'common/menu.php';}?>
   
   <?php renderStationSummaries(ShiftInfo::getShiftId());?>
     
</div>

<script src="script/flexscreen.js<?php echo versionQuery();?>"></script>
<?php if (isKioskMode()) {echo "<script src=\"script/kiosk.js\"" . versionQuery() . "></script>";}?>
<script src="script/workstationSummary.js<?php echo versionQuery();?>"></script>
<script>
   // Set menu selection.
   setMenuSelection(MenuItem.WORKSTATION_SUMMARY);

   // Start a five-second timer to update the count/hourly count div.
   setInterval(function(){update();}, 5000);
</script>

</body>

</html>
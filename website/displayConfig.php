<?php

require_once 'common/displayInfo.php';
require_once 'common/database.php';
require_once 'common/header.php';
require_once 'common/params.php';
require_once 'common/stationInfo.php';
require_once 'common/version.php';

Time::init();

session_start();

if (!(Authentication::isAuthenticated() &&
      Authentication::checkPermissions(Permission::DISPLAY_CONFIG)))
{
   header('Location: index.php?action=logout');
   exit;
}

function renderTable()
{
   echo
<<<HEREDOC
   <table>
      <tr>
         <th>ID</th>
         <th>IP Address</th>
         <th>Last Contact</th>
         <th>Status</th>
         <th></th>
         <th></th>
      </tr>
HEREDOC;

   $database = FlexscreenDatabase::getInstance();

   if ($database && $database->isConnected())
   {
      $result = $database->getDisplays();

      while ($result && $row = $result->fetch_assoc())
      {
         $displayInfo = DisplayInfo::load($row["displayId"]);

         $id = "display-" . $displayInfo->displayId;
         $isOnline = $displayInfo->isOnline();
         $status = $isOnline ? "Online" : "Offline";
         $ledClass = $isOnline ? "led-green" : "led-red";

         echo
<<<HEREDOC
         <tr>
            <td>$displayInfo->uid</td>
            <td>$displayInfo->ipAddress</td>
            <td>$displayInfo->lastContact</td>
            <td id="$id"><div>$status</div><div class="$ledClass"></div></td>
            <td><button class="config-button" onclick="setDisplayConfig($displayInfo->displayId, $displayInfo->presentationId, $displayInfo->enabled); showModal('config-modal');">Configure</button></div></td>
            <td><button class="config-button" onclick="setDisplayId($displayInfo->displayId); showModal('confirm-delete-modal');">Delete</button></div></td>
         </tr>
HEREDOC;
      }
   }

   echo "</table>";
}

function getPresentationOptions()
{
   $options = "<option value=\"0\">None</option>";

   $database = FlexscreenDatabase::getInstance();

   if ($database && $database->isConnected())
   {
      $result = $database->getPresentations();

      while ($result && $row = $result->fetch_assoc())
      {
         $options .= "<option value=\"{$row["presentationId"]}\">{$row["name"]}</option>";
      }
   }

   return ($options);
}

function deleteDisplay($displayId)
{
   $database = FlexscreenDatabase::getInstance();

   if ($database && $database->isConnected())
   {
      $database->deleteDisplay($displayId);
   }
}

function updateDisplay($displayId, $presentationId, $enabled)
{
   $diplayInfo = DisplayInfo::load($displayId);
   $diplayInfo->presentationId = $presentationId;
   $diplayInfo->enabled = $enabled;

   $database = FlexscreenDatabase::getInstance();

   if ($database && $database->isConnected())
   {
      $database->updateDisplay($diplayInfo);
   }
}

// *****************************************************************************
//                              Action handling

$params = Params::parse();

switch ($params->get("action"))
{
   case "delete":
   {
      deleteDisplay($params->get("displayId"));
      break;
   }

   case "update":
   {
      updateDisplay($params->getInt("displayId"), $params->getInt("presentationId"), $params->getBool("enabled"));
      break;
   }

   default:
   {
      break;
   }
}

?>

<html>

<head>

   <meta name="viewport" content="width=device-width, initial-scale=1">

   <title>Display Config</title>

   <!--  Material Design Lite -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

   <link rel="stylesheet" type="text/css" href="css/flex.css<?php echo versionQuery();?>"/>
   <link rel="stylesheet" type="text/css" href="css/flexscreen.css<?php echo versionQuery();?>"/>
   <link rel="stylesheet" type="text/css" href="css/modal.css<?php echo versionQuery();?>"/>

</head>

<body>

<form id="config-form" method="post">
   <input id="action-input" type="hidden" name="action">
   <input id="display-id-input" type="hidden" name="displayId">
</form>

<div class="flex-vertical" style="align-items: flex-start;">

   <?php Header::render(false);?>
   
   <?php include 'common/menu.php';?>

   <div class="flex-horizontal main">
      <?php renderTable();?>
   </div>

</div>

<!--  Modal dialogs -->

<div id="config-modal" class="modal">
   <div class="flex-vertical modal-content" style="width:300px;">
      <div id="close" class="close">&times;</div>
      
      <div class="flex-vertical input-block">
         <label>Presentation</label>
         <select id="presentation-id-input" form="config-form" name="presentationId">
            <?php echo getPresentationOptions();?>
         </select>
      </div>
      
      <div class="flex-horizontal input-block">
         <label>Enabled</label>
         <input id="enabled-input" type="checkbox" form="config-form" name="enabled">
      </div>
      
      <div class="flex-horizontal">
         <button class="config-button" type="submit" form="config-form" onclick="setAction('update')">Save</button>
      </div>
   </div>
</div>

<div id="confirm-delete-modal" class="modal">
   <div class="flex-vertical modal-content" style="width:300px;">
      <div id="close" class="close">&times;</div>
      <p>Really delete button?</p>
      <button class="config-button" type="submit" form="config-form" onclick="setAction('delete')">Confirm</button>
   </div>
</div>

<script src="script/flexscreen.js<?php echo versionQuery();?>"></script>
<script src="script/modal.js<?php echo versionQuery();?>"></script>
<script src="script/displayConfig.js<?php echo versionQuery();?>"></script>
<script>
   setMenuSelection(MenuItem.CONFIGURATION);

   function setDisplayId(displayId)
   {
      var input = document.getElementById('display-id-input');
      input.value = displayId;
   }

   function setDisplayConfig(displayId, presentationId, enabled)
   {
      setDisplayId(displayId);
      
      document.getElementById('presentation-id-input').value = presentationId;
      document.getElementById('enabled-input').checked = enabled;
   }

   function setAction(action)
   {
      var input = document.getElementById('action-input');
      input.value = action;
   }

   // Start a 10 second timer to update the display status LEDs.
   setInterval(function(){updateDisplayStatus();}, 10000);
</script>

</body>

</html>
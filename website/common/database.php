<?php

require_once 'databaseKey.php';
require_once 'time.php';

interface Database
{
   public function connect();

   public function disconnect();

   public function isConnected();

   public function query(
      $query);
}

class MySqlDatabase implements Database
{
   function __construct(
      $server,
      $user,
      $password,
      $database)
   {
      $this->server = $server;
      $this->user = $user;
      $this->password = $password;
      $this->database = $database;
   }

   public function connect()
   {
      // Create connection
      $this->connection = new mysqli($this->server, $this->user, $this->password, $this->database);

      // Check connection
      if ($this->connection->connect_error)
      {
         // TODO?
      }
      else
      {
         $this->isConnected = true;
      }
   }

   public function disconnect()
   {
      if ($this->isConnected())
      {
         $this->connection->close();
      }
   }

   public function isConnected()
   {
      return ($this->isConnected);
   }

   public function query(
      $query)
   {
      $result = NULL;

      if ($this->isConnected())
      {
         $result = $this->connection->query($query);
      }

      return ($result);
   }

   public static function countResults($result)
   {
      return (mysqli_num_rows($result));
   }

   public function rowsAffected()
   {
      return(mysqli_affected_rows($this->connection));
   }

   public function lastInsertId()
   {
      return (mysqli_insert_id($this->connection));
   }

   public function lastQuery()
   {
      return ($this->connection->last_query());
   }

   protected function getConnection()
   {
      return ($this->connection);
   }

   private $server = "";

   private $user = "";

   private $password = "";

   private $database = "";

   private $connection;

   private $isConnected = false;
}

class FactoryStatsGlobalDatabase extends MySqlDatabase
{
   public static function getInstance()
   {
      if (!FactoryStatsGlobalDatabase::$databaseInstance)
      {
         self::$databaseInstance = new FactoryStatsGlobalDatabase();
         
         self::$databaseInstance->connect();
      }
      
      return (self::$databaseInstance);
   }
   
   public function __construct()
   {
      global $SERVER, $GLOBAL_USER, $GLOBAL_PASSWORD, $GLOBAL_DATABASE;
      
      parent::__construct($SERVER, $GLOBAL_USER, $GLOBAL_PASSWORD, $GLOBAL_DATABASE);
   }
   
   // **************************************************************************
   
   public function isDisplayRegistered($uid)
   {
      $query = "SELECT * FROM display WHERE uid = \"$uid\";";
      
      $result = $this->query($query);
      
      return (FactoryStatsGlobalDatabase::countResults($result) == 1);
   }
   
   public function registerDisplay($uid)
   {
      $query = "INSERT INTO display (uid, subdomain) VALUES (\"$uid\", \"\");";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function uregisterDisplay($uid)
   {
      $query = "DELETE FROM display WHERE uid = \"$uid\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function associateDisplayWithSubdomain($uid, $subdomain)
   {
      $query = "UPDATE display SET subdomain = '$subdomain' WHERE uid = '$uid';";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getAssociatedSubdomainForDisplay($uid)
   {
      $domain = "";
      
      $query = "SELECT * FROM display WHERE uid = '$uid';";
      
      $result = $this->query($query);
      
      if ($result && ($row = $result->fetch_assoc()))
      {
         $domain = $row["subdomain"];
      }
      
      return ($domain);
   }
   
   // **************************************************************************

   private static $databaseInstance = null;
}

class FlexscreenDatabase extends MySqlDatabase
{
   public static function getInstance()
   {
      if (!FlexscreenDatabase::$databaseInstance)
      {
         self::$databaseInstance = new FlexscreenDatabase();
         
         self::$databaseInstance->connect();
      }
      
      return (self::$databaseInstance);
   }
   
   public function __construct()
   {
      global $SERVER, $USER, $PASSWORD, $DATABASE;
      
      parent::__construct($SERVER, $USER, $PASSWORD, $DATABASE);
   }
   
   // **************************************************************************
   
   public function getUser($userId)
   {
      $query = "SELECT * FROM user WHERE userId = \"$userId\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getUserByEmployeeNumberName($employeeNumber)
   {
      $query = "SELECT * FROM user WHERE employeeNumber = \"$employeeNumber\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getUserByName($username)
   {
      $query = "SELECT * FROM user WHERE username = \"$username\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getUsers()
   {
      $query = "SELECT * FROM user ORDER BY firstName ASC;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getUsersByRole($role)
   {
      $roleClause = "";
      if ($role != Role::UNKNOWN)
      {
         $roleClause = "WHERE roles = $role";
      }
      
      $query = "SELECT * FROM user $roleClause ORDER BY firstName ASC;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getUsersByRoles($roles)
   {
      $result = null;
      
      if (sizeof($roles) > 0)
      {
         $rolesClause = "roles in (";
         
         $count = 0;
         foreach ($roles as $role)
         {
            $rolesClause .= "'$role'";
            
            $count++;
            
            if ($count < sizeof($roles))
            {
               $rolesClause .= ", ";
            }
         }
         
         $rolesClause .= ")";
         
         $query = "SELECT * FROM user WHERE $rolesClause ORDER BY firstName ASC;";
         
         $result = $this->query($query);
      }
      
      return ($result);
   }
   
   public function newUser($userInfo)
   {
      $query =
      "INSERT INTO user " .
      "(employeeNumber, username, password, roles, permissions, firstName, lastName, email, authToken, assignedStations) " .
      "VALUES " .
      "('$userInfo->employeeNumber', '$userInfo->username', '$userInfo->password', '$userInfo->roles', '$userInfo->permissions', '$userInfo->firstName', '$userInfo->lastName', '$userInfo->email', '$userInfo->authToken', '$userInfo->assignedStations');";

      $result = $this->query($query);
      
      return ($result);
   }
   
   public function updateUser($userInfo)
   {
      $query =
      "UPDATE user " .
      "SET employeeNumber = '$userInfo->employeeNumber', username = '$userInfo->username', password = '$userInfo->password', roles = '$userInfo->roles', permissions = '$userInfo->permissions', firstName = '$userInfo->firstName', lastName = '$userInfo->lastName', email = '$userInfo->email', authToken = '$userInfo->authToken', assignedStations = '$userInfo->assignedStations' " .
      "WHERE userId = '$userInfo->userId';";

      $result = $this->query($query);
      
      return ($result);
   }
   
   public function deleteUser($userId)
   {
      $query = "DELETE FROM user WHERE userId = '$userId';";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   // **************************************************************************
   
   public function getDisplay($displayId)
   {
      $query = "SELECT * from display WHERE displayId = \"$displayId\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getDisplays()
   {
      $query = "SELECT * from display ORDER BY uid DESC;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getDisplayByUid($uid)
   {
      $query = "SELECT * from display WHERE uid = \"$uid\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function newDisplay($displayInfo)
   {
      $lastContact = Time::toMySqlDate($displayInfo->lastContact);
      
      $enabled = ($displayInfo->enabled ? "true" : "false");
      
      $query =
      "INSERT INTO display (uid, ipAddress, name, presentationId, lastContact, enabled) " .
      "VALUES ('$displayInfo->uid', '$displayInfo->ipAddress', '$displayInfo->name', '$displayInfo->presentationId', '$lastContact', $enabled);";

      $this->query($query);
   }
   
   public function updateDisplay($displayInfo)
   {
      $lastContact = Time::toMySqlDate($displayInfo->lastContact);
      
      $enabled = ($displayInfo->enabled ? "true" : "false");
      
      $query =
      "UPDATE display " .
      "SET uid = \"$displayInfo->uid\", ipAddress = \"$displayInfo->ipAddress\", name = \"$displayInfo->name\", presentationId = \"$displayInfo->presentationId\", lastContact = \"$lastContact\", enabled = $enabled " .
      "WHERE displayId = $displayInfo->displayId;";

      $this->query($query);
   }
   
   public function deleteDisplay($displayId)
   {
      $query = "DELETE FROM display WHERE displayId = $displayId;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   // **************************************************************************
   
   public function getButton($buttonId)
   {
      $query = "SELECT * from button WHERE buttonId = \"$buttonId\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getButtons()
   {
      $query = "SELECT * from button ORDER BY uid ASC;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getButtonsForStation($stationId)
   {
      $query = "SELECT * from button WHERE stationId = \"$stationId\" ORDER BY lastContact DESC;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getButtonByUid($uid)
   {
      $query = "SELECT * from button WHERE uid = \"$uid\";";

      $result = $this->query($query);
     
      return ($result);
   }
   
   public function buttonExists($uid)
   {
      $query = "SELECT buttonId from button WHERE uid = \"$uid\";";
      
      $result = $this->query($query);
      
      return ($result && ($result->num_rows > 0));
   }
   
   public function newButton($buttonInfo)
   {
      $lastContact = Time::toMySqlDate($buttonInfo->lastContact);
      
      $clickAction = $buttonInfo->getButtonAction(ButtonPress::SINGLE_CLICK);
      $doubleClickAction = $buttonInfo->getButtonAction(ButtonPress::DOUBLE_CLICK);
      $holdAction = $buttonInfo->getButtonAction(ButtonPress::HOLD);
      
      $enabled = ($buttonInfo->enabled ? "true" : "false");
      
      $query =
      "INSERT INTO button (uid, ipAddress, name, stationId, clickAction, doubleClickAction, holdAction, lastContact, enabled) " .
      "VALUES ('$buttonInfo->uid', '$buttonInfo->ipAddress', '$buttonInfo->name', '$buttonInfo->stationId', '$clickAction', '$doubleClickAction', '$holdAction', '$lastContact', $enabled);";

      $this->query($query);
   }
   
   public function updateButton($buttonInfo)
   {
      $lastContact = Time::toMySqlDate($buttonInfo->lastContact);
      
      $clickAction = $buttonInfo->getButtonAction(ButtonPress::SINGLE_CLICK);
      $doubleClickAction = $buttonInfo->getButtonAction(ButtonPress::DOUBLE_CLICK);
      $holdAction = $buttonInfo->getButtonAction(ButtonPress::HOLD);
      
      $enabled = ($buttonInfo->enabled ? "true" : "false");
      
      $query =
      "UPDATE button " .
      "SET uid = \"$buttonInfo->uid\", ipAddress = \"$buttonInfo->ipAddress\", name = \"$buttonInfo->name\", stationId = \"$buttonInfo->stationId\", clickAction = \"$clickAction\", doubleClickAction = \"$doubleClickAction\", holdAction = \"$holdAction\", lastContact = \"$lastContact\", enabled = $enabled " .
      "WHERE buttonId = $buttonInfo->buttonId;";

      $this->query($query);
   }
   
   public function deleteButton($buttonId)
   {
      $query = "DELETE FROM button WHERE buttonId = $buttonId;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   // **************************************************************************
   
   public function getSensor($sensorId)
   {
      $query = "SELECT * from sensor WHERE sensorId = \"$sensorId\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getSensors()
   {
      $query = "SELECT * from sensor ORDER BY uid ASC;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getSensorByUid($uid)
   {
      $query = "SELECT * from sensor WHERE uid = \"$uid\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function sensorExists($uid)
   {
      $query = "SELECT sensorId from sensors WHERE uid = \"$uid\";";
      
      $result = $this->query($query);
      
      return ($result && ($result->num_rows > 0));
   }
   
   public function newSensor($sensorInfo)
   {
      $lastContact = Time::toMySqlDate($sensorInfo->lastContact);
      
      $enabled = ($sensorInfo->enabled ? "true" : "false");
      
      $query =
      "INSERT INTO sensor (uid, ipAddress, version, name, sensorType, stationId, lastContact, enabled) " .
      "VALUES ('$sensorInfo->uid', '$sensorInfo->ipAddress', '$sensorInfo->version', '$sensorInfo->name', '$sensorInfo->sensorType', '$sensorInfo->stationId', '$lastContact', $enabled);";
      
      $this->query($query);
   }
   
   public function updateSensor($sensorInfo)
   {
      $lastContact = Time::toMySqlDate($sensorInfo->lastContact);
      
      $enabled = ($sensorInfo->enabled ? "true" : "false");
      
      $query =
      "UPDATE sensor " .
      "SET uid = \"$sensorInfo->uid\", ipAddress = \"$sensorInfo->ipAddress\", version = \"$sensorInfo->version\", name = \"$sensorInfo->name\", sensorType = \"$sensorInfo->sensorType\", stationId = \"$sensorInfo->stationId\", lastContact = \"$lastContact\", enabled = $enabled " .
      "WHERE sensorId = $sensorInfo->sensorId;";
      
      $this->query($query);
   }
   
   public function deleteSensor($sensorId)
   {
      $query = "DELETE FROM sensor WHERE sensorId = $sensorId;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   // **************************************************************************
   
   public function getStation($stationId)
   {
      $query = "SELECT * from station WHERE stationId = \"$stationId\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getStations()
   {
      $query = "SELECT * from station ORDER BY name ASC;";
      
      $result = $this->query($query);
      
      return ($result);
   } 
   
   public function stationExists($stationId)
   {
      $query = "SELECT stationId from station WHERE stationId = \"$stationId\";";

      $result = $this->query($query);
      
      return ($result && ($result->num_rows > 0));
   }
   
   public function newStation($stationInfo)
   {
      $now = Time::toMySqlDate(Time::now("Y-m-d H:i:s"));
      
      $query = "INSERT INTO station (name, label, description, cycleTime, updateTime) VALUES ('$stationInfo->name', '$stationInfo->label', '$stationInfo->description', '$stationInfo->cycleTime', $now');";

      $this->query($query);
   }
   
   public function addStation($stationInfo)
   {
      $query =
      "INSERT INTO station (name, label, description, cycleTime) " .
      "VALUES ('$stationInfo->name', '$stationInfo->label', '$stationInfo->description', '$stationInfo->cycleTime');";

      $this->query($query);
   }
   
   public function updateStation($stationInfo)
   {
      $query =
      "UPDATE station " .
      "SET name = \"$stationInfo->name\", label = \"$stationInfo->label\", description = \"$stationInfo->description\", cycleTime = $stationInfo->cycleTime " .
      "WHERE stationId = $stationInfo->stationId;";

      $this->query($query);
   }
   
   public function deleteStation($stationId)
   {
      $query = "DELETE FROM station WHERE stationId = $stationId;";
      
      $this->query($query);
      
      $query = "DELETE FROM screencount WHERE stationId = $stationId;";
      
      $this->query($query);
      
      $query = "UPDATE button SET stationId = NULL WHERE stationId = $stationId;";
      
      $this->query($query);
      
      $query = "UPDATE display SET stationId = NULL WHERE stationId = $stationId;";
      
      $this->query($query);
   }
   
   public function touchStation($stationId)
   {
      $now = Time::toMySqlDate(Time::now("Y-m-d H:i:s"));

      // Record last update time.
      $query = "UPDATE station SET updateTime = \"$now\" WHERE stationId = \"$stationId\";";

      $this->query($query);
   }
   
   // **************************************************************************
   
   const ALL_SHIFTS = 0;

   public function getCount($stationId, $shiftId, $startDateTime, $endDateTime)
   {
      $screenCount = 0;
      
      $stationClause = ($stationId == "ALL") ? "" : "stationId = \"$stationId\" AND";
      $shiftClause = ($shiftId == FlexscreenDatabase::ALL_SHIFTS) ? "" : "shiftId = \"$shiftId\" AND";
      $query = "SELECT SUM(count) FROM screencount WHERE $stationClause $shiftClause dateTime BETWEEN '" . Time::toMySqlDate($startDateTime) . "' AND '" . Time::toMySqlDate($endDateTime) . "';";

      $result = $this->query($query);
      
      if ($result && ($row = $result->fetch_assoc()))
      {
         $screenCount = intval($row['SUM(count)']);
      }
      
      return ($screenCount);
   }
   
   public function getHourlyCounts($stationId, $shiftId, $startDateTime, $endDateTime)
   {
       $stationClause = ($stationId == "ALL") ? "" : "stationId = \"$stationId\" AND";
       $shiftClause = ($shiftId == FlexscreenDatabase::ALL_SHIFTS) ? "" : "shiftId = \"$shiftId\" AND";
       $query = "SELECT stationId, shiftId, dateTime, count FROM screencount WHERE $stationClause $shiftClause dateTime BETWEEN '" . Time::toMySqlDate($startDateTime) . "' AND '" . Time::toMySqlDate($endDateTime) . "' ORDER BY stationId ASC, dateTime ASC;";

       $result = $this->query($query);
             
       return ($result);
   }
   
   public function updateCount($stationId, $shiftId, $screenCount)
   {
      $now = Time::toMySqlDate(Time::now("Y-m-d H:i:s"));
      $nowHour = Time::toMySqlDate(Time::now("Y-m-d H:00:00"));
      
      // Calculate the time since the update (in seconds).
      $countTime = FlexscreenDatabase::calculateCountTime($stationId);
      
      // Determine if we have an entry for this station/hour.
      $query = "SELECT * from screencount WHERE stationId = \"$stationId\" AND shiftId = \"$shiftId\" AND dateTime = \"$nowHour\";";

      $result = $this->query($query);
      
      // New entry.
      if ($result && ($result->num_rows == 0))
      {
         $query =
         "INSERT INTO screencount " .
         "(stationId, shiftId, dateTime, count, countTime, firstEntry, lastEntry) " .
         "VALUES " .
         "('$stationId', '$shiftId', '$nowHour', '$screenCount', '$countTime', '$now', '$now');";
         
         $this->query($query);
      }
      // Updated entry.
      else
      {
         // Update counter count.
         $query = 
            "UPDATE screencount SET count = count + $screenCount, countTime = countTime + $countTime, lastEntry = \"$now\" " .
            "WHERE stationId = \"$stationId\" AND shiftId = \"$shiftId\" AND dateTime = \"$nowHour\";";

         $this->query($query);
      }
      
      // Store a new updateTime for this station.
      $this->touchStation($stationId);
   }
   
   public function getUpdateTime($stationId)
   {
      $updateTime = "";
      
      $query = "SELECT updateTime from station WHERE stationId = \"$stationId\";";

      $result = $this->query($query);
      
      if ($result && ($row = $result->fetch_assoc()))
      {
         $updateTime = Time::fromMySqlDate($row["updateTime"], "Y-m-d H:i:s");
      }
      
      return ($updateTime);
   }
   
   public function getFirstEntry($stationId, $shiftId, $startDateTime, $endDateTime)
   {
      $firstEntry = null;
      
      $query = 
         "SELECT firstEntry FROM screencount " . 
         "WHERE stationId = \"$stationId\" AND shiftId = \"$shiftId\" AND dateTime >= '" . Time::toMySqlDate($startDateTime) . "' AND dateTime < '" . Time::toMySqlDate($endDateTime) . "' ORDER BY dateTime ASC LIMIT 1;";

      $result = $this->query($query);
      
      if ($result && ($row = $result->fetch_assoc()) && $row["firstEntry"])
      {
         $firstEntry = Time::fromMySqlDate($row["firstEntry"], "Y-m-d H:i:s");
      }
      
      return ($firstEntry);
   }
   
   public function getLastEntry($stationId,  $shiftId, $startDateTime, $endDateTime)
   {
      $lastEntry = null;
      
      $query = 
         "SELECT lastEntry FROM screencount " .
         "WHERE stationId = \"$stationId\" AND shiftId = \"$shiftId\" AND dateTime >= '" . Time::toMySqlDate($startDateTime) . "' AND dateTime < '" . Time::toMySqlDate($endDateTime) . "' ORDER BY dateTime DESC LIMIT 1;";

      $result = $this->query($query);
      
      if ($result && ($row = $result->fetch_assoc()) && $row["lastEntry"])
      {
         $lastEntry = Time::fromMySqlDate($row["lastEntry"], "Y-m-d H:i:s");
      }
      
      return ($lastEntry);
   }
   
   // **************************************************************************
   
   public function getCurrentBreakId($stationId, $shiftId)
   {
      $breakId = 0;
      
      $query =
      "SELECT breakId FROM break WHERE stationId = $stationId AND shiftId = $shiftId AND endTime IS NULL";

      $result = $this->query($query);
      
      if ($result && ($row = $result->fetch_assoc()))
      {
         $breakId = $row["breakId"];
      }
      
      return ($breakId);
   }
   
   public function isOnBreak($stationId, $shiftId)
   {
      return ($this->getCurrentBreakId($stationId, $shiftId) != 0);
   }
   
   public function startBreak($stationId, $shiftId, $breakDescriptionId, $startDateTime)
   {
      $success = false;
      
      if (!$this->isOnBreak($stationId, $shiftId))
      {
         $query =
         "INSERT INTO break " .
         "(stationId, shiftId, breakDescriptionId, startTime) " .
         "VALUES " .
         "('$stationId', '$shiftId', '$breakDescriptionId', '" . Time::toMySqlDate($startDateTime) . "');";

         $success = $this->query($query);
      }
      
      return ($success);
   }
   
   public function endBreak($stationId, $shiftId, $endDateTime)
   {
      $success = false;
      
      $breakId = $this->getCurrentBreakId($stationId, $shiftId);
      
      if ($breakId != 0)
      {
         $query =
         "UPDATE break " .
         "SET endTime = \"" . Time::toMySqlDate($endDateTime) . "\" " .
         "WHERE breakId = $breakId;";
         
         $success = $this->query($query);
      }
      
      return ($success);
   }
   
   public function getBreak($breakId)
   {
      $query = "SELECT * from break WHERE breakId = \"$breakId\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getBreaks($stationId, $shiftId, $startDateTime, $endDateTime)
   {
      $stationClause = ($stationId == "ALL") ? "" : "stationId = \"$stationId\" AND";
      $shiftClause = ($shiftId == FlexscreenDatabase::ALL_SHIFTS) ? "" : "shiftId = \"$shiftId\" AND";
      $query = "SELECT * FROM break WHERE $stationClause $shiftClause startTime BETWEEN '" . Time::toMySqlDate($startDateTime) . "' AND '" . Time::toMySqlDate($endDateTime) . "' ORDER BY stationId ASC, startTime ASC;";

      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getBreakTime($stationId, $shiftId, $startDateTime, $endDateTime)
   {
      $breakTime = 0;
      
      $query = "SELECT * FROM break WHERE stationId = \"$stationId\" AND shiftId = \"$shiftId\" AND startTime BETWEEN '" . Time::toMySqlDate($startDateTime) . "' AND '" . Time::toMySqlDate($endDateTime) . "' ORDER BY startTime DESC;";

      $result = $this->query($query);
      
      $updateTime = getUpdateTime($stationId);
      
      while ($result && ($row = $result->fetch_assoc()))
      {
         // Only count complete breaks.
         $isCompleteBreak = ($row["endTime"] != null);
         
         // Don't count breaks that start *after* the last screen count.
         $startTime = Time::fromMySqlDate($row["startTime"], "Y-m-d H:i:s");
         $isValidBreak = (new DateTime($startTime) < new DateTime($updateTime));

         if ($isCompleteBreak && $isValidBreak)
         {
            $breakTime += Time::differenceSeconds($row["startTime"], $row["endTime"]);
         }
      }
      
      return ($breakTime);
   }
   
   // **************************************************************************
   
   public function getBreakDescription($breakDescriptionId)
   {
      $query = "SELECT * from breakdescription WHERE breakDescriptionId = \"$breakDescriptionId\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getBreakDescriptions()
   {
      $query = "SELECT * FROM breakdescription;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function newBreakDescription($breakDescription)
   {
      $query =
      "INSERT INTO breakdescription (code, description) " .
      "VALUES ('$breakDescription->code', '$breakDescription->description');";
      
      $this->query($query);
   }
   
   public function updateBreakDescription($breakDescription)
   {
      $query =
      "UPDATE breakdescription " .
      "SET code = \"$breakDescription->code\", description = \"$breakDescription->description\" " .
      "WHERE breakDescriptionId = $breakDescription->breakDescriptionId;";
      
      $this->query($query);
   }
   
   public function deleteBreakDescription($breakDescriptionId)
   {
      $query = "DELETE FROM breakdescription WHERE breakDescriptionId = $breakDescriptionId;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   // **************************************************************************
   
   public function getSettings()
   {
      $query = "SELECT * from settings;";

      $result = $this->query($query);
      
      return ($result);
   }
       
   // **************************************************************************

   public function getShift($shiftId)
   {
      $query = "SELECT * from shift WHERE shiftId = \"$shiftId\";";

      $result = $this->query($query);
      
      return ($result);
   }

   public function getShifts()
   {
      $query = "SELECT * from shift ORDER BY startTime ASC;";
      
      $result = $this->query($query);
      
      return ($result);
   }

   public function newShift($shiftInfo)
   {
      $query =
      "INSERT INTO shift (shiftName, startTime, endTime) " .
      "VALUES ('$shiftInfo->shiftName', '$shiftInfo->startTime', '$shiftInfo->endTime');";
      
      $this->query($query);
   }

   public function updateShift($shiftInfo)
   {
      $query =
      "UPDATE shift " .
      "SET shiftName = \"$shiftInfo->shiftName\", startTime = \"$shiftInfo->startTime\", endTime = \"$shiftInfo->endTime\" WHERE shiftId = $shiftInfo->shiftId;";
      
      $this->query($query);
   }

   public function deleteShift($shiftId)
   {
      $query = "DELETE FROM shift WHERE shiftId = $shiftId;";
      
      $this->query($query);
      
      $query = "DELETE FROM screencount WHERE shiftId = $shiftId;";
      
      $this->query($query);
   }

   // **************************************************************************
   
   protected function calculateCountTime($stationId)
   {
      $countTime = 0;
      
      $now = new DateTime("now", new DateTimeZone('America/New_York'));
      
      $updateTime = new DateTime(FlexscreenDatabase::getUpdateTime($stationId), new DateTimeZone('America/New_York'));
      
      if ($updateTime)
      {
         $interval = $updateTime->diff($now);
         
         $sameDay = (($interval->days == 0) &&
                     (intval($now->format('d')) == intval($updateTime->format('d'))));
         
         if ($sameDay)
         {
            // Convert to seconds.
            $countTime = (($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s);
         }
      }
      
      return ($countTime);
   }
   
   // **************************************************************************
   
   public function getCustomer($customerId)
   {
      $query = "SELECT * from customer WHERE customerId = \"$customerId\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getCustomerFromSubdomain($subdomain)
   {
      $query = "SELECT * from customer WHERE subdomain = \"$subdomain\";";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   // **************************************************************************
   
   public function getPresentations()
   {
      $query = "SELECT * from presentation ORDER BY name ASC;";
      
      $result = $this->query($query);
      
      return ($result);
   }
   
   public function getPresentation($presentationId)
   {
      $query = "SELECT * from presentation WHERE presentationId = \"$presentationId\";";
      
      $result = $this->query($query);
      
      return ($result);      
   }
   
   public function newPresentation($presentationInfo)
   {
      $query =
      "INSERT INTO presentation (name) " .
      "VALUES ('$presentationInfo->name');";
      
      $this->query($query);
   }
   
   public function updatePresentation($presentationInfo)
   {
      $query =
      "UPDATE presentation " .
      "SET name = \"$presentationInfo->name\" " .
      "WHERE presentationId = $presentationInfo->presentationId;";
      
      $this->query($query);
   }
   
   public function deletePresentation($presentationId)
   {
      $query = "DELETE FROM presentation WHERE presentationId = $presentationId;";
      
      $this->query($query);
      
      $query = "DELETE FROM slide WHERE presentationId = $presentationId;";
      
      $this->query($query);
   }
      
   // **************************************************************************
   
   public function getSlide($slideId)
   {
      $query = "SELECT * from slide WHERE slideId = \"$slideId\";";
      
      $result = $this->query($query);
      
      return ($result);      
   }
   
   public function getSlidesForPresentation($presentationId)
   {
      $query = "SELECT * from slide WHERE presentationId = \"$presentationId\" ORDER BY slideIndex ASC";
      
      $result = $this->query($query);
      
      return ($result);      
   }
   
   public function newSlide($slideInfo)
   {
      $enabled = ($slideInfo->enabled ? "true" : "false");
      
      $query =
      "INSERT INTO slide (presentationId, slideType, slideIndex, duration, enabled, reloadInterval, url, image, shiftId, stationId1, stationId2, stationId3, stationId4) " .
      "VALUES ('$slideInfo->presentationId', '$slideInfo->slideType', '$slideInfo->slideIndex', '$slideInfo->duration', $enabled, '$slideInfo->reloadInterval', '$slideInfo->url', '$slideInfo->image', '$slideInfo->shiftId', '{$slideInfo->stationIds[0]}', '{$slideInfo->stationIds[1]}', '{$slideInfo->stationIds[2]}', '{$slideInfo->stationIds[3]}');";

      $this->query($query);
   }
   
   public function updateSlide($slideInfo)
   {
      $enabled = ($slideInfo->enabled ? "true" : "false");
      
      $query =
      "UPDATE slide " .
      "SET presentationId = \"$slideInfo->presentationId\", slideType = \"$slideInfo->slideType\", slideIndex = \"$slideInfo->slideIndex\", duration = \"$slideInfo->duration\", enabled = $enabled, reloadInterval = \"$slideInfo->reloadInterval\", url = \"$slideInfo->url\", image = \"$slideInfo->image\", shiftId = \"$slideInfo->shiftId\", stationId1 = \"{$slideInfo->stationIds[0]}\", stationId2 = \"{$slideInfo->stationIds[1]}\", stationId3 = \"{$slideInfo->stationIds[2]}\", stationId4 = \"{$slideInfo->stationIds[3]}\" " .
      "WHERE slideId = $slideInfo->slideId;";

      $this->query($query);
   }
   
   public function updateSlideOrder($slideId, $slideIndex)
   {
      $query =
      "UPDATE slide SET slideIndex = \"$slideIndex\" WHERE slideId = $slideId;";
      
      $this->query($query);
   }
   
   public function deleteSlide($slideId)
   {
      $query = "DELETE FROM slide WHERE slideId = $slideId;";
      
      $this->query($query);
   }
   
   // **************************************************************************
   
   
   private static $databaseInstance = null;
}

?>
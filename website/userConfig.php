<?php

require_once 'common/database.php';
require_once 'common/header.php';
require_once 'common/params.php';
require_once 'common/userInfo.php';
require_once 'common/version.php';

Time::init();

session_start();

if (!(Authentication::isAuthenticated() &&
      Authentication::checkPermissions(Permission::USER_CONFIG)))
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
         <th>Employee #</th>
         <th>Name</th>
         <th>Username</th>
         <th>Role</th>
         <th>Email</th>
         <th></th>
         <th></th>
      </tr>
HEREDOC;
   
   $database = FlexscreenDatabase::getInstance();
   
   if ($database && $database->isConnected())
   {
      $result = $database->getUsers();
      
      while ($result && $row = $result->fetch_assoc())
      {
         $userInfo = UserInfo::load(intval($row["userId"]));
         
         $name = $userInfo->getFullName();
         
         $roleName = "Unassigned";
         $role = Role::getRole($userInfo->roles);
         if ($role)
         {
            $roleName = $role->roleName;
         }
         
         echo 
<<<HEREDOC
         <tr>
            <td>$userInfo->employeeNumber</td>
            <td>$name</td>
            <td>$userInfo->username</td>
            <td>$roleName</td>
            <td>$userInfo->email</td>
            <td><button class="config-button" onclick="setUserInfo($userInfo->userId, $userInfo->employeeNumber, '$userInfo->firstName', '$userInfo->lastName', '$userInfo->username', '$userInfo->password', '$userInfo->roles', '$userInfo->email', '$userInfo->authToken'); showModal('config-modal');">Configure</button></div></td>
            <td><button class="config-button" onclick="setUserId($userInfo->userId); showModal('confirm-delete-modal');">Delete</button></div></td>
         </tr>
HEREDOC;
      }
   }
   
   echo "</table>";
}

function getRoleOptions()
{
   $options = "";

   $roles = Role::getRoles();
   
   $options .= "<option style=\"display:none\">";
   
   foreach ($roles as $role)
   {
      $options .= "<option value=\"$role->roleId\">$role->roleName</option>";
   }
   
   return ($options);
}

function addUser($employeeNumber, $firstName, $lastName, $username, $password, $role, $email)
{
   $userInfo = new UserInfo();
   
   $roleDetails = Role::getRole($role);
   
   $userInfo->employeeNumber = $employeeNumber;
   $userInfo->firstName = $firstName;
   $userInfo->lastName = $lastName;
   $userInfo->username = $username;
   $userInfo->password = $password;   
   $userInfo->roles = $role;
   if ($roleDetails)
   {
      $userInfo->permissions = $roleDetails->defaultPermissions;
   }
   $userInfo->email = $email;
   
   $database = FlexscreenDatabase::getInstance();
   
   if ($database && $database->isConnected())
   {
      $database->newUser($userInfo);
   }
}

function deleteUser($userId)
{
   $database = FlexscreenDatabase::getInstance();
   
   if ($database && $database->isConnected())
   {
      $database->deleteUser($userId);
   }
}

function updateUser($userId, $employeeNumber, $firstName, $lastName, $username, $password, $role, $email)
{
   $userInfo = UserInfo::load($userId);
   
   if ($userInfo)
   {
      $userInfo->employeeNumber = $employeeNumber;
      $userInfo->firstName = $firstName;
      $userInfo->lastName = $lastName;
      $userInfo->username = $username;
      $userInfo->password = $password;      
      $userInfo->roles = $role;
      $userInfo->email = $email;
      
      $database = FlexscreenDatabase::getInstance();
      
      if ($database && $database->isConnected())
      {
         $database->updateUser($userInfo);
      }
   }
}

// *****************************************************************************
//                              Action handling

Time::init();

$params = Params::parse();

switch ($params->get("action"))
{
   case "delete":
   {
      deleteUser($params->get("userId"));
      break;      
   }
   
   case "update":
   {
      if ($params->getInt("userId") == UserInfo::UNKNOWN_USER_ID)
      {
         addUser(
            $params->get("employeeNumber"),
            $params->get("firstName"),
            $params->get("lastName"),
            $params->get("updatedUsername"),
            $params->get("updatedPassword"),
            $params->getInt("role"),
            $params->get("email"),
            $params->get("authToken"));
      }
      else
      {
         updateUser(
            $params->get("userId"),
            $params->get("employeeNumber"),
            $params->get("firstName"),
            $params->get("lastName"),
            $params->get("updatedUsername"),
            $params->get("updatedPassword"),
            $params->getInt("role"),
            $params->get("email"),
            $params->get("authToken"));
      }
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
   
   <title>User Config</title>
   
   <!--  Material Design Lite -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
   
   <link rel="stylesheet" type="text/css" href="css/flex.css<?php echo versionQuery();?>"/>
   <link rel="stylesheet" type="text/css" href="css/flexscreen.css<?php echo versionQuery();?>"/>
   <link rel="stylesheet" type="text/css" href="css/modal.css<?php echo versionQuery();?>"/>
   
</head>

<body>

<form id="config-form" method="post">
   <input id="action-input" type="hidden" name="action">
   <input id="user-id-input" type="hidden" name="userId">
   <input id="auth-token-input" type="hidden" name="authToken">   
</form>

<div class="flex-vertical" style="align-items: flex-start;">

   <?php Header::render(false);?>
   
   <?php include 'common/menu.php';?>
   
   <div class="main vertical">
      <div class="flex-vertical" style="align-items: flex-end;">
         <button class="config-button" onclick="setUserInfo('', '', '', '', '', '', '', ''); showModal('config-modal');">New User</button>
         <br>
         <?php renderTable();?>
      </div>
   </div>
     
</div>

<!--  Modal dialogs -->

<div id="config-modal" class="modal">
   <div class="flex-vertical modal-content" style="width:300px;">
      <div id="close" class="close">&times;</div>
      <label>Employee #</label>
      <input id="employee-number-input" type="text" form="config-form" name="employeeNumber" value=""> 
      <label>First Name</label>
      <input id="first-name-input" type="text" form="config-form" name="firstName" value="">
      <label>Last Name</label>
      <input id="last-name-input" type="text" form="config-form" name="lastName" value="">
      <label>Username</label>
      <input id="username-input" type="text" form="config-form" name="updatedUsername" value="">
      <label>Password</label>
      <input id="password-input" type="password" form="config-form" name="updatedPassword" value="">      
      <label>Role</label>
      <select id="role-input" form="config-form" name="role">
         <?php echo getRoleOptions();?>
      </select>
      <label>Email</label>
      <input id="email-input" type="text" form="config-form" name="email" value=""> 
      <div class="flex-horizontal">
         <button class="config-button" type="submit" form="config-form" onclick="setAction('update')">Save</button>
      </div>
   </div>
</div>

<div id="confirm-delete-modal" class="modal">
   <div class="flex-vertical modal-content" style="width:300px;">
      <div id="close" class="close">&times;</div>
      <p>Really delete user?</p>
      <button class="config-button" type="submit" form="config-form" onclick="setAction('delete')">Confirm</button>
   </div>
</div>

<script src="script/flexscreen.js<?php echo versionQuery();?>"></script>
<script src="script/modal.js<?php echo versionQuery();?>"></script>
<script src="script/userConfig.js<?php echo versionQuery();?>"></script>
<script>
   setMenuSelection(MenuItem.CONFIGURATION);
</script>

</body>

</html>
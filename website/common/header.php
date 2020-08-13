<?php 

require_once 'authentication.php';
require_once 'customerInfo.php';
require_once 'kiosk.php';
require_once 'root.php';
require_once 'shiftInfo.php';

class Header
{
   public static function getHtml($includeShiftIdInput)
   {
      global $ROOT;
      
      $shiftIdInput = "";
      if ($includeShiftIdInput)
      {
         $shiftOptions = ShiftInfo::getShiftOptions(ShiftInfo::getShiftId(), false);
         $shiftIdInput = 
<<<HEREDOC
         <select id="shift-id-input" name="shiftId" onchange="storeInSession('shiftId', this.value); update();">$shiftOptions</select>
HEREDOC;
      }
      
      $imagesFolder = CustomerInfo::getImagesFolder();
      
      $html = 
<<<HEREDOC
      <div class="flex-horizontal header">
         <div class="flex-horizontal" style="width:33%; justify-content:flex-start; margin-left: 20px;">
            $shiftIdInput
         </div>

         <div class="flex-horizontal" style="width:33%; justify-content:center;">
            <img src="$imagesFolder/flexscreen-logo.png" width="350px">
         </div>

         <div class="flex-horizontal" style="width:33%; justify-content:flex-end; margin-right: 20px;">
HEREDOC;
      
      if (!isKioskMode() && Authentication::isAuthenticated())
      {
         $username = Authentication::getAuthenticatedUser()->username;
         
         $html .=
<<<HEREDOC
            <i class="material-icons" style="margin-right:5px; color: #ffffff; font-size: 35px;">person</i>
            <div class="nav-username">$username &nbsp | &nbsp</div>
            <a class="nav-link" href="$ROOT/index.php?action=logout">Logout</a>
HEREDOC;
      }
         
      $html .= 
<<<HEREDOC
         </div>
      </div>
HEREDOC;
      
      return ($html);
   }
   
   public static function render($includeShiftIdInput)
   {
      echo (Header::getHtml($includeShiftIdInput));
   }
}

?>

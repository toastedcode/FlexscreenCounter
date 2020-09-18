<?php

require_once 'root.php';
require_once 'shiftInfo.php';
require_once 'stationInfo.php';

abstract class SlideType
{
   const UNKNOWN = 0;
   const FIRST = 1;
   const URL = SlideType::FIRST;
   const IMAGE = 2;
   const WORKSTATION_SUMMARY_PAGE = 3;
   const WORKSTATION_PAGE = 4;
   const LAST = 5;   
   const COUNT = SlideType::LAST - SlideType::FIRST;
   
   public static $values = array(SlideType::URL, SlideType::IMAGE, SlideType::WORKSTATION_SUMMARY_PAGE, SlideType::WORKSTATION_PAGE);
   
   public static function getLabel($slideType)
   {
      $labels = array("---", "Webpage", "Image", "Workstation Summary Page", "Workstation Page");
      
      return ($labels[$slideType]);
   }
}

class SlideInfo
{
   const UNKNOWN_SLIDE_ID = 0;
   
   const MAX_STATION_IDS = 4;
   
   public $slideId;
   public $presentationId;
   public $slideType;
   public $duration;
   
   // URL options
   public $url;
   
   // Image options
   public $image;
   
   // Page options
   public $shiftId;
   
   // Workstation page options
   public $stationIds;   
   
   public function __construct()
   {
      $this->slideId = SlideInfo::UNKNOWN_SLIDE_ID;
      $this->slideType = SlideType::UNKNOWN;
      $this->slideIndex = 0;
      $this->duration = 0;
      $this->url = "";
      $this->image = "";
      $this->shiftIds = ShiftInfo::UNKNOWN_SHIFT_ID;
      $this->stationIds = array(StationInfo::UNKNOWN_STATION_ID, StationInfo::UNKNOWN_STATION_ID, StationInfo::UNKNOWN_STATION_ID, StationInfo::UNKNOWN_STATION_ID);
   }
   
   public function initializeFromDatabaseRow($row)
   {
      $this->slideId = intval($row['slideId']);
      $this->presentationId = intval($row['presentationId']);
      $this->slideType = intval($row['slideType']);
      $this->slideIndex = intval($row['slideIndex']);
      $this->duration = intval($row['duration']);
      $this->url = $row['url'];
      $this->image = $row['image'];
      $this->shiftId = intval($row['shiftId']);
      
      for ($i = 0; $i < SlideInfo::MAX_STATION_IDS; $i++)
      {
         $rowName = "stationId" . ($i + 1);
         $this->stationIds[$i] = intval($row[$rowName]);
      }
      
   }
   
   public static function load($slideId)
   {
      $slideInfo = null;
      
      $database = FlexscreenDatabase::getInstance();
      
      if ($database && $database->isConnected())
      {
         $result = $database->getSlide($slideId);
         
         if ($result && ($row = $result->fetch_assoc()))
         {
            $slideInfo = new SlideInfo();
            
            $slideInfo->initializeFromDatabaseRow($row);
         }
      }
      
      return ($slideInfo);
   }
   
   public function getUrl()
   {
      global $ROOT;
      
      $url = "";
      
      switch ($this->slideType)
      {
         case SlideType::URL:
         {
            $url = $this->url;
            break;
         }
         
         case SlideType::IMAGE:
         {
            $url = "http://" . $_SERVER['HTTP_HOST'] . $ROOT . "/uploads/" . $this->image;
            break;
         }
         
         case SlideType::WORKSTATION_SUMMARY_PAGE:
         {
            $url = "http://" . $_SERVER['HTTP_HOST'] . $ROOT . "/workstationSummary.php?kiosk=true";
            
            if ($this->shiftId != ShiftInfo::UNKNOWN_SHIFT_ID)
            {
               $url .= "&shiftId=" . $this->shiftId;
            }
            
            $url .= "&authToken=jO9xT7iKvBwUsZDD56fV9UzFPin3qyvp";
            
            break;
         }
         
         case SlideType::WORKSTATION_PAGE:
         {
            $url = "http://" . $_SERVER['HTTP_HOST'] . $ROOT . "/workstation.php?kiosk=true";            
            
            foreach ($this->stationIds as $stationId)
            {
               if ($stationId != StationInfo::UNKNOWN_STATION_ID)
               {
                  $url .= "&stationId=" . $stationId;
               }
            }
            
            if ($this->shiftId != ShiftInfo::UNKNOWN_SHIFT_ID)
            {
               $url .= "&shiftId=" . $this->shiftId;
            }
            
            $url .= "&authToken=jO9xT7iKvBwUsZDD56fV9UzFPin3qyvp";
            
            break;
         }
         
         default:
         {
            break;
         }
      }
      
      return ($url);
   }
}

/*
if (isset($_GET["slideId"]))
{
   $slideId = $_GET["slideId"];
   $slideInfo = SlideInfo::load($slideId);
 
   if ($slideInfo)
   {
      echo "slideId: " .          $slideInfo->slideId .                        "<br/>";
      echo "presentationId: " .   $slideInfo->presentationId .                 "<br/>";
      echo "slideType: " .        SlideType::getLabel($slideInfo->slideType) . "<br/>";
      echo "slideIndex: " .       $slideInfo->slideIndex .                     "<br/>";
      echo "duration: " .         $slideInfo->duration .                       "<br/>";
      echo "url: " .              $slideInfo->url .                            "<br/>";
      echo "image: " .            $slideInfo->image .                          "<br/>";
      echo "shiftId: " .          $slideInfo->shiftId .                        "<br/>";
      echo "stationIds: [";

      foreach ($slideInfo->stationIds as $stationId)
      {
         echo $stationId . ", ";
      }
      echo "]<br><br>";
      
      echo "getUrl(): " . $slideInfo->getUrl();
   }
   else
   {
      echo "No slide info found.";
   }
}
*/
?>
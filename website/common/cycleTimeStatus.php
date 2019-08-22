<?php

require_once 'settings.php';
require_once 'time.php';
   
class CycleTimeStatus
{
   const UNKNOWN          = 0;
   const FIRST            = 1;
   const UNDER_CYCLE_TIME = CycleTimeStatus::FIRST;
   const NEAR_CYCLE_TIME  = 2;
   const OVER_CYCLE_TIME  = 3;
   const LAST             = CycleTimeStatus::OVER_CYCLE_TIME;
   
   const WARNING_THRESHOLD = 0.8;  // 80%
   const OVER_THRESHOLD = 1.0;     // 100%
   
   static function getClassLabel($cycleTimeStatus)
   {
      static $classLabels = array("", "under-cycle-time", "near-cycle-time", "over-cycle-time");
      
      $classLabel = "";
      
      if (($cycleTimeStatus >= CycleTimeStatus::FIRST) && ($cycleTimeStatus <= CycleTimeStatus::LAST))
      {
         $classLabel = $classLabels[$cycleTimeStatus];
      }
      
      return ($classLabel);
   }
   
   static function calculateCycleTimeStatus($updateTime, $cycleTime)
   {
      $cycleTimeStatus = CycleTimeStatus::UNKNOWN;
      
      if (($cycleTime > 0) &&
          Time::isToday($updateTime) &&
          Settings::isShiftActive(Time::now("H:i:s")))
      {
         $updateDateTime = new DateTime($updateTime);
         $now = new DateTime(Time::now("Y-m-d H:i:s"));
         
         $seconds = ($now->getTimestamp() - $updateDateTime->getTimestamp());
         
         if ($seconds > ($cycleTime * CycleTimeStatus::OVER_THRESHOLD))
         {
            $cycleTimeStatus = CycleTimeStatus::OVER_CYCLE_TIME;
         }
         else if  ($seconds > ($cycleTime * CycleTimeStatus::WARNING_THRESHOLD))
         {
            $cycleTimeStatus = CycleTimeStatus::NEAR_CYCLE_TIME;
         }
         else
         {
            $cycleTimeStatus = CycleTimeStatus::UNDER_CYCLE_TIME;
         }
      }
      
      return ($cycleTimeStatus);
   }
}

//echo CycleTimeStatus::getClassLabel(CycleTimeStatus::calculateCycleTimeStatus("2019-8-1 01:12:00", 30));
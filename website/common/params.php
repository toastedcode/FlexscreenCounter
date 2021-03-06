<?php

class Params extends ArrayObject
{
   public static function parse()
   {
      $params = new Params(array());
      
      if (isset($_SESSION))
      {
         foreach ($_SESSION as $key => $value)
         {
            $params[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
         }
      }
      
      if ($_SERVER["REQUEST_METHOD"] === "GET")
      {
         foreach ($_GET as $key => $value)
         {
            if (is_array($_GET[$key]))
            {
               $params[$key] = $_GET[$key];  // TODO: Sanitize input and implement for PUT, SESSION
            }
            else
            {
               $params[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
         }
      }
      else if ($_SERVER["REQUEST_METHOD"] === "POST")
      {
         if ($_SERVER["CONTENT_TYPE"] == "application/json")
         {
            $json = file_get_contents('php://input');

            if ($data = json_decode($json))
            {
               foreach ($data as $key => $value)
               {
                  $params[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
               }
            }
         }
         else
         {
            foreach ($_POST as $key => $value)
            {
               $params[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
         }
      }
      
      return $params;
   }
   
   public function keyExists($key)
   {
       return (isset($this[$key]));
   }
   
   public function get($key)
   {
      return (isset($this[$key]) ? $this[$key] : "");
   }
   
   public function getBool($key)
   {
      return (isset($this[$key]) && filter_var($this[$key], FILTER_VALIDATE_BOOLEAN));
   }
   
   public function getInt($key)
   {
      return (intval($this->get($key)));
   }
}

?>
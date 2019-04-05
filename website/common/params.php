<?php
class Params extends ArrayObject
{
   public static function parse()
   {
      $params = new Params(array());
      
      if ($_SERVER["REQUEST_METHOD"] === "GET")
      {
         foreach ($_GET as $key => $value)
         {
            $params[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
         }
      }
      else if ($_SERVER["REQUEST_METHOD"] === "POST")
      {
         foreach ($_POST as $key => $value)
         {
            $params[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
         }
      }
      
      return $params;
   }
   
   public function get($key)
   {
      return (isset($this[$key]) ? $this[$key] : "");
   }
}
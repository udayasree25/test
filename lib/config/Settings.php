<?php

namespace KrowdByz\config;
	
class Settings {
  
  public static $DATABASE = array(
    'host'      => 'localhost',
    'name'      => 'game',
    'username'  => 'root',
    'password'  => '',
   
  );
  
  public static $MAIN_PAGE = 'ssi.php'; // Used in redirects; leave blank ('') for root
  
}

?>
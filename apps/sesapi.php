<?php 
if(isset($_GET['restApi']) && !empty($_GET['restApi']) && $_GET['restApi'] == 'Sesapi'){ 
  define('_SESAPI_R_TARG', 'sesapi.php'); 
 if(!empty($_GET['sesapi_platform'])) 
  define('_SESAPI_PLATFORM_SERVICE', $_GET['sesapi_platform']); 
 else 
  define('_SESAPI_PLATFORM_SERVICE',0); 
  
if(!empty($_GET['sesapi_version'])) {
  if(_SESAPI_PLATFORM_SERVICE == 1) {
    define('_SESAPI_VERSION_IOS',$_GET['sesapi_version']); 
    define('_SESAPI_VERSION_ANDROID',0);
  } else if(_SESAPI_PLATFORM_SERVICE == 2) {
    define('_SESAPI_VERSION_ANDROID',$_GET['sesapi_version']); 
    define('_SESAPI_VERSION_IOS',0);
  }
} else {
  define('_SESAPI_VERSION_ANDROID',0); 
  define('_SESAPI_VERSION_IOS',0); 
}

if(empty($_FILES['image'])) 
  $_FILES['image'] = array(); 
elseif(empty($_FILES['video'])) 
  $_FILES['video'] = array(); 
}

if(!empty($_GET['sesapiPaymentModel'])) 
  $_SESSION['sesapiPaymentModel'] = true;
  
include $this->_rootPath
            . DIRECTORY_SEPARATOR
            .'application'. DIRECTORY_SEPARATOR
            . 'sesapi.php';

<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

use RobinTheHood\ModifiedStdModule\Classes\StdModule;
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

class gul_catImage extends StdModule
{
    public function __construct()
    {
        $this->init('MODULE_GUL_CATIMAGE');
        
        $this->keys = '';
    }

    function install() {}

    function remove() {}

    //--- BEGIN CUSTOM  CLASS METHODS ---//
    
    function getImage(string $image, string $dir, string $check, string $noImg, string $imageOrigin): string
    {
    
		    if (defined('MODULE_GUL_CATEGORIES_IMAGE_RESIZE_STATUS') && MODULE_GUL_CATEGORIES_IMAGE_RESIZE_STATUS == 'true')
        {
           
			      if ($imageOrigin != '')
            {
				        $resizedImage = DIR_WS_IMAGES . $dir . 'info_images/' . $imageOrigin;
			      }   
			      if (is_file(DIR_FS_CATALOG . $resizedImage))
            {
				        $image = $resizedImage;
			      }
        
			      return $image;
		    }
        
        return $image;
    }
}

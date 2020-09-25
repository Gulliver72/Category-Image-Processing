<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

use RobinTheHood\ModifiedStdModule\Classes\StdModule;
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

class gul_catImageResize extends StdModule
{
    public function __construct()
    {
        $this->init('MODULE_GUL_CATIMAGERESIZE');
        
        $this->keys = '';
    }

    function install() {}

    function remove() {}

    //--- BEGIN CUSTOM  CLASS METHODS ---//
    function categories_image_process($categories_image_name, $categories_image_name_process)
    {
		    if (defined('MODULE_GUL_CATEGORIES_IMAGE_RESIZE_STATUS') && MODULE_GUL_CATEGORIES_IMAGE_RESIZE_STATUS == 'true')
        {
            //image processing
            $this->image_process($categories_image_name, $categories_image_name_process);

            //set file rights
            $this->set_categories_images_file_rights($categories_image_name);
		    }
	  }
    
    function delete_category_image($category_image)
    {
        if (file_exists(DIR_FS_CATALOG_CATEGORIES_INFO_IMAGES . $category_image))
        {
            @ unlink(DIR_FS_CATALOG_CATEGORIES_INFO_IMAGES . $category_image);
        }
        if (file_exists(DIR_FS_CATALOG_CATEGORIES_THUMBNAIL_IMAGES . $category_image))
        {
            @ unlink(DIR_FS_CATALOG_CATEGORIES_THUMBNAIL_IMAGES . $category_image);
        }
    }

    function image_process($categories_image_name, $categories_image_name_process)
    {
        include(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'categories_info_images.php');
        include(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'categories_thumbnail_images.php');
    }

    //set categories images file rights
    function set_categories_images_file_rights($image_name)
    {
        if ($image_name != '')
        {
            @ chmod(DIR_FS_CATALOG_CATEGORIES_THUMBNAIL_IMAGES . $image_name, 0644);
            @ chmod(DIR_FS_CATALOG_CATEGORIES_INFO_IMAGES . $image_name, 0644);
        }
    }
}

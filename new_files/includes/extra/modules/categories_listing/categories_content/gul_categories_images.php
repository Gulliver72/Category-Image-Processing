<?php
if (defined('MODULE_GUL_CATEGORIES_IMAGE_RESIZE_STATUS') && MODULE_GUL_CATEGORIES_IMAGE_RESIZE_STATUS == 'true')
{

	  $image = str_replace('info_images', 'thumbnail_images', $image);
	
	  if (is_file(DIR_FS_CATALOG . $image))
    {
		    $categories_content[$rows]['CATEGORIES_IMAGE'] = DIR_WS_BASE . $image;
	  }
}

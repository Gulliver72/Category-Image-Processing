<?php
/* --------------------------------------------------------------
   $Id: product_info_images.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

if (!isset($categories_image_name_process))
{
    $categories_image_name_process = $categories_image_name;
}

$a = new image_manipulation(DIR_FS_CATALOG_CATEGORIES_IMAGES . $categories_image_name, CATEGORIES_IMAGE_WIDTH, CATEGORIES_IMAGE_HEIGHT, DIR_FS_CATALOG_CATEGORIES_INFO_IMAGES . $categories_image_name_process, IMAGE_QUALITY, '');

if (defined('CATEGORIES_IMAGE_MERGE') && CATEGORIES_IMAGE_MERGE != '')
{
    $string = str_replace("'", '', CATEGORIES_IMAGE_MERGE);
    $string = str_replace(')', '', $string);
    $string = str_replace('(', DIR_FS_CATALOG_IMAGES, $string);
    $array = explode(',', $string);
    $a->merge($array[0], $array[1], $array[2], $array[3], $array[4]);
}

$a->create();

unset($categories_image_name_process);

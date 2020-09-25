<?php
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

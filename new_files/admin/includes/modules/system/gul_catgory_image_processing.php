<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

use RobinTheHood\ModifiedStdModule\Classes\StdModule;
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

class gul_category_image_processing extends StdModule
{
    public function __construct()
    {
        $this->init('MODULE_GUL_CATEGORY_IMAGE_PROCESSING');
    }

    public function display()
    {
        return $this->displaySaveButton();
    }

    public function process($file)
    {
    }

    public function install()
    {
        parent::install();
    }

    public function remove()
    {
        parent::remove();
    }
}

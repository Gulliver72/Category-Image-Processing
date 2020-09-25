<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

use RobinTheHood\ModifiedStdModule\Classes\StdModule;
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

class gul_categories_image_resize extends StdModule
{
    public function __construct()
    {
        $this->init('MODULE_GUL_CATEGORIES_IMAGE_RESIZE');
        
        //define needed class extensions
        $this->needed_class_extensions = array();
        $this->get_needed_class_extensions();

        if((isset($_GET['module']) && $_GET['module'] == $this->code) && (isset($_GET['set']) && $_GET['set'] == 'system'))
        {
            if(isset($_GET['action']) && $_GET['action'] == 'save')
            {
                foreach ($this->needed_class_extensions as $ext)
                {
                    if (defined($ext['module'] . '_STATUS') && $ext['module'] . '_STATUS' != $this->enabled)
                    {
                        $this->update_class_extension_status($ext['module'] . '_STATUS', $this->enabled);
                    }
                }
            }
        }
    }

    public function display(): array
    {

        return array('text' => '<br />' . '<div><strong>'.MODULE_CATEGORIES_IMAGE_RESIZE_STATUS_INFO.'</strong></div>' . 
			         '<br /><br />' . '<div align="center">' . xtc_button(BUTTON_SAVE) .
			         xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code . ')) . '</div>'
			         );

    }
    
    private function update_class_extension_status($modul, $status)
    {
    
    	$res = xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . (string)$status . "' WHERE configuration_key = '" . $modul . "'");
        
        return $res;
    }

    public function process($file)
    {
    }

    public function install()
    {
        parent::install();
        
	// Einstellungen Kategoriebilder installieren, wenn Modul Imageprocessing Kategoriebilder nicht installiert ist
        if (!defined('CATEGORIES_IMAGE_HEIGHT')) $this->addConfiguration('CATEGORIES_IMAGE_HEIGHT', '300', 4, 40);
        if (!defined('CATEGORIES_IMAGE_WIDTH')) $this->addConfiguration('CATEGORIES_IMAGE_WIDTH', '300', 4, 41);
        if (!defined('CATEGORIES_IMAGE_MERGE')) $this->addConfiguration('CATEGORIES_IMAGE_MERGE', '', 4, 44);
        if (!defined('CATEGORIES_IMAGE_THUMBNAIL_HEIGHT')) $this->addConfiguration('CATEGORIES_IMAGE_THUMBNAIL_HEIGHT', '150', 4, 42);
        if (!defined('CATEGORIES_IMAGE_THUMBNAIL_WIDTH')) $this->addConfiguration('CATEGORIES_IMAGE_THUMBNAIL_WIDTH', '150', 4, 43);
        if (!defined('CATEGORIES_IMAGE_THUMBNAIL_MERGE')) $this->addConfiguration('CATEGORIES_IMAGE_THUMBNAIL_MERGE', '', 4, 45);

        // benötigte Klassenerweiterungsmodule werden mitinstalliert
        $this->set_installed_modules('install');
    }
    
    private function set_installed_modules($action)
    {
    
        switch($action)
        {
            case 'install':
                foreach ($this->needed_class_extensions as $ext)
                {
                    if (!defined($ext['module'] . '_STATUS'))
                    {
                        $this->addConfigurationSelect($ext['module'] . '_STATUS', 'true', 6, 1);
                        $this->addConfiguration($ext['module'] . '_SORT_ORDER', '10', 6, 2);

                        $module_installed = (!defined('MODULE_' . $ext['class'] . '_INSTALLED') ? $ext['file'] : MODULE_MAIN_INSTALLED . ';' . $ext['file']);
                        xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '" . $module_installed . "', last_modified = now() where configuration_key = 'MODULE_" . $ext['class'] . "_INSTALLED'");
                    } else {
                        $this->update_class_extension_status($ext['module'] . '_STATUS', 'true');
                    }
                }
                break;
            case 'deinstall':
                foreach ($this->needed_class_extensions as $ext)
                {
            	    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '" . $ext['module'] . "'_%'");
                    
                    $module_installed_string = 'MODULE_' . $ext['class'] . '_INSTALLED';
                    $module_installed = str_replace($ext['file'], '', $module_installed_string);
                    $module_installed = trim($module_installed, ';');
                    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $module_installed . "', last_modified = now() where configuration_key = '" . $module_installed_string . "'");

                }
                break;
        }
    }

    private function get_needed_class_extensions() {
    
        $this->needed_class_extensions[] = array('class' => 'MAIN', 'module' => 'MODULE_MAIN_CATIMAGE', 'file' => 'catImage.php');    
        $this->needed_class_extensions[] = array('class' => 'CATEGORIES', 'module' => 'MODULE_CATEGORIES_CATIMAGERESIZE', 'file' => 'catImageResize.php'); 
    }

    public function remove()
    {
        parent::remove();
        
        if (!defined('MODULE_GUL_CATEGORY_IMAGE_PROCESSING_STATUS'))
        {
            $this->deleteConfiguration('CATEGORIES_IMAGE_HEIGHT');
            $this->deleteConfiguration('CATEGORIES_IMAGE_WIDTH');
            $this->deleteConfiguration('CATEGORIES_IMAGE_MERGE');
            $this->deleteConfiguration('CATEGORIES_IMAGE_THUMBNAIL_HEIGHT');
            $this->deleteConfiguration('CATEGORIES_IMAGE_THUMBNAIL_WIDTH');
            $this->deleteConfiguration('CATEGORIES_IMAGE_THUMBNAIL_MERGE');
	}

	// Klassenerweiterungsmodul wird zeitgleich deinstalliert
        $this->set_installed_modules('deinstall');
    }
}

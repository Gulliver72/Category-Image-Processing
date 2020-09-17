<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

use RobinTheHood\ModifiedStdModule\Classes\StdModule;
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

class gul_category_image_processing extends StdModule
{
    public function __construct()
    {
        $this->init('MODULE_GUL_CATEGORY_IMAGE_PROCESSING');
        
        $this->properties = array();
        $this->files = array();

        $this->logfile = DIR_FS_CATALOG . 'log/image_processing_*.log';

        //define used get parameters
        $this->get_params = array();
        //define used post parameters
        $this->post_params = array();

        $this->properties['form_edit'] = xtc_draw_form('modules', FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code . '&action=custom', 'post', 'id="form_image_processing"');
    }

    public function display() {

        //Array für max. Bilder pro Seitenreload
        $max_array = array (array ('id' => '1', 'text' => '1'));
        $max_array[] = array ('id' => '5', 'text' => '5');
        $max_array[] = array ('id' => '10', 'text' => '10');
        $max_array[] = array ('id' => '15', 'text' => '15');
        $max_array[] = array ('id' => '20', 'text' => '20');
        $max_array[] = array ('id' => '50', 'text' => '50');

        $this->get_images_files(DIR_FS_CATALOG_CATEGORIES_IMAGES, 1, 1);

        require (DIR_WS_INCLUDES . 'javascript/jquery.image_processing.js.php');

        $ajax_img = '<img src="images/loading.gif" class="ajax_loading"> ';

        return array('text' => xtc_draw_hidden_field('process', 'module_processing_do') .
                               xtc_draw_hidden_field('ajax_url', xtc_href_link($this->module_filename, 'set=' . $_GET['set'] . '&module='.$this->code). '&action=custom') .
                               xtc_draw_hidden_field('ajax', '1') .
                               xtc_draw_hidden_field('total', $this->max_files).
                               xtc_draw_hidden_field('start', '0') .
                               IMAGE_EXPORT_TYPE . '<br />' .
                               IMAGE_EXPORT . '<br />' .
                               '<br />' . sprintf(IMAGE_COUNT_INFO, basename(DIR_FS_CATALOG_CATEGORIES_IMAGES), $this->max_files) . '['.$this->formatBytes($this->data_volume).']' . '<br />' .
                               '<br />' . xtc_draw_pull_down_menu('max_datasets', $max_array, '5') . ' ' . TEXT_MAX_IMAGES . '<br />' .
                               '<br />' . xtc_draw_checkbox_field('only_missing_images', '1', false, '', 'class="only_missing_images"') . ' ' . TEXT_ONLY_MISSING_IMAGES . '<br />' .
                               '<br />' . xtc_draw_checkbox_field('lower_file_ext', '1', false, '', 'class="lower_file_ext"') . ' ' . TEXT_LOWER_FILE_EXT . '<br />' .
                               '<br />' . xtc_draw_checkbox_field('logging', '1', false, '', 'class="logfile"') . ' ' . TEXT_LOGFILE . '<br />' .
                               '<br />' . xtc_button(BUTTON_START) . '&nbsp;' .
                               xtc_button_link(BUTTON_CANCEL, xtc_href_link($this->module_filename, 'set=' . $_GET['set'] . '&module=' . $this->code)) .

                               '<div class="ajax_responce" style="margin-bottom:15px;"><hr>' .
                               '<div class="ajax_imgname"></div>' .
                               sprintf(MODULE_STEP_READY_STYLE_TEXT, $ajax_img . IMAGE_STEP_INFO . '<span class="ajax_count"></span> / ' . (int)$this->max_files . '<span class="ajax_ready_info">' . IMAGE_STEP_INFO_READY . '<span>') .
                               '<div class="process_wrapper">
                                <div class="process_inner_wrapper">
                                  <div id="show_image_process" style="width:' . 0 . '%;"></div>
                                </div>
                               </div>
                               <div class="ajax_btn_back">' . sprintf(MODULE_STEP_READY_STYLE_BACK,xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code))) . '</div>
                             </div>'
                   );

    }
    
    public function image_processing_do()
    {

        include ('includes/classes/' . FILENAME_IMAGEMANIPULATOR);

        $offset = (int)$_POST['start'];
        $step = (int)$_POST['max_datasets'];
        $count = isset($_POST['count']) ? (int)$_POST['count'] : 0;
        $limit = $offset + $step;

        $rData = array();

        $rData['file_time'] = isset($_POST['file_time']) ? $_POST['file_time'] : date("Y-m-d-His");

        $this->logfile = str_replace('*', $rData['file_time'], $this->logfile);

        @ini_set('memory_limit','256M');
        @xtc_set_time_limit(0);

        $this->get_images_files(DIR_FS_CATALOG_CATEGORIES_IMAGES, $offset, $limit);
        $files = $this->files;

        $ext_search = array('.GIF','.JPG','.JPEG','.PNG');
        $ext_replace = array('.gif','.jpg','.jpeg','.png');

        for ($i = $offset; $i < $limit; $i++)
        {
            if ($i >= $this->max_files)
            {
                $rData['start'] = $limit;
                $rData['count'] = $count;
                return $rData; // step is done
            }
            $categories_image_name = $files[$i]['text'];
            $categories_image_name_process = (isset($_GET['lower_file_ext']) && $_GET['lower_file_ext'] == 1) ? str_replace($ext_search, $ext_replace, $files[$i]['text']) : $files[$i]['text'];

            $rData['imgname'] = encode_htmlentities($categories_image_name_process);

            if (isset($_POST['logging']) && $_POST['logging'] == 1)
            {
                $handle = fopen($this->logfile, "a");
                fwrite($handle, $categories_image_name . '|read' . "\n");
                fclose($handle);
            }

            if (isset($_POST['only_missing_images']) && $_POST['only_missing_images'] == 1)
            {
                $flag = false;
                
                if (!is_file(DIR_FS_CATALOG_CATEGORIES_THUMBNAIL_IMAGES . $categories_image_name_process))
                {
                    require(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'categories_thumbnail_images.php');
                    $flag = true;
                }
                
                if (!is_file(DIR_FS_CATALOG_CATEGORIES_INFO_IMAGES . $categories_image_name_process))
                {
                    require(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'categories_info_images.php');
                    $flag = true;
                }
                
                if ($flag)
                {
                    $count += 1;
                    
                    if (isset($_POST['logging']) && $_POST['logging'] == 1)
                    {
                        $handle = fopen($this->logfile, "a");
                        fwrite($handle, $rData['imgname'] . '|process' . "\n");
                        fclose($handle);
                    }
                }
            } else {
                require(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'categories_thumbnail_images.php');
                require(DIR_FS_ADMIN . DIR_WS_INCLUDES . 'categories_info_images.php');
                
                $count += 1;
                
                if (isset($_POST['logging']) && $_POST['logging'] == 1)
                {
                    $handle = fopen($this->logfile, "a");
                    fwrite($handle, $rData['imgname'] . '|process' . "\n");
                    fclose($handle);
                }
            }
        }

        $rData['start'] = $limit;
        $rData['count'] = $count;
        
        return $rData;
    }

    public function process($file)
    {
    }

    public function install()
    {
        parent::install();
        
        $this->addConfigurationSelect('CATEGORIES_IMAGE_HEIGHT', '300', 4, 40);
        $this->addConfigurationSelect('CATEGORIES_IMAGE_WIDTH', '300', 4, 41);
        $this->addConfigurationSelect('CATEGORIES_IMAGE_MERGE', '', 4, 44);
        $this->addConfigurationSelect('CATEGORIES_IMAGE_THUMBNAIL_HEIGHT', '150', 4, 42);
        $this->addConfigurationSelect('CATEGORIES_IMAGE_THUMBNAIL_WIDTH', '150', 4, 43);
        $this->addConfigurationSelect('CATEGORIES_IMAGE_THUMBNAIL_MERGE', '', 4, 45);
    }
    
    public function custom()
    {
        $rData = $this->image_processing_do();
        $json = array_merge($_POST,$rData);
        echo json_encode($json);
        exit();
    }

    public function get_images_files($filedir,$offset=1,$limit=1)
    {
        $ext_array = array('gif','jpg','jpeg','png'); //Gültige Dateiendungen
        $files = array();
        $this->data_volume = 0;
        
        if ($dir = opendir($filedir)) {
            $max_files = 0;
            
            while  ($file = readdir($dir))
            {
                $tmp = explode('.', $file);
                
                if(is_array($tmp))
                {
                    $ext = strtolower($tmp[count($tmp)-1]);
                    
                    if (is_file($filedir . $file) && in_array($ext, $ext_array) )
                    {
                        if ($max_files >= $offset && $max_files < $limit)
                        {
                            $files[$max_files]= array('id' => $file,
                                                      'text' =>$file
                                                     );
                        }
                        
                        $this->data_volume += filesize($filedir . $file);
                        $max_files ++;
                    }
                }
            }
            closedir($dir);
        }
        $this->max_files = $max_files;
        $this->files = $files;
    }

    public function remove()
    {
        parent::remove();
        
        if (!defined('MODULE_CATEGORIES_IMAGE_RESIZE_STATUS')) {
            $this->deleteConfiguration('CATEGORIES_IMAGE_HEIGHT');
            $this->deleteConfiguration('CATEGORIES_IMAGE_WIDTH');
            $this->deleteConfiguration('CATEGORIES_IMAGE_MERGE');
            $this->deleteConfiguration('CATEGORIES_IMAGE_THUMBNAIL_HEIGHT');
            $this->deleteConfiguration('CATEGORIES_IMAGE_THUMBNAIL_WIDTH');
            $this->deleteConfiguration('CATEGORIES_IMAGE_THUMBNAIL_MERGE');
        }
    }
    
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

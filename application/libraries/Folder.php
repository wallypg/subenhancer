<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Folder
{
    public $obj;
    public $folder;

    function __construct()
    {
        $this->obj =& get_instance();
    }

    function setFolder($folder)
    {
        $this->folder = $folder;
    }

    function view($view, $data=null)
    {   
        $view = $this->folder . '/' . $view;
        // $output = $this->obj->load->view($this->layout, $loadedData, true);
        $this->obj->load->view($view, $data);
    }
}

?>
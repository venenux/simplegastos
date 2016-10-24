<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Output extends CI_Output {

    function nocache()
    {
        $this->set_header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        $this->set_header('Last-Modified: '.gmdate("D, d M Y H:i:s") . ' GMT');
        $this->set_header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0',TRUE);
        $this->set_header('Cache-Control: post-check=0, pre-check=0', TRUE);
        $this->set_header('Pragma: no-cache',TRUE);
    }

}

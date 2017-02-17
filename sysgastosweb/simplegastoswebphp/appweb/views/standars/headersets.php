<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

		$giflogo = base_url() . APPPATH . 'media/01.gif';

		$pathcss = base_url() . APPPATH . 'styles/'; $typcs='text/css';
		$pathjsc = base_url() . APPPATH . 'scripts/'; $typjs='text/javascript';

		$metaline1 = array('name' => 'description', 'content' => 'Sistema Repositorio de Catalago');
		$metaline2 = array('name' => 'keywords', 'content' => 'productos, administracion, catalogo, sistemas');
		$metaline3 = array('name' => 'Content-type', 'content' => 'text/html; charset='.config_item('charset'), 'type' => 'equiv');
		$metaline4 = array('name' => 'Cache-Control', 'content' => 'no-cache, no-store, must-revalidate, max-age=0, post-check=0, pre-check=0', 'type' => 'equiv');
		$metaline5 = array('name' => 'Last-Modified', 'content' => gmdate("D, d M Y H:i:s") . ' GMT', 'type' => 'equiv');
		$metaline6 = array('name' => 'pragma', 'content' => 'no-cache', 'type' => 'equiv');
		$metalines = array('name' => 'Content-Security-Policy', 'content' => '');

		$linkdefcss = array('type'=>$typcs,'rel'=>'stylesheet','href' => $pathcss.'defaultstyle.css?'.time());
		$linkappcss = array('type'=>$typcs,'rel'=>'stylesheet','href' => $pathcss.'bootstrap.css?'.time());
		$linkde2css = array('type'=>$typcs,'rel'=>'stylesheet','href' => $pathcss.'catalogo_defaultstyle.css');
		$linktabcss = array('type'=>$typcs,'rel'=>'stylesheet','href' => $pathcss.'catalogo_tabbertstyle.css');

		$linkbrownavdecsjs = array('type'=>$typjs,'src' => $pathjsc.'brownavdec.js?'.time());
		$linkvalidaformsjs = array('type'=>$typjs,'src' => $pathjsc.'valida.js?'.time());
		$linktabberdetaljs = array('type'=>$typjs,'src' => $pathjsc.'tabber.js?'.time());
		$linkdatepickerugl = array('type'=>$typjs,'src' => $pathjsc.'datetimepicker.js?'.time());

		$meta = array( $metaline1, $metaline2, $metaline3, $metaline4, $metaline5, $metaline6, $metalines );
?>

<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->load->helper('html');
	echo doctype('xhtml1-trans'), PHP_EOL,'<html xmlns="http://www.w3.org/1999/xhtml">', PHP_EOL;
	echo '<head>', PHP_EOL;
	include FCPATH.APPPATH.config_item('defsview').'/headersets.php';
		echo meta($meta);
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		echo link_tag($linkdefcss);
		//echo link_tag($linkde2css);
		echo link_tag($linkappcss);
		echo script_tag($linkbrownavdecsjs);
		echo script_tag($linkvalidaformsjs);
		//echo script_tag($linktabberdetaljs);
		echo script_tag($linkdatepickerugl);
		//if( isset($output) )
		//{
			//foreach($css_files as $file)
			//{
				//echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
			//foreach($js_files as $file)
			//{	echo '<script src="'.$file.'"></script>';	}
		//}
	echo '</head>', PHP_EOL;
	?>
	<body onload = 'checkAvailable()' >
		<div class="menu ">
			<center>
				<!--<img src="<?php echo $giflogo ?>" alt="Logo VNX Codeigniter" width="880" height="24" />-->
				<?=$menu.PHP_EOL?>
			</center>
		</div>
	<center>

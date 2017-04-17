<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->load->helper('html');
	echo doctype('xhtml1-trans'), PHP_EOL,'<html xmlns="http://www.w3.org/1999/xhtml">', PHP_EOL;
	echo '<head>', PHP_EOL;
	include FCPATH.APPPATH.config_item('defsview').'/headersets.php';
		echo meta($meta);
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		echo link_tag($linkdefcss);		// link css estilo aparienca defaultstyle.css para body, center y meta
		echo link_tag($linkappcss);		// link css estilo apariencia boostrap para input, tags, botones y tablas
		echo link_tag($linkcombocss); 	// link css para comobo boxes dinamicos
		echo script_tag($linkbrownavdecsjs);
		echo script_tag($linkvalidaformsjs);	// validadores genericos de campos, trata recorer un campo y verificar su valor segun la llamada
		//echo script_tag($linktabberdetaljs);	// comportamiento de pestañas para que al dar click esconda y muestre como si fueran pestañas elementos de lista
		echo script_tag($linkdatepickerugl);	// comportamiento de selector de fechas sin usar jquery, 1005 compatible con cualqueir navegador
	/*	
	 * esta seccion comentada son los css y js de el grocery crud, la logica es que :
	 *  si no esta presente no lo caraga, pero si esta, entonces lo carga y 
	 * escupe el html de un meta tag de link de css y de js, las variables 
	 *  que se usan deben llamarse como las de grocerycrud
	 *  y estas deben ser enviadas por separado desde el controlador despues de invocar render/output
	 */ /*
		$typcs='text/css';
	$pathcssgc = base_url() .'assets/grocery_crud/themes/datatables/css/datatables.css';
	$linkdefcssgc = array('type'=>$typcs,'rel'=>'stylesheet','href' => $pathcssgc);
	echo link_tag($linkdefcssgc);
	$pathcssgc = base_url() .'assets/grocery_crud/themes/datatables/css/demo_table_jui.css';
	$linkdefcssgc = array('type'=>$typcs,'rel'=>'stylesheet','href' => $pathcssgc);
	echo link_tag($linkdefcssgc);
	$pathcssgc = base_url() .'assets/grocery_crud/themes/datatables/css/jquery.dataTables.css';
	$linkdefcssgc = array('type'=>$typcs,'rel'=>'stylesheet','href' => $pathcssgc);
	echo link_tag($linkdefcssgc);

*/
	
		
	echo '</head>', PHP_EOL;
	?>
	<body onload = 'checkAvailable()' >
		<div class="menu ">
			<center>
				<?php echo $menu.PHP_EOL; ?>
			</center>
		</div>
	<center>

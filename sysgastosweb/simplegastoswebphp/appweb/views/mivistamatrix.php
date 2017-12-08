	<h1>Matriz de Gastos Tiendas X Categorías</h1>
	<?php

	/* ********* ini valores predeterminados ******************** */
	$htmlformaattributos = array('name'=>'formulariomatrix','class'=>'formularios','style'=>'none','onSubmit'=>'return validageneric(this);');

	if( !isset($fechainimatrix) )	// valor inicial para escoger la fecha deseada de la matris (cualquier dia)
	$fechainimatrix=date('Ymd');$idfechainimatrix='fechainimatrix';$inputfechainiattr = array('name'=>$idfechainimatrix,'id'=>$idfechainimatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechainimatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechainimatrix, $$idfechainimatrix));

	if( !isset($fechafinmatrix) )	// valor inicial para escoger la fecha deseada de la matris (cualquier dia)
	$fechafinmatrix=date("Ymd", strtotime("+1 week"));$idfechafinmatrix='fechafinmatrix';$inputfechafinattr = array('name'=>$idfechafinmatrix,'id'=>$idfechafinmatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechafinmatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechafinmatrix, $$idfechafinmatrix));

	if( !isset($seccionpagina) )		// si no dice por defecto muestra el formulario, seccion dice a que parte muestra de la vista
	$seccionpagina = 'seccionformulario';
	/* ********* fin valores predeterminados ******************** */

	/* ********* ini seccion del formulario filtrara ******************** */
	$jspickathingjs='<script type="text/javascript" src="' . base_url() . APPPATH . 'scripts/'.'pickathing.js"></script>';
		$jscategorialis = '<script>var selectjscategorialis = new Pickathing(\'list_categoria\', true);</script>';
		$jssubcategorialis = '<script>var selectjssubcategorialis = new Pickathing(\'list_subcategoria\', true);</script>';
		$jsentidadlis = '<script>var selectjsentidadlis = new Pickathing(\'list_entidad\', true);</script>';
	if ($seccionpagina == 'seccionfiltrarmatrix')
	{
	
		//echo form_fieldset('Ingrese datos solo si desea filtrar la matrix',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('/mimatrixcontroller/secciontablamatrix/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Periodo entre',form_input($inputfechainiattr),' y hasta ',form_input($inputfechafinattr), form_submit('vermatrix', 'Ver la matrix (click)', 'class="btn btn-primary btn-large b10"'), '', '' ) ;
			$this->table->add_row('Categoria - Concepto:', form_multiselect('cod_subcategoria', $list_categoria).br().PHP_EOL,'','');
			$this->table->add_row('Centro de Costo:', form_dropdown('cod_entidad', $list_entidad).'(automatico)'.br().PHP_EOL ,'','');
		echo $this->table->generate();
		//generar el dropdown con las categorias
		echo form_close() . PHP_EOL;
		
		
		//echo form_fieldset_close() . PHP_EOL;
		//echo br().PHP_EOL;
	}
	/* ********* fin seccion de formulario que filtrara ******************** */

	/* ********* ini seccion de pagina pinta matrix ******************** */
	if ($seccionpagina == 'secciontablamatrix')
	{
		// mostrar la tabla tiendas x categorias, esto es construido en el controlador y enviado preformateado ya html
	//	echo "Periodo mostrado es entre : " . $fechainimatrix . " y el " . $fechafinmatrix . br() . PHP_EOL;
		foreach($css_files as $file)
		{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		foreach($js_files as $file)
		{	echo '<script src="'.$file.'"></script>';	}
		echo br(). PHP_EOL;
		echo $output. PHP_EOL;
		//echo $htmlquepintamatrix . PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */

	?>

	<h1>Matriz de Gastos Tiendas X Categorías</h1>
	<?php

	/* ********* ini valores predeterminados ******************** */
	$htmlformaattributos = array('name'=>'formulariomatrix','class'=>'formularios','style'=>'none','onSubmit'=>'return validageneric(this);');
	$usuariocodgernow = $this->session->userdata('cod_entidad');

	// cargar las ubicciones/centro costo o aseguramiento que exista
	if( !isset($list_entidad) ) $list_entidad = array($usuariocodgernow => 'Los propios gastos');
	// cargar las categorias y subcategorias o aseguramiento que exista
	if( !isset($list_categoria) ) $list_categoria = array('' => 'N/A');

	if( !isset($fechainimatrix) )	// valor inicial para escoger la fecha deseada de la matris (cualquier dia)
	$fechainimatrix=date('Ymd');$idfechainimatrix='fechainimatrix';$inputfechainiattr = array('name'=>$idfechainimatrix,'id'=>$idfechainimatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechainimatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechainimatrix, $$idfechainimatrix));

	if( !isset($fechafinmatrix) )	// valor inicial para escoger la fecha deseada de la matris (cualquier dia)
	$fechafinmatrix=date("Ymd", strtotime("+1 week"));$idfechafinmatrix='fechafinmatrix';$inputfechafinattr = array('name'=>$idfechafinmatrix,'id'=>$idfechafinmatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechafinmatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechafinmatrix, $$idfechafinmatrix));

	if( !isset($seccionpagina) )		// si no dice por defecto muestra el formulario, seccion dice a que parte muestra de la vista
	$seccionpagina = 'seccionformulario';
	/* ********* fin valores predeterminados ******************** */

	/* ********* ini seccion del formulario filtrara ******************** */
	if ($seccionpagina == 'seccionfiltrarmatrix')
	{
		echo form_fieldset('Por favor seleccionar fecha, categoria y entidad',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('/mimatrixcontroller/secciontablamatrix/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Periodo entre',form_input($inputfechainiattr) . ' y hasta ' . form_input($inputfechafinattr)) ;
			$this->table->add_row('Por Categoria:', form_multiselect('list_categoria[]', $list_categoria,null,'id="list_categoria"').PHP_EOL);
			$this->table->add_row('Centro de Costo:', form_multiselect('list_entidad[]', $list_entidad, null,'id="list_entidad"').PHP_EOL );
		echo $this->table->generate();
		echo form_submit('vermatrix', 'Ver matrix', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		//echo br().PHP_EOL;
	}
	/* ********* fin seccion de formulario que filtrara ******************** */

	/* ********* ini seccion de pagina pinta matrix ******************** */
	if ($seccionpagina == 'secciontablamatrix')
	{
		// mostrar la tabla tiendas x categorias, esto es construido en el controlador y enviado preformateado ya html
		foreach($css_files as $file)
		{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		foreach($js_files as $file)
		{	echo '<script src="'.$file.'"></script>';	}
		echo $output. PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */

	?>

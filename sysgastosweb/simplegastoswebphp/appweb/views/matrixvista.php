	<h1>Matrix de Gastos</h1>
	<?php

	/* ********* ini valores predeterminados ******************** */
	if( !isset($htmlformaattributos) )
	$htmlformaattributos = array('name'=>'formulariomatrix','class'=>'formularios','onSubmit'=>'return validageneric(this);');
	if( !isset($fechainimatrix) )
	$fechainimatrix=date('Ym01');$idfechainimatrix='fechainimatrix';$inputfechaini = array('name'=>$idfechainimatrix,'id'=>$idfechainimatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechainimatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechainimatrix, $$idfechainimatrix));
	if( !isset($fechafinmatrix) )
	$fechafinmatrix=date('Ymt');$idfechafinmatrix='fechafinmatrix';$inputfechafin = array('name'=>$idfechafinmatrix,'id'=>$idfechafinmatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechafinmatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechafinmatrix, $$idfechafinmatrix));
	if( !isset($seccionpagina) ) 
	$seccionpagina = 'seccionmatrixpedirla';
	if( !isset($list_entidad) ) 
	$list_entidad = array('cod_entidad' => '');
	if( !isset($list_categoria) ) 
	$list_categoria = array('cod_categoria' => '');
	if( !isset($list_subcategoria) ) 
	$list_subcategoria = array('cod_subcategoria' => '');
	/* ********* fin valores predeterminados ******************** */

	/* ********* ini seccion de pagina formulario ******************** */
	if ($seccionpagina == 'seccionmatrixpedirla')
	{
		$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
		echo form_fieldset('Ingrese datos solo si desea filtrar la matrix',array('class'=>'containerin')) . PHP_EOL;
		echo form_open_multipart('cargargastoex/registrargasto/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Rango:',form_input($inputfechaini). ' al '.form_input($inputfechafin).br().PHP_EOL );
			$this->table->add_row('Categoria', form_dropdown('cod_categoria', $list_categoria).br().PHP_EOL);
			$this->table->add_row('Centro de Costo:', form_dropdown('cod_entidad', $list_entidad).br().PHP_EOL );
		echo $this->table->generate();
		echo form_submit('vermatrix', 'Ver totales', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */

	/* ********* ini seccion de pagina pinta matrix ******************** */
	$this->load->helper('html');
	if( isset($css_files) )
		foreach($css_files as $file)
		{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
	if( isset($js_files) )
		foreach($js_files as $file)
		{	echo '<script src="'.$file.'"></script>';	}
	if ($seccionpagina == 'seccionmatrixresultado')
	{

		echo form_fieldset('Matrix de reporte de gatos',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo br().PHP_EOL;
		echo 'Usuario actual : '.$userintranet.' ('.$usercorreo.'), Fecha gasto: '.$fechafiltramatrix.'<br>'.PHP_EOL;
		echo br().PHP_EOL;
		echo $htmlquepintamatrix;
		echo form_fieldset_close() . PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */

	?>

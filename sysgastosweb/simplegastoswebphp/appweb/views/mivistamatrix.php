	<h1>Matrix de Gastos Tiendas X Categorías</h1>
	<?php

	/* ********* ini valores predeterminados ******************** */
	$htmlformaattributos = array('name'=>'formulariomatrix','class'=>'formularios','onSubmit'=>'return validageneric(this);');
	if( !isset($fechafiltramatrix) )
	$fechafiltramatrix=date('Ymd');$idfechamatrix='fechafiltramatrix';$inputfechaattr = array('name'=>$idfechamatrix,'id'=>$idfechamatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechamatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechamatrix, $$idfechamatrix));
	if( !isset($seccionpagina) ) 
	$seccionpagina = 'seccionformulario';
	if( !isset($list_entidad) ) 
	$list_entidad = array('cod_entidad' => '');
	if( !isset($list_categoria) ) 
	$list_categoria = array('cod_categoria' => '');
	if( !isset($list_subcategoria) ) 
	$list_subcategoria = array('cod_subcategoria' => '');
	/* ********* fin valores predeterminados ******************** */

	/* ********* ini seccion de pagina formulario ******************** */
	if ($seccionpagina == 'seccionformulario')
	{
		$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
		echo form_fieldset('Ingrese datos solo si desea filtrar la matrix',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('/mimatrixcontroller/secciontablamatrix/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Fecha deseada',form_input($inputfechaattr).br().PHP_EOL);
			$this->table->add_row('Categoria - Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria).br().PHP_EOL);
			$this->table->add_row('Centro de Costo:', form_dropdown('cod_entidad', $list_entidad).'(automatico)'.br().PHP_EOL );
		echo $this->table->generate();
		echo form_submit('vermatrix', 'Ver la matrix', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */
	
	/* ********* ini seccion de pagina pinta matrix ******************** */
	else if ($seccionpagina == 'secciontablamatrix')
	{
		echo form_fieldset('Matrix de reporte de gastos',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo br().PHP_EOL;
		echo 'Usuario actual : '.$userintranet.' ('.$usercorreo.'), Fecha gasto: '.$fechafiltramatrix.'<br>'.PHP_EOL;
		echo br().PHP_EOL;
		echo $htmlquepintamatrix;
		echo form_fieldset_close() . PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */

	?>

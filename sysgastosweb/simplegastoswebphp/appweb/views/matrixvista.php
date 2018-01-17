	<?php

	/* ********* ini valores predeterminados ******************** */
	if( !isset($htmlformaattributos) )
	$htmlformaattributos = array('name'=>'formulariomatrix','class'=>'formularios','onSubmit'=>'return validageneric(this);');
	if( !isset($fechainimatrix) )
	$fechainimatrix=date('Ym01');$idfechainimatrix='fechainimatrix';$inputfechaini = array('name'=>$idfechainimatrix,'id'=>$idfechainimatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechainimatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechainimatrix, $$idfechainimatrix));
	if( !isset($fechafinmatrix) )
	$fechafinmatrix=date('Ymt');$idfechafinmatrix='fechafinmatrix';$inputfechafin = array('name'=>$idfechafinmatrix,'id'=>$idfechafinmatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechafinmatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechafinmatrix, $$idfechafinmatrix));
	if( !isset($seccionpagina) ) 
	$seccionpagina = 'seccionmatrixpedirtotales';
	if( !isset($list_entidad) ) 
	$list_entidad = array('cod_entidad' => '');
	if( !isset($list_categoria) ) 
	$list_categoria = array('cod_categoria' => '');
	if( !isset($list_subcategoria) ) 
	$list_subcategoria = array('cod_subcategoria' => '');
	if( !isset($menusub) ) 
	$menusub = array('Aceso restringido, solo ppresidencia y gerencia, reporte a soportevnz si incorrecto');
	/* ********* fin valores predeterminados ******************** */

	/* ********* ini seccion de pagina index ******************** */
	if ($seccionpagina == 'seccionmatrixindex')
	{
		echo form_fieldset('Sub-modulo de MATRIX DE GASTO para administracion y gerencia',array('class'=>'containerin')) . PHP_EOL;
		$this->table->clear();
		$this->table->set_datatables(FALSE);
			$this->table->add_row($menusub);
		echo $this->table->generate();
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */


	/* ********* ini seccion de pagina formulario ******************** */
	if ($seccionpagina == 'seccionmatrixpedirtotales')
	{
		$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
		$this->table->clear();
		$this->table->set_datatables(FALSE);
			$this->table->add_row($menusub);
		echo $this->table->generate();
		echo form_fieldset('Ingrese datos solo si desea filtrar resultados',array('class'=>'containerin')) . PHP_EOL;
		echo form_open_multipart('matrixcontroler/matrixtotalesfiltrado/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
		$this->table->set_datatables(FALSE);
			$this->table->add_row('Rango:',form_input($inputfechaini). ' al '.form_input($inputfechafin).PHP_EOL );
			$this->table->add_row('Categoria', form_dropdown('cod_categoria', $list_categoria,null,'id="list_categoria"').PHP_EOL);
			$this->table->add_row('Centro de Costo:', form_dropdown('cod_entidad', $list_entidad,null,'id="list_entidad"').PHP_EOL );
			$this->table->add_row('Intranet especifica:',  form_input('sessioncarga','').PHP_EOL);
			$this->table->add_row('Descripcion similar a: ',  form_input('des_concepto','') );
			$this->table->add_row('Mostrar por intranet?: ', form_checkbox('ind_intranet', 'conintranet', FALSE) .PHP_EOL  );
			$this->table->add_row('Mostrar los detalles?: ', form_checkbox('ind_concepto', 'condetalle', TRUE) .PHP_EOL  );
		echo $this->table->generate();
		echo form_submit('vermatrix', 'Ver totales', 'class="btn-primary btn b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */

	/* ********* ini seccion de pagina pinta matrix ******************** */
	//$this->load->helper('html');
	if( isset($css_files) )
		foreach($css_files as $file)
		{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
	if( isset($js_files) )
		foreach($js_files as $file)
		{	echo '<script src="'.$file.'"></script>';	}
	if ($seccionpagina == 'seccionmatrixresultado')
	{
		$this->table->clear();
		$this->table->set_datatables(FALSE);
			$this->table->add_row($menusub);
		echo $this->table->generate();
		echo form_fieldset('Matrix de reporte de gastos totalizados') . PHP_EOL;
		echo 'Filtros : '.$des_concepto.' '.$sessioncarga.', '.$cod_entidad.' ('.$cod_categoria.'), Fecha gasto: '.$fechainimatrix.' al '.$fechafinmatrix.'<br>'.PHP_EOL;
		echo $htmlquepintamatrix;
		echo form_fieldset_close() . PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */

	?>

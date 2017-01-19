	<h1>Matriz de Gastos Tiendas X Categorías</h1>
	<?php
	
	/* ********* ini valores predeterminados ******************** */
	$htmlformaattributos = array('name'=>'formulariomatrix','class'=>'formularios','onSubmit'=>'return validageneric(this);');
	
	if( !isset($fechafiltramatrix) )	// valor inicial para escoger la fecha deseada de la matris (cualquier dia)
	$fechafiltramatrix=date('Ymd');$idfechamatrix='fechafiltramatrix';$inputfechaattr = array('name'=>$idfechamatrix,'id'=>$idfechamatrix, 'onclick'=>'javascript:NewCssCal(\''.$idfechamatrix.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechamatrix, $$idfechamatrix));
	
	if( !isset($seccionpagina) )		// si no dice por defecto muestra el formulario, seccion dice a que parte muestra de la vista
	$seccionpagina = 'seccionformulario';
	/* ********* fin valores predeterminados ******************** */

	/* ********* ini seccion del formulario filtrara ******************** */
	if ($seccionpagina == 'seccionfiltrarmatrix')
	{
		echo form_fieldset('Ingrese datos solo si desea filtrar la matrix',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('/mimatrixcontroller/secciontablamatrix/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Fecha del mes deseado',form_input($inputfechaattr).'(filtrara la matrix en el mes de la fecha que escoja)'.br().PHP_EOL) ;
			/*$this->table->add_row('Categoria - Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria).br().PHP_EOL);
			$this->table->add_row('Centro de Costo:', form_dropdown('cod_entidad', $list_entidad).'(automatico)'.br().PHP_EOL );*/
		echo $this->table->generate();
		echo form_submit('vermatrix', 'Ver la matrix', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	/* ********* fin seccion de formulario que filtrara ******************** */

	/* ********* ini seccion de pagina pinta matrix ******************** */
	else if ($seccionpagina == 'secciontablamatrix')
	{
		$this->table->clear();
		$tablestyle = array( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table display groceryCrudTable dataTable ui default ">' );
		$this->table->add_row(
			'<center>'.anchor('mimatrixcontroller/secciontablamatrix/'.(date("Ym")-1),form_button('admcategorias/admcategorias/add', '<< Mes '.(date("Ym")-1), 'class="btn btn-primary btn-large b10" ')).'</center>'
			,
			'<center>'.anchor('mimatrixcontroller/secciontablamatrix/'.(date("Ym")+1),form_button('admcategorias/admcategorias/list', 'Mes '.(date("Ym")).'>>', 'class="btn btn-primary btn-large b10" ')).'</center>'
		);
		$tablabotonmes = $this->table->generate();

		//info usuarios
		echo 'Usuario actual : '.$userintranet.' ('.$usercorreo.'), Fecha gasto: '.$fechafiltramatrix.'<br>'.PHP_EOL;
		// mostrar la tabla botones
		echo $tablabotonmes;
		// mostrar la tabla tiendas x categorias, esto es construido en el controlador y enviado preformateado ya html
		echo $htmlquepintamatrix . PHP_EOL;
		// si la tabla es larga vuelvo mostrar los botones
		echo $tablabotonmes;
	}
	/* ********* fin seccion de pagina formulario ******************** */
   
	?>

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
		// mostrar la tabla tiendas x categorias, esto es construido en el controlador y enviado preformateado ya html
		echo $htmlquepintamatrix . PHP_EOL;
	}
	/* ********* fin seccion de pagina formulario ******************** */
   
	?>

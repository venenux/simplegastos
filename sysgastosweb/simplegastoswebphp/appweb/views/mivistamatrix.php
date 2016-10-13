	<h1>Matrix de Gastos Tiendas X Categorías</h1>
	<?php
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
// CONVERT(fecha_registro,UNSIGNED) >= ".$fec_registroini."
	/* ********* ini seccion de pagina formulario ******************** */
	if ($seccionpagina == 'seccionfiltrarmatrix')
	{
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
		$this->table->clear();
		$this->table->add_row(
			anchor('admcategorias/admcategorias/add',form_button('admcategorias/admcategorias/add', '<< Mes Anterior', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admcategorias/admcategorias/list',form_button('admcategorias/admcategorias/list', 'Mes Siguiente >>', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonmes = form_fieldset(' Seleccionar Mes',array('class'=>'container_blue containerin')) . PHP_EOL;
		$tablabotonmes .= $this->table->generate();
		
		$tablabotonmes .= form_fieldset_close() . PHP_EOL;

		$this->table->clear();
			$this->table->add_row($tablabotonmes);
			
		$botonesmes = $this->table->generate();
		//info usuarios
		echo 'Usuario actual : '.$userintranet.' ('.$usercorreo.'), Fecha gasto: '.$fechafiltramatrix.'<br>'.PHP_EOL;
		// mostrar la tabla botones
		echo $botonesmes;
		// mostrar la tabla tiendas x categorias
		echo $htmlquepintamatrix . PHP_EOL;
		echo $botonesmes;
		
		
	
	}
	/* ********* fin seccion de pagina formulario ******************** */
   
	?>

	<?php

	$classinput = array('class'=>' form-input-box btn containerin');
	if( !isset($mens) )
		$mens = '<strong>Gastos semanales</strong>';

	//  cargar la session o aseguramiento que exista un objeto session
	if( !isset($this->session) )
		$usuariocodgernow = null;
	else
		$usuariocodgernow = $this->session->userdata('cod_entidad');

	// que parte del formulario y a donde ira a viajar el html
	if( !isset($accionejecutada) )
		$accionejecutada = 'gastosucursalesindex';

	if( !isset( $factura_tipo) )
		 $factura_tipo = array( 'EGRESO' => 'EGRESO', 'CONTRIBUYENTE' => 'CONTRIBUYENTE');

	// cargar las ubicciones/centro costo o aseguramiento que exista
	if( !isset($list_entidad) )
		$list_entidad = array($usuariocodgernow => 'Los propios gastos');

	// cargar las categorias y subcategorias o aseguramiento que exista
	if( !isset($list_categoria) ) $list_categoria = array('' => 'N/A');
	if( !isset($list_subcategoria) ) $list_subcategoria = array('' => 'Sin permiso para cargar');


	// pintar botones de gestion para carga manual ya que las acciones de agregar y ver son customizadas
	$this->table->clear();
	$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">' );
	$this->table->set_template($tmplnewtable);
	$this->table->add_row(
			anchor('cargargastosucursales/gastomanualcargaruno',form_button('cargargastomanual/gastomanualcargaruno/add', 'Registrar Gasto', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('cargargastosucursales/gastosucursalesrevisarlos',form_button('cargargastomanual/gastomanualrevisarlos/list', 'Revisar Gastos', 'class="btn btn-primary btn-large b10" '))
			/*,
			anchor('cargargastomanual/gastomanualfiltrarlos',form_button('cargargastomanual/gastomanualfiltrarlos/veruno', 'Editar Gasto', 'class="btn btn-primary btn-large b10" '))*/
		);
//	$tablabotonsgasto = form_fieldset(,array('class'=>'container_blue containerin')) . PHP_EOL;
	$tablabotonsgasto = $this->table->generate();
//	$tablabotonsgasto .= form_fieldset_close() . PHP_EOL;

	$this->table->clear();
	$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">' );
	$this->table->set_template($tmplnewtable);
	$this->table->add_row($tablabotonsgasto);
	$botonesgestion = $this->table->generate();

	// detectar que mostrar segun lo enviado desde el controlador
	echo 'ESTADO OPERACION: <strong>'.$mens.'</strong>';
	if ($accionejecutada == 'gastosucursalesindex')
	{
		echo br() . PHP_EOL;
		echo form_fieldset('¿Que desea realizar?',array('class'=>'containerin')) . PHP_EOL;
		echo form_hidden('accionejecutada',$accionejecutada).PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	else if ($accionejecutada == 'gastosucursalesrevisarlos')
	{
		echo br().PHP_EOL;
		echo form_fieldset('Cargas y registros de gastos',array('class'=>'containerin ')) . PHP_EOL;
		echo form_hidden('accionejecutada',$accionejecutada).br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		//echo $tabledelfiltrocualesgastos . PHP_EOL;
		if( isset($css_files) )
			foreach($css_files as $file)
			{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		if( isset($js_files) )
			foreach($js_files as $file)
			{	echo '<script src="'.$file.'"></script>';	}
		echo $output;
		echo form_fieldset_close() . PHP_EOL;
	}
	else if ($accionejecutada == 'gastomanualcargaruno')
	{
		$fecha_concepto=date('Ymd');
		$idfecdesde='fecha_concepto';
		$valoresinputfecha = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));

		$htmlformaattributos = array('name'=>'cargargastoucursal','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo form_fieldset('<strong>Ingreso de un Gasto</strong> Ingrese los datos por favor',array('class'=>'containerin')) . PHP_EOL;
		echo form_open_multipart('cargargastosucursales/gastomanualcargarunolisto/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();

		$this->table->set_template(array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">','cell_start' => '<td class="form-field-box odd">', ) );
			$this->table->add_row('Fecha del gasto (3 dias maximo):',form_input($valoresinputfecha).'(no mas de 3 dias atras)'.br().PHP_EOL, $classinput);
			$this->table->add_row('De quien es el gasto:', form_dropdown('cod_entidad', $list_entidad, $usuariocodgernow, $classinput ));
			$this->table->add_row('Categoria y SubCategoria:', form_dropdown('cod_subcategoria', $list_subcategoria, '', $classinput).br().PHP_EOL);
			$this->table->add_row('Monto (punto para decimal, sin coma)', form_input('mon_registro', '0.00', $classinput).br().PHP_EOL);
			$this->table->add_row('Concepto o Detalle:', form_input('des_concepto', '', $classinput).br().PHP_EOL);
			$this->table->add_row('Factura tipo:', form_dropdown('factura_tipo', $list_factura_tipo , 'CONTRIBUYENTE', $classinput));
			$this->table->add_row('Factura Numero (contribuyente):', form_input('factura_num', '', $classinput).br().PHP_EOL);
			$this->table->add_row('Factura RIF (contribuyente):', form_input('factura_rif', '', $classinput).br().PHP_EOL);
			$this->table->add_row('Factura escaneada? :', form_upload(array('name'  => 'factura_bin', 'id'=>'factura_bin')).br().PHP_EOL );
		echo $this->table->generate().br().PHP_EOL;
		echo form_hidden('tipo_concepto', 'NORMAL'); // la carga de una sucursal es normal, la realizada por departamentos es administrativa
		echo form_submit('cargargastosenviar', 'Registrar gasto', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
	}
	else if ($accionejecutada == 'gastomanualeditaruno')
	{
		if( !isset( $fecha_concepto) )	$fecha_concepto=date('Ymd');
		$idfecdesde='fecha_concepto';
		$valoresinputfecha = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));

		$htmlformaattributos = array('name'=>'cargargastoucursal','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo form_fieldset('<strong>Ediccion de un Gasto</strong> Ingrese los datos por favor',array('class'=>'containerin')) . PHP_EOL;
		echo form_open_multipart('cargargastosucursales/gastomanualeditarunolisto/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();

		$this->table->set_template(array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">','cell_start' => '<td class="form-field-box odd">', ) );
			$this->table->add_row('Fecha del gasto (3 dias maximo):',form_input($valoresinputfecha).'(no mas de 3 dias atras)'.br().PHP_EOL, $classinput);
			$this->table->add_row('De quien es el gasto:', form_dropdown('cod_entidad', $list_entidad, $usuariocodgernow, $classinput ));
			$this->table->add_row('Categoria y SubCategoria:', form_dropdown('cod_subcategoria', $list_subcategoria, $cod_subcategoria, $classinput).br().PHP_EOL);
			$this->table->add_row('Monto (punto para decimal, sin coma)', form_input('mon_registro',$mon_registro, $classinput).br().PHP_EOL);
			$this->table->add_row('Concepto o Detalle:', form_input('des_concepto',$des_concepto, $classinput).br().PHP_EOL);
			$this->table->add_row('Factura tipo:', form_dropdown('factura_tipo', $list_factura_tipo , $factura_tipo, $classinput));
			$this->table->add_row('Factura Numero (contribuyente):', form_input('factura_num', $factura_num, $classinput).br().PHP_EOL);
			$this->table->add_row('Factura RIF (contribuyente):', form_input('factura_rif', $factura_rif, $classinput).br().PHP_EOL);
			$this->table->add_row('Factura escaneada:', form_upload(array('name'  => 'factura_binX', 'id'=>'factura_binX') ).br().'EN MODO EDICCION SOLO SUBA ADJUNTO SI DESEA CAMBIAR EL ARCHIVO'.br().' SINO DEJELO SIN ALTERAR ('.$factura_bin.')'.PHP_EOL );
		echo $this->table->generate().br().PHP_EOL;
		echo form_hidden('factura_bin', $factura_bin); // no se puede resubir archivos, entonces comparo si cambio el nombre y tomo el subido nuevo, sino esta variable es el nombre viejo inalterado
		echo form_hidden('cod_registro', $cod_registro); // la carga de una sucursal es normal, la realizada por departamentos es administrativa
		echo form_hidden('tipo_concepto', 'NORMAL'); // la carga de una sucursal es normal, la realizada por departamentos es administrativa
		echo form_submit('cargargastosenviar', 'Modificar este gasto', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
	}
	else if ($accionejecutada == 'gastomanualfiltrardouno')
	{
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo form_fieldset('Gasto registrado adjudicado codigo: '.$cod_registro,array('class'=>'containerin ')) . PHP_EOL;
			if( isset($css_files) )
			foreach($css_files as $file)
			{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		if( isset($js_files) )
			foreach($js_files as $file)
			{	echo '<script src="'.$file.'"></script>';	}
		echo $htmltablacargasregistros; // $output
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
	}
	echo 'ESTADO OPERACION:<strong>'.$mens.'</strong>';
	?>
	</div>
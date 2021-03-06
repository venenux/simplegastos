	<?php

	$classinput = array('class'=>' form-input-box containerin');
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

	if( !isset( $list_factura_tipo) )
		 $list_factura_tipo = array( 'EGRESO' => 'EGRESO', 'CONTRIBUYENTE' => 'CONTRIBUYENTE');

	if( !isset( $list_tipo_concepto) )
		 $list_tipo_concepto = array( 'SUCURSAL' => 'SUCURSAL');

	// cargar las ubicciones/centro costo o aseguramiento que exista
	if( !isset($list_entidad) )
		$list_entidad = array($usuariocodgernow => 'Los propios gastos');

	// cargar las categorias y subcategorias o aseguramiento que exista
	if( !isset($list_categoria) ) $list_categoria = array('' => 'N/A');
	if( !isset($list_subcategoria) ) $list_subcategoria = array('' => 'Sin permiso para cargar');
	if( !isset($cod_subcategoria) ) $cod_subcategoria = '';

	$fec_registroini='';
	$idfecdesde='fec_registroini';
	$valoresinputfecha1ini = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));
	$fec_registrofin='';
	$idfechasta='fec_registrofin';
	$valoresinputfecha1fin = array('name'=>$idfechasta,'id'=>$idfechasta, 'onclick'=>'javascript:NewCssCal(\''.$idfechasta.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechasta, $$idfechasta));

	$fec_conceptoini='';
	$idfecdesde='fec_conceptoini';
	$valoresinputfecha2ini = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));
	$fec_conceptofin='';
	$idfechasta='fec_conceptofin';
	$valoresinputfecha2fin = array('name'=>$idfechasta,'id'=>$idfechasta, 'onclick'=>'javascript:NewCssCal(\''.$idfechasta.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechasta, $$idfechasta));

	// pintar botones de gestion para carga manual ya que las acciones de agregar y ver son customizadas
	if( !isset($botongestion0) ) $botongestion0 = '';
	$botongestion1 = anchor('cargargastosucursalesadm/gastomanualcargaruno',form_button('cargargastomanual/gastomanualcargaruno/add', 'Cargar Gasto interno', 'class="btn-primary btn" '));
	$botongestion2 = anchor('cargargastosucursalesadm/gastosucursalesrevisarlos',form_button('cargargastomanual/gastomanualrevisarlos/list', 'Revisar Ultimos', 'class="btn-primary btn" '));
	$botongestion3 = anchor('cargargastosucursalesadm/gastomanualfiltrarlos',form_button('cargargastomanual/gastomanualfiltrarlos/veruno', 'Filtrar Gasto interno', 'class="btn-primary btn" '));
	$this->table->clear();
	$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">' );
	$this->table->set_template($tmplnewtable);
	$this->table->add_row($botongestion0,$botongestion1,$botongestion2,$botongestion3);
	$botonesgestion = $this->table->generate();

	// detectar que mostrar segun lo enviado desde el controlador
		echo '<div style="color:red;background-color:red">ESTADO OPERACION: <strong style="background-color:red;color:white;">'.$mens.'</strong></div>';
	if ($accionejecutada == 'gastosucursalesindex')
	{
		echo br() . PHP_EOL;
		echo form_fieldset('¿Que desea realizar?',array('class'=>'containerin')) . PHP_EOL;
		echo form_hidden('accionejecutada',$accionejecutada).PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	else if ($accionejecutada == 'gastomanualfiltrarlos')
	{
		echo br().PHP_EOL;
		echo form_fieldset('Puede dejar campos en blanco para filtrar <strong>'. date("Y/M") .' y '.date("Y/M", strtotime('-1 month')).'</strong> SOLO LOS ULTIMOS 500 GASTOS!!! y use punto para decimal, sin comas ',array('class'=>'containerin ')) . PHP_EOL;
		echo $botonesgestion . PHP_EOL;
				$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_open_multipart('cargargastosucursalesadm/gastosucursalesrevisarlos', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Registrado en el sistema el/entre:',form_input($valoresinputfecha1ini).PHP_EOL.' y '.form_input($valoresinputfecha1fin).br().PHP_EOL);
			$this->table->add_row('Fecha de factura o egreso el/entre:',form_input($valoresinputfecha2ini).PHP_EOL.' y '.form_input($valoresinputfecha2fin).br().PHP_EOL);
			$this->table->add_row('Por Categoria/Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria,null,'id="list_subcategoria"').br().PHP_EOL);
			$this->table->add_row('Por Centro de Costo:', form_dropdown('cod_entidad', $list_entidad, $usercodger,'id="list_entidad"').'(automatico)'.br().PHP_EOL );
			$this->table->add_row('Monto similar o real a', form_input('mon_registroigual','').br().PHP_EOL);
			$this->table->add_row('Monto mayor o igual a', form_input('mon_registromayor','').br().PHP_EOL);
			$this->table->add_row('Por Concepto similar a:', form_input('des_registrolike','').br().PHP_EOL);
			$this->table->add_row('Por intranet:', form_input('sessioncarga','').br().PHP_EOL);
		echo $this->table->generate();
		echo form_hidden('accionejecutada',$accionejecutada).br().PHP_EOL;
		echo form_submit('gastofiltrarya', 'Ver reporte gasto', 'class="btn-primary btn"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
	}
	else if ($accionejecutada == 'gastosucursalesrevisarlos')
	{
		echo br().PHP_EOL;
		echo form_fieldset('SE MUESTRAN SOLO LAS 100 ULTIMAS CARGAS : <strong>'. date("Y/M") .' y '.date("Y/M", strtotime('-1 month')).'</strong> use los filtros para otras o para mas!!!',array('class'=>'containerin ')) . PHP_EOL;
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
		echo form_open_multipart($haciacontrolador.'/gastomanualcargarunolisto/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
		$this->table->set_template(array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">','cell_start' => '<td class="form-field-box odd">', ) );
			$this->table->add_row('Fecha del gasto (10 dias maximo):',form_input($valoresinputfecha).'(no mas de 10 dias atras)'.br().PHP_EOL, $classinput);
			$this->table->add_row('Categoria y SubCategoria:', form_dropdown('cod_subcategoria', $list_subcategoria, $cod_subcategoria,'id="list_subcategoria"').br().PHP_EOL);
			$this->table->add_row('De quien es el gasto:', form_dropdown('cod_entidad', $list_entidad, $usuariocodgernow, 'id="list_entidad"' ));
			$this->table->add_row('Monto (punto para decimal, sin coma)', form_input('mon_registro', '0.00', $classinput).' OJO: sin separador de miles!'.br().PHP_EOL);
			$this->table->add_row('Concepto o Detalle:', form_input('des_concepto', '', $classinput).br().PHP_EOL);
			/*$this->table->add_row('Concepto tipo:', form_dropdown('tipo_concepto', $list_tipo_concepto , 'SUCURSAL', $classinput).br().PHP_EOL);*/
			$this->table->add_row('Factura tipo:', form_dropdown('factura_tipo', $list_factura_tipo , 'CONTRIBUYENTE', $classinput));
			$this->table->add_row('Factura Numero (contribuyente):', form_input('factura_num', '', $classinput).br().PHP_EOL);
			$this->table->add_row('Factura RIF (contribuyente):', form_input('factura_rif', '', $classinput).br().PHP_EOL);
			$this->table->add_row('Factura escaneada? :', form_upload(array('name'  => 'factura_bin', 'id'=>'factura_bin')).br().PHP_EOL );
		echo $this->table->generate().br().PHP_EOL;
		echo form_hidden('estado', 'PENDIENTE'); // la carga de una sucursal es normal, la realizada por departamentos es administrativa
		echo form_submit('cargargastosenviar', 'Registrar gasto', 'class="btn-primary btn"');
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
		echo form_fieldset('<strong>Ediccion de Gasto CODIGO:"'.$cod_registro.'"</strong>',array('class'=>'containerin')) . PHP_EOL;
		echo form_open_multipart($haciacontrolador.'/gastomanualeditarunolisto/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();

		$this->table->set_template(array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">','cell_start' => '<td class="form-field-box odd">', ) );
			$this->table->add_row('Fecha del gasto (10 dias atras maximo):',form_input($valoresinputfecha).'(no mas de 10 dias atras)'.br().PHP_EOL, $classinput);
			$this->table->add_row('De quien es el gasto:', form_dropdown('cod_entidad', $list_entidad, $usuariocodgernow, 'id="list_entidad"' ));
			$this->table->add_row('Categoria y SubCategoria:', form_dropdown('cod_subcategoria', $list_subcategoria, $cod_subcategoria,'id="list_subcategoria"').br().PHP_EOL);
			$this->table->add_row('Monto (punto para decimal, sin coma)', form_input('mon_registro',$mon_registro, $classinput).' OJO: sin separador de miles!'.br().PHP_EOL);
			$this->table->add_row('Concepto o Detalle:', form_input('des_concepto',$des_concepto, $classinput).br().PHP_EOL);
			$this->table->add_row('Concepto tipo:', form_dropdown('tipo_concepto', $list_tipo_concepto , 'SUCURSAL', $classinput).br().PHP_EOL);
			$this->table->add_row('Factura tipo:', form_dropdown('factura_tipo', $list_factura_tipo , $factura_tipo, $classinput));
			$this->table->add_row('Factura Numero (contribuyente):', form_input('factura_num', $factura_num, $classinput).br().PHP_EOL);
			$this->table->add_row('Factura RIF (contribuyente):', form_input('factura_rif', $factura_rif, $classinput).br().PHP_EOL);
			$this->table->add_row('Factura escaneada:', form_upload(array('name'  => 'factura_binX', 'id'=>'factura_binX') ).br().'EN MODO EDICCION SOLO SUBA ADJUNTO SI DESEA CAMBIAR EL ARCHIVO'.br().' SINO DEJELO SIN ALTERAR ('.$factura_bin.')'.PHP_EOL );
		echo $this->table->generate().br().PHP_EOL;
		echo form_hidden('factura_bin', $factura_bin); // no se puede resubir archivos, entonces comparo si cambio el nombre y tomo el subido nuevo, sino esta variable es el nombre viejo inalterado
		echo form_hidden('cod_registro', $cod_registro); // la carga de una sucursal es normal, la realizada por departamentos es administrativa
		echo form_hidden('estado', 'PENDIENTE'); // la carga de una sucursal es normal, la realizada por departamentos es administrativa
		echo form_submit('cargargastosenviar', 'Modificar este gasto', 'class="btn-primary btn"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
	}
	else if ($accionejecutada == 'gastomanualfiltraruno')
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
	else if ($accionejecutada == 'gastoauditoriacodigo')
	{
		echo br().PHP_EOL;
		echo form_fieldset('<strong>ERRORES PENDIENTES</strong>',array('class'=>'containerin')) . PHP_EOL;
		if ( $accionauditar != '' )
			echo br().PHP_EOL.$htmlauditarcodigo.br().PHP_EOL;
		$this->table->clear();
		$this->table->set_template(array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">','cell_start' => '<td class="form-field-box odd">', ) );
			$this->table->add_row('Cantidad de gastos rechazados en cola :', $can_rechazados);
			$this->table->add_row('Cantidad de gastos incorrectos pendientes: ', $can_erroneos);
		echo $this->table->generate().br().PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;

		echo br().PHP_EOL;
	}
		echo '<div style="color:red;background-color:red">ESTADO OPERACION: <strong style="background-color:red;color:white;">'.$mens.'</strong></div>';
	?>
	</div>

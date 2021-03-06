	<?php

	$this->load->helper('html');

	// pintar botones de gestion para carga manual ya que las acciones de agregar y ver son customizadas
	$botongestion0 = anchor('cargargastoadministrativo/gastoregistros/add',form_button('cargargastoadministrativo/gastoregistros/add', 'Cargar Gasto interno', 'class="btn-primary btn b10" '));
	$botongestion1 = anchor('gas_registro_gastos_ingreso',form_button('gas_registro_gastos_ingreso', 'Ir a Gastos Mayorista', 'class="btn-primary btn" '));
	$botongestion2 = anchor('cargargastoadministrativo/index',form_button('cargargastoadministrativo/index', 'Filtrar internos (directo)', 'class="btn-primary btn" '));
	$botongestion3 = anchor('cargargastosucursalesadm/gastomanualfiltrarlos',form_button('cargargastosucursalesadm/gastomanualfiltrarlos', 'Filtrar internos (RAPIDO)', 'class="btn-primary btn b10" '));
	$this->table->clear();
	$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">' );
	$this->table->set_template($tmplnewtable);
	$this->table->add_row($botongestion0,$botongestion1,$botongestion2,$botongestion3);
	$tablabotonsgasto = $this->table->generate();

	// inicializar variables si no estan cargadas en el controlador
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

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionejecutada) ) $accionejecutada = 'cargardatosadministrativosfiltrar';
	if( !isset($list_entidad) ) $list_entidad = array('cod_entidad' => 'nombregalpon','cod_entidad2' => 'nombregalpon2');
	if( !isset($list_categoria) ) $list_categoria = array('cod_categoria' => 'Varios','cod_categoria2' => 'Gastos diversos');
	if( !isset($list_subcategoria) ) $list_subcategoria = array('cod_categoria' => 'Varios','cod_categoria2' => 'Gastos diversos');

	$jspickathingjs='<script type="text/javascript" src="' . base_url() . APPPATH . 'scripts/'.'pickathing.js"></script>';
		$jscategorialis = '<script>var selectjscategorialis = new Pickathing(\'list_categoria\', true);</script>';
		$jssubcategorialis = '<script>var selectjssubcategorialis = new Pickathing(\'list_subcategoria\', true);</script>';
		$jsentidadlis = '<script>var selectjsentidadlis = new Pickathing(\'list_entidad\', true);</script>';

	// detectar que mostrar segun lo enviado desde el controlador
	echo $tablabotonsgasto;
	if ($accionejecutada == 'cargardatosadministrativosfiltrar')
	{
		$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_fieldset('Gastos internos, usar puntos para decimal, todos opcionales filtrar...',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('cargargastoadministrativo/gastoregistros/', $htmlformaattributos) . PHP_EOL;
		echo 'LAS TIENDAS DEBEN ESPECIFICAR EL CODIGO DE GASTO PARA DETALLES POR MEDIO DE TICKET';
		$this->table->clear();
			$this->table->add_row('Fue Ingresado el/entre:',form_input($valoresinputfecha1ini).PHP_EOL.' y '.form_input($valoresinputfecha1fin).br().PHP_EOL);
			$this->table->add_row('Fecha factura el/entre:',form_input($valoresinputfecha2ini).PHP_EOL.' y '.form_input($valoresinputfecha2fin).br().PHP_EOL);
			$this->table->add_row('Por Categoria/Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria,null,'id="list_subcategoria"').PHP_EOL);
			$this->table->add_row('Por Centro de Costo:', form_dropdown('cod_entidad', $list_entidad,null,'id="list_entidad"').PHP_EOL );
			$this->table->add_row('Monto similar a exacto:', form_input('mon_registroigual','').br().PHP_EOL);
			$this->table->add_row('Monto mayor o igual a:', form_input('mon_registromayor','').br().PHP_EOL);
			$this->table->add_row('Por Concepto :', form_input('des_registrolike','').br().PHP_EOL);
			$this->table->add_row('Por intranet que carga :', form_input('sessioncarga','').br().PHP_EOL);
			$this->table->add_row('Por Codigo gasto :', form_input('cod_registro','').br().PHP_EOL);
		echo $this->table->generate();
		echo form_hidden('accionejecutada',$accionejecutada).br().PHP_EOL;
		echo form_submit('gastofiltrarya', 'Ver reporte gasto', 'class="btn-primary btn"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	else if ($accionejecutada == 'cargardatosadminnistrativosfiltrados')
	{
		echo form_fieldset('Cargas y registros de gastos INTERNOS',array('class'=>'container_blue containerin ')) . PHP_EOL;
		if( isset($css_files) )
			foreach($css_files as $file)
			{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		if( isset($js_files) )
			foreach($js_files as $file)
			{	echo '<script src="'.$file.'"></script>';	}
		echo $output;
		echo form_fieldset_close() . PHP_EOL;
	}
	echo $tablabotonsgasto;

	?>

	<?php

	$this->load->helper('html');

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

	// detectar que mostrar segun lo enviado desde el controlador
	echo br();
	if ($accionejecutada == 'cargardatosadministrativosfiltrar')
	{
		$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_fieldset('Ingrese los datos por favor',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('cargargastoadministrativo/gastoregistros/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Fue Creado el/entre:',form_input($valoresinputfecha1ini).PHP_EOL.' y '.form_input($valoresinputfecha1fin).br().PHP_EOL);
			$this->table->add_row('De Fechado el/entre:',form_input($valoresinputfecha2ini).PHP_EOL.' y '.form_input($valoresinputfecha2fin).br().PHP_EOL);
			$this->table->add_row('Por Categoria/Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria).br().PHP_EOL);
			$this->table->add_row('Por Centro de Costo:', form_dropdown('cod_entidad', $list_entidad).'(automatico)'.br().PHP_EOL );
			$this->table->add_row('Monto similar a:', form_input('mon_registroigual','').br().PHP_EOL);
			$this->table->add_row('Monto mayor o igual', form_input('mon_registromayor','').br().PHP_EOL);
			$this->table->add_row('Por Concepto :', form_input('des_registrolike','').br().PHP_EOL);
		echo $this->table->generate();
		echo form_hidden('accionejecutada',$accionejecutada).br().PHP_EOL;
		echo form_submit('gastofiltrarya', 'Ver reporte gasto', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	else if ($accionejecutada == 'cargardatosadminnistrativosfiltrados')
	{
		echo form_fieldset('Cargas y registros de gastos',array('class'=>'container_blue containerin ')) . PHP_EOL;
		if( isset($css_files) )
			foreach($css_files as $file)
			{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		if( isset($js_files) )
			foreach($js_files as $file)
			{	echo '<script src="'.$file.'"></script>';	}
		echo $output;
		echo form_fieldset_close() . PHP_EOL;
	}

	?>

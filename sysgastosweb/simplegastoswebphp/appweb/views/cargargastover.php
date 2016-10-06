	<?php

	$fec_registroini=date('Ymd');
	$idfecdesde='fec_registroini';
	$valoresinputfechaini = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));
	$fec_registrofin=date('Ymd');
	$idfechasta='fec_registrofin';
	$valoresinputfechafin = array('name'=>$idfechasta,'id'=>$idfechasta, 'onclick'=>'javascript:NewCssCal(\''.$idfechasta.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechasta, $$idfechasta));
	//echo 'Fecha del gasto:'.form_input($valoresinputfecha).br().PHP_EOL;

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionejecutada) ) $accionejecutada = 'cargardatosver';
	if( !isset($list_entidad) ) $list_entidad = array('cod_entidad' => 'nombregalpon','cod_entidad2' => 'nombregalpon2');
	if( !isset($list_categoria) ) $list_categoria = array('cod_categoria' => 'Varios','cod_categoria2' => 'Gastos diversos');
	if( !isset($list_subcategoria) ) $list_subcategoria = array('cod_categoria' => 'Varios','cod_categoria2' => 'Gastos diversos');
	// detectar que mostrar segun lo enviado desde el controlador
	echo br();
	if ($accionejecutada == 'cargardatosver')
	{
		$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_fieldset('Ingrese los datos por favor',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('cargargastover/gastoregistros/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Filto Fecha desde:',form_input($valoresinputfechaini).br().PHP_EOL);
			$this->table->add_row('Filto Fecha hasta:',form_input($valoresinputfechafin).br().PHP_EOL);
			$this->table->add_row('Por Categoria/Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria).br().PHP_EOL);
			$this->table->add_row('Por Centro de Costo:', form_dropdown('cod_entidad', $list_entidad).'(automatico)'.br().PHP_EOL );
			$this->table->add_row('Monto menor o igual', form_input('mon_registroigual','').br().PHP_EOL);
			$this->table->add_row('Monto mayor o igual', form_input('mon_registromayor','').br().PHP_EOL);
			$this->table->add_row('Descripcion del detalle :', form_input('des_registrolike','').br().PHP_EOL);
		echo $this->table->generate();
		echo form_submit('gastofiltrarya', 'Ver reporte gasto', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		/*
		echo form_fieldset('EJEMPLO DE COMO DEBE LLENARSE',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo $tableejemplo;
		echo form_fieldset_close() . PHP_EOL;
		*/
	}
	else if ($accionejecutada == 'cargardatosfiltrados')
	{
		echo form_fieldset('Cargas y registros de gastos',array('class'=>'container_blue containerin ')) . PHP_EOL;
/*		echo 'Fecha rango entre: '.$fec_registroini.' y '.$fec_registrofin.'<br>'.PHP_EOL;
		echo 'Codigo del registro: '.$cod_registro.'<br>'.PHP_EOL;
	*/	$this->load->helper('html');
		foreach($css_files as $file)
		{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		foreach($js_files as $file)
		{	echo '<script src="'.$file.'"></script>';	}
		echo $output;
		echo form_fieldset_close() . PHP_EOL;
	}

	?>

	<p class="footer">texto de ayuda (poner iframe qui con urta a soporte tickets con el tema</p>
	</div>

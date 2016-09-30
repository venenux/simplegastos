	<h1>Cargar un gasto</h1>
	<?php

	$fec_registro=date('Ymd');
	$idfecdesde='fec_registro';
	$valoresinputfecha = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));
	//echo 'Fecha del gasto:'.form_input($valoresinputfecha).br().PHP_EOL;

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionejecutada) ) $accionejecutada = 'cargardatos';
	if( !isset($list_entidad) ) $list_entidad = array('cod_sucursal' => 'nombregalpon','cod_sucursal2' => 'nombregalpon2');
	if( !isset($list_categoria) ) $list_categoria = array('cod_categoria' => 'Varios','cod_categoria2' => 'Gastos diversos');
	if( !isset($list_subcategoria) ) $list_subcategoria = array('cod_categoria' => 'Varios','cod_categoria2' => 'Gastos diversos');
	// detectar que mostrar segun lo enviado desde el controlador
	if ($accionejecutada == 'cargardatos')
	{
		$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
		$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_fieldset('Ingrese los datos por favor',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('cargargasto/registrargasto/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Fecha del gasto:',form_input($valoresinputfecha).'(no mas de 3 dias atras)'.br().PHP_EOL);
			$this->table->add_row('Categoria - Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria).br().PHP_EOL);
			$this->table->add_row('Pertenencia o sucursal:', form_dropdown('cod_entidad', $list_entidad).'(automatico)'.br().PHP_EOL );
			$this->table->add_row('Monto adjudicar', form_input('mon_registro','').br().PHP_EOL);
			$this->table->add_row('Descripcion del detalle :', form_input('des_registro','').br().PHP_EOL);
			$this->table->add_row('Adjuntar documento', form_upload(array('name'  => 'nam_archivo', 'id'=>'nam_archivo')).br().PHP_EOL );
		echo $this->table->generate();
		echo form_submit('login', 'Registrar gasto', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		/*
		echo form_fieldset('EJEMPLO DE COMO DEBE LLENARSE',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo $tableejemplo;
		echo form_fieldset_close() . PHP_EOL;
		*/
	}
	else if ($accionejecutada == 'resultadocargardatos')
	{
		echo form_fieldset('Gasto registrado y adjudicado',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo br();
		echo 'Su resultado:<br>'.PHP_EOL;
		echo 'Registro exitoso por : '.$userintran.', Fecha gasto: '.$fec_registro.'<br>'.PHP_EOL;
		echo 'Codigo del registro: '.$cod_registro.'<br>'.PHP_EOL;
		echo '<br>'.PHP_EOL;
		echo $htmltablacargasregistros;
		echo form_fieldset_close() . PHP_EOL;
	}

	?>

	<p class="footer">texto de ayuda (poner iframe qui con urta a soporte tickets con el tema</p>
	</div>

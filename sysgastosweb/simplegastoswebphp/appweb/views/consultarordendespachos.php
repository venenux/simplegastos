	<h1>Consultas de ordenes de despacho generadas</h1>
	<?php

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionejecutada) ) $accionejecutada = 'pedirdatos';
	if( !isset($list_ubicacionorigen) ) $list_ubicacionorigen = array('codigomsc' => 'nombregalpon','codigomsc2' => 'nombregalpon2');
	if( !isset($list_ubicaciondestin) ) $list_ubicaciondestin = array('codigomsc' => 'nombregalpon','codigomsc2' => 'nombregalpon2');
	// detectar que mostrar segun lo enviado desde el controlador
	if ($accionejecutada == 'pedirdatos')
	{
		$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
		$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_fieldset('Ingrese los datos por favor',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo form_open_multipart('generarordenconcarga/generacionautomatica/', $htmlformaattributos) . PHP_EOL;
		echo 'Origen:'.form_dropdown('ubicacionorigen', $list_ubicacionorigen).br().PHP_EOL;
		echo 'Destino:'.form_dropdown('ubicaciondestin', $list_ubicaciondestin).br().PHP_EOL;
		echo 'Fecha:'.form_input(array('name'=>'fechainicio', 'id'=>'fechainicio', 'value'=>'', 'onclick'=>'javascript:NewCal(\'fechainicio\',\'YYYYMMDD\')')).br().PHP_EOL;
		//echo form_submit('login', 'Revisar filtrado', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
	echo "<p>CUIDADO ESTE MODULO AUN NO FILTRA, solo muestra las ultimas 20 detalles</p>";
		echo form_fieldset_close() . PHP_EOL;
	}
	
	echo form_fieldset('Ultimas ordenes generadas en la ultimas horas',array('class'=>'container_blue containerin ')) . PHP_EOL;
	echo $htmltablaultimosdespachosordenados;
	echo form_fieldset_close() . PHP_EOL;
	
	if ($accionejecutada == 'resultadoordenescargadas')
	{
		$tmplnewtable = array ( 'table_open'  => '<table border="1" cellpadding="1" cellspacing="1" class="table">' );
        $this->table->set_caption(NULL);
		$this->table->clear();
		$this->table->set_template($tmplnewtable);
		$this->table->set_heading('des_producto', 'cod_producto', 'can_despachar', 'precio_archivo', 'precio_origen', 'precio_destino', 'existencia_origen');
		foreach ($resultadocarga as $rowtable)
		{
			$this->table->add_row($rowtable['des_producto'], $rowtable['cod_producto'], $rowtable['can_cantidaddespachar'], $rowtable['precio_archivo'], $rowtable['precio_origen'], $rowtable['precio_destino'], $rowtable['saldo_origen']);
		}
		echo form_fieldset('Orden de compra generada',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo 'Origen: '.$ubicacionorigen.', Destino: '.$ubicaciondestin.'<br>'.PHP_EOL;
		echo 'Oredn generada: '.$filenamen.', Procesados: '.$cantidadLineas.'<br>'.PHP_EOL;
		echo $this->table->generate();
		echo form_fieldset_close() . PHP_EOL;
		echo anchor('consultarordendespachos', 'Nueva consulta..');
	}
	?>

	<p class="footer">texto de ayuda (poner iframe qui con urta a soporte tickets con el tema</p>
	</div>

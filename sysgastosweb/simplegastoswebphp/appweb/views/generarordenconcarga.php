	<h1>Generar una orden de despacho (con carga de archivo)</h1>
	<?php

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionejecutada) ) $accionejecutada = 'cargardatos';
	if( !isset($list_ubicacionorigen) ) $list_ubicacionorigen = array('codigomsc' => 'nombregalpon','codigomsc2' => 'nombregalpon2');
	if( !isset($list_ubicaciondestin) ) $list_ubicaciondestin = array('codigomsc' => 'nombregalpon','codigomsc2' => 'nombregalpon2');
	// detectar que mostrar segun lo enviado desde el controlador
	if ($accionejecutada == 'cargardatos')
	{
		$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
		$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_fieldset('Ingrese los datos por favor',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo form_open_multipart('generarordenconcarga/generacionautomatica/', $htmlformaattributos) . PHP_EOL;
		echo 'Origen:'.form_dropdown('ubicacionorigen', $list_ubicacionorigen).br().PHP_EOL;
		echo 'Destino:'.form_multiselect('ubicaciondestin[]', $list_ubicaciondestin).br().PHP_EOL;
		echo 'Archivo:'.form_upload('archivoproductosprecionom').br().PHP_EOL;
		echo 'Separar:'.form_dropdown('archivoproductospreciosep', $separadores).br().PHP_EOL;
		echo form_submit('login', 'Generar', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo "".PHP_EOL;
		echo form_fieldset('EJEMPLO DE COMO ES EL ARCHIVO',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo $tableejemplo;
		echo form_fieldset_close() . PHP_EOL;
	}
	else if ($accionejecutada == 'resultadocargardatos')
	{
		echo form_fieldset('Orden de compra generada',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo 'Origen: '.$ubicacionorigen.', Destino: '.$eldestinosinsertar.'<br>'.PHP_EOL;
		echo 'Orden generada: '.$filenamen.', Procesados: '.$cantidadLineas.'<br>'.PHP_EOL;
		echo $htmltablageneradodetalle;
		echo form_fieldset_close() . PHP_EOL;
		echo anchor('generarordenconcarga', 'Revisar las ordenes existentes no procesadas.');
	}
	?>

	<p class="footer">texto de ayuda (poner iframe qui con urta a soporte tickets con el tema</p>
	</div>

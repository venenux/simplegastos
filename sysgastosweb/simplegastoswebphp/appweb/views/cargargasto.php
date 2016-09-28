	<h1>Cargar un gasto</h1>
	<?php

	$fec_registro='20160922';
	$idfecdesde='fec_registro';
	$valoresinputfecha = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));
	//echo 'Fecha del gasto:'.form_input($valoresinputfecha).br().PHP_EOL;

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionejecutada) ) $accionejecutada = 'cargardatos';
	if( !isset($list_categoria) ) $list_categoria = array('cod_sucursal' => 'nombregalpon','cod_sucursal2' => 'nombregalpon2');
	if( !isset($list_subcategoria) ) $list_subcategoria = array('cod_sucursal' => 'nombregalpon','cod_sucursal2' => 'nombregalpon2');
	// detectar que mostrar segun lo enviado desde el controlador
	if ($accionejecutada == 'cargardatos')
	{
		$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
		$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_fieldset('Ingrese los datos por favor',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo form_open_multipart('generarordenconcarga/generacionautomatica/', $htmlformaattributos) . PHP_EOL;
		echo 'Fecha del gasto:'.form_input($valoresinputfecha).br().PHP_EOL;
		echo 'Monto del gasto:'.form_input('mon_registro','').br().PHP_EOL;
		echo 'Descripcion del registro :'.form_input('des_registro','').br().PHP_EOL;
		echo 'Categoria/Subcategoria:'.form_dropdown('subcategoria', $list_subcategoria).br().PHP_EOL;
		echo 'Archivo factura:'.form_upload('archivoproductosprecionom').br().PHP_EOL;
		echo form_submit('login', 'Registrar gasto', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		echo form_fieldset('EJEMPLO DE COMO DEBE LLENARSE',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo $tableejemplo;
		echo form_fieldset_close() . PHP_EOL;
	}
	else if ($accionejecutada == 'resultadocargardatos')
	{
		echo form_fieldset('Orden de compra generada',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo 'Origen: '.$categoriaorigen.', Destino: '.$eldestinosinsertar.'<br>'.PHP_EOL;
		echo 'Orden generada: '.$filenamen.', Procesados: '.$cantidadLineas.'<br>'.PHP_EOL;
		echo $htmltablageneradodetalle;
		echo form_fieldset_close() . PHP_EOL;
		echo anchor('generarordenconcarga', 'Revisar las ordenes existentes no procesadas.');
	}

	?>

	<p class="footer">texto de ayuda (poner iframe qui con urta a soporte tickets con el tema</p>
	</div>

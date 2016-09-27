<h1>Carga de detalle cambio precio.</h1>
	<?php

	// si variables vacias llenar con datos mientras tanto
	if( !isset($listadesplegableordenesbox) ) $listadesplegableordenesbox = array('' => 'no hay aun ordenes, genera una');
	if( !isset($listadesplegablepreciosbox) ) $listadesplegablepreciosbox = array('' => 'no hay eventos, vaya a oasis');
	// detectar que mostrar segun lo enviado desde el controlador
	$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
	$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
	echo form_fieldset('Cargar detalle cambio de precio con la orden despacho',array('class'=>'container_blue containerin ')) . PHP_EOL;
	echo "<p>CUIDADO: no recarge o ejecute acciones al azar, use el menu arriba</p>";
	echo form_open_multipart('cargardetallecambioprecio/asociardetalleconordendespacho/', $htmlformaattributos) . PHP_EOL;
	echo 'Despacho:'.$listadesplegableordenesbox.' con cambio precio:'.$listadesplegablepreciosbox.PHP_EOL;
	echo form_submit('login', 'Cargar detalle!', 'class="btn btn-primary btn-large b10"');
	echo form_close() . PHP_EOL;
	echo form_fieldset_close() . PHP_EOL;
	if( !isset($accionejecutada) ) $accionejecutada = '';
	if ($accionejecutada == 'resultadocargardetallecambioprecio')
		echo "<script>alert('Cambio de precio cargado con detalle de orden despacho Puede seguir asociando y cargando o generar uan nueva orden de despacho')</script>";
	?>
	<p class="footer">texto de ayuda (poner iframe qui con urta a soporte tickets con el tema</p>
	</div>

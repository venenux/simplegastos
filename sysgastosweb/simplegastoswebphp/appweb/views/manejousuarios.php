<h1>Sistemas modulares para departamentos.</h1>
	<?php

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionpagina) ) $accionpagina = 'deslogeado';
	// detectar que mostrar segun lo enviado desde el controlador
	echo form_fieldset('DEBE CAMBIAR LA CLAVE DE CORREO, todo evento o accion se envia a su correo',array('class'=>'container_blue containerin ')) . PHP_EOL;
	if ($accionpagina == 'deslogeado')
	{
		$htmlformaattributos = array('name'=>'formulariomanejousuarios','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo "<p>CUIDADO: no recarge o ejecute acciones al azar, use el menu arriba</p>";
		echo form_open('manejousuarios/verificarintranet', $htmlformaattributos) . PHP_EOL;
		echo 'Usuario intranet:'.form_input('nombre','').PHP_EOL;
		echo 'Clave intranet:'.form_password('contrasena','').PHP_EOL;
		echo form_submit('login', 'Iniciar sesion', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
	}
	else
	{
		echo $presentar;
	}
	echo form_fieldset_close() . PHP_EOL;
	?>
	<p class="footer">texto de ayuda (poner iframe qui con urta a soporte tickets con el tema</p>
	</div>
<h1>Sistema de gasto (beta) fase 0.</h1>
	<?php

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionpagina) ) $accionpagina = 'deslogueado';
	// detectar que mostrar segun lo enviado desde el controlador
	echo form_fieldset('todo evento o accion se envia a su correo',array('class'=>'container_blue containerin ')) . PHP_EOL;
	if ($accionpagina == 'logueado')
	{
		echo $presentar;
	}
	else
	{
		$htmlformaattributos = array('name'=>'formulariomanejousuarios','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo "<h4>ADVERTENCIA: debe cambia la clave para activarse, adicional si el departamento Gastos no le da acceso no podra entrar.</h4>";
		echo form_open('manejousuarios/verificarintranet', $htmlformaattributos) . PHP_EOL;
		echo 'Usuario:'.form_input('nombre','').PHP_EOL;
		echo 'Clave :'.form_password('contrasena','').PHP_EOL;
		echo form_submit('login', 'Iniciar sesion', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
	}
	echo form_fieldset_close() . PHP_EOL;
	?>
	</div>
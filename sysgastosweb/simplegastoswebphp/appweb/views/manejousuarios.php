<h1>Sistema купить - ERP version 0.1.</h1>
	<?php

	// si variables vacias llenar con datos mientras tanto
	if( !isset($accionpagina) ) $accionpagina = 'deslogueado';
	if( !isset($can_errores) ) $can_errores = '0';
	// detectar que mostrar segun lo enviado desde el controlador
	echo form_fieldset('todo evento o accion se envia a su correo',array('class'=>'containerin ')) . PHP_EOL;
	if ($accionpagina == 'logueado')
	{
		echo $presentar.PHP_EOL;
//		echo form_fieldset('Errores cometidos auditados:'. $can_errores,array('class'=>'containerin ')) . PHP_EOL;
			if( isset($css_files) )
			foreach($css_files as $file)
			{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		if( isset($js_files) )
			foreach($js_files as $file)
			{	echo '<script src="'.$file.'"></script>';	}
//\		echo $htmltablacargaserrores; // $output
//		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
//		echo $botonesgestion . PHP_EOL;
	}
	else
	{
		$htmlformaattributos = array('name'=>'formulariomanejousuarios','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo "<h4>ADVERTENCIA: debe cambia la clave intranet para activarse, adicional solicitar por correo (soporte@intranet1.net.ve) el acceso para entrar.</h4>";
		echo form_open('manejousuarios/verificarintranet', $htmlformaattributos) . PHP_EOL;
		echo 'Usuario:'.form_input('nombre','').PHP_EOL;
		echo 'Clave :'.form_password('contrasena','').PHP_EOL;
		echo form_submit('login', 'Iniciar sesion', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
	}
	echo form_fieldset_close() . PHP_EOL;
	?>
	</div>

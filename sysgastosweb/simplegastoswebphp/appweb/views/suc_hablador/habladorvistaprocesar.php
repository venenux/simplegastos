<?php

	/* ******************* PARTE1 INI redefinir variables si no existen ************************ */

	$classinput = array('class'=>' form-input-box btn containerin');

	// mensage de titulo si no hay ninguno
	if( !isset($mens) or $mens == '' or $mens == null)
		$mens = '<strong>Hablador paso 1: codigos de productos</strong>';

	//  cargar la session o aseguramiento que exista un objeto session
	if( !isset($this->session) )
		$usuariocodgernow = null;
	else
		$usuariocodgernow = $this->session->userdata('cod_entidad');
echo $usuariocodgernow;
	// que parte del formulario y a donde ira a viajar el html
	if( !isset($accionejecutada) )
		$accionejecutada = 'habladorpaso1datos';

	// que formato hablador cargara
	if( !isset( $tipo_hablador) )
		 $tipo_hablador = 'suc_habladorlistado';

	// formatos disposnibles, sino hay entonces enviar uno dummy
	if( !isset( $list_tipo_hablador) )
		 $list_tipo_hablador = array( 'suc_habladorlistado' => 'suc_habladorlistado');

	// fecha que desea que el hablador se imprima (la que mostrara se imprimio)
	$fec_impresion='';
	$iffecimprini='fec_impresion';
	$fec_valoresinputini = array('name'=>$iffecimprini,'id'=>$iffecimprini, 'onclick'=>'javascript:NewCssCal(\''.$iffecimprini.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($iffecimprini, $$iffecimprini));

	// pintar botones de acciones hablador, ejemplo subir plantilla, imprimir, cargar descargar
	if( !isset($botongestion0) ) 
		$botongestion0 = '';

	/* ******************* PARTE1 FIN redefinir variables si no existen ************************ */

	$htmlformaattributos = array('name'=>'habladorform','class'=>'formularios','onSubmit'=>'return validageneric(this);');
	// impresion del mensaje de cabecera (tambein en el pie
		
	/* ******************* PARTE2 INI mostrar forms segun la accion enviada (controlador define) ******* */

	echo $botongestion0;
	// accion por defecto si no se definio nada o primera visita del usuarios
	if ($accionejecutada == 'habladorpaso1datos' or ! isset($accionejecutada) )
	{
		echo '<div style="background-color:white">ESTADO OPERACION: <strong style="color:red;">'.$mens.'</strong></div>';
		echo br() . PHP_EOL;
		echo form_fieldset('Impresion de 12(max) productos al hablador',array('class'=>'containerin')) . PHP_EOL;
		echo form_open_multipart('suc_hablador/habladorcontrol/habladorpaso1datosrecibe', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Fecha impresion:',form_input($fec_valoresinputini).PHP_EOL );
			$this->table->add_row('Codigos de productos'.br().'separados por comas', form_textarea('cod_productos','').PHP_EOL);
			$this->table->add_row('Entidad de coste:', form_dropdown('cod_entidad', $list_entidad,$usuariocodgernow,'id="list_entidad"').PHP_EOL );
			$this->table->add_row('Tipo hablador<br>' , form_dropdown('list_tipo_hablador', $list_tipo_hablador, $tipo_hablador,'id="tipo_hablador"').PHP_EOL);
			$this->table->add_row('Revisar existencia?:',form_checkbox('ind_existencia', '0', TRUE) .PHP_EOL);
		echo $this->table->generate();
		echo form_hidden('cod_entidadusr',$usuariocodgernow).br().PHP_EOL;
		echo form_hidden('accionejecutada',$accionejecutada).PHP_EOL;
		echo form_submit('habladorprocesar', 'Procesar hablador', 'class="btn-primary btn"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	// impresion del mensaje de cabecera (tambein en el pie
	echo '<div style="background-color:white">ESTADO OPERACION: <strong style="color:red;">'.$mens.'</strong></div>';
	}
	
	if ($accionejecutada == 'habladorpaso2impresion')
	{
/*		
				$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_open_multipart('cargargastosucursalesadm/gastosucursalesrevisarlos', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Registrado en el sistema el/entre:',form_input($fec_valoresinputini).PHP_EOL.' y '.form_input($valoresinputfecha1fin).br().PHP_EOL);
			$this->table->add_row('Fecha de factura o egreso el/entre:',form_input($valoresinputfecha2ini).PHP_EOL.' y '.form_input($valoresinputfecha2fin).br().PHP_EOL);
			$this->table->add_row('Por Categoria/Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria,null,'id="list_subcategoria"').br().$jspickathingjs.$jssubcategorialis.PHP_EOL);
			$this->table->add_row('Por Centro de Costo:', form_dropdown('cod_entidad', $list_entidad, $usercodger,'id="list_entidad"').'(automatico)'.br().$jspickathingjs.$jsentidadlis.PHP_EOL );
			$this->table->add_row('Monto exacto o cantidad igual a', form_input('mon_registroigual','').br().PHP_EOL);
			$this->table->add_row('Monto mayor o igual a', form_input('mon_registromayor','').br().PHP_EOL);
			$this->table->add_row('Por Concepto similar a:', form_input('des_registrolike','').br().PHP_EOL);
		echo $this->table->generate();
		echo form_hidden('accionejecutada',$accionejecutada).br().PHP_EOL;
		echo form_submit('gastofiltrarya', 'Mostrar hablador a imprimir', 'class="btn-primary btn"');
		echo form_close() . PHP_EOL;
		*/
	}
	/* ******************* PARTE2 FIN mostrar forms segun la accion enviada (controlador define) ******* */

	?>
	</div>

	<br>
	<?php
	
	$classinput = array('class'=>' form-input-box btn containerin');

	if( !isset($mens) )
		$mens = '<strong>Registros de actividades importantes</strong>';

	//  cargar la session o aseguramiento que exista un objeto session
	if( !isset($this->session) )
		$usuariocodgernow = null;
	else
		$usuariocodgernow = $this->session->userdata('cod_entidad');

	// que parte del formulario y a donde ira a viajar el html
	if( !isset($accionejecutado) )
		$accionejecutado = 'seccionlogpre';
	if( !isset($accionejecutara) )
		$accionejecutara = 'seccionlogver';

	// cargar las ubicciones/centro costo o aseguramiento que exista
	if( !isset($list_entidad) )
		$list_entidad = array($usuariocodgernow => 'Los propios gastos');

	// cargar las categorias y subcategorias o aseguramiento que exista
	if( !isset($list_categoria) ) $list_categoria = array('' => 'N/A');
	if( !isset($list_subcategoria) ) $list_subcategoria = array('' => 'Sin permiso para cargar');
	if( !isset($cod_subcategoria) ) $cod_subcategoria = '';

	$fec_registroini='';
	$idfecdesde='fec_registroini';
	$valoresinputfecha1ini = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));
	$fec_registrofin='';
	$idfechasta='fec_registrofin';
	$valoresinputfecha1fin = array('name'=>$idfechasta,'id'=>$idfechasta, 'onclick'=>'javascript:NewCssCal(\''.$idfechasta.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfechasta, $$idfechasta));

	$this->table->clear();
	$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">' );
	$this->table->set_template($tmplnewtable);

	if ($accionejecutado == 'seccionlogpre')
	{
		echo br() . PHP_EOL;
		echo form_fieldset('¿Que desea realizar?',array('class'=>'containerin')) . PHP_EOL;
				$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo form_open_multipart('admgastoslog/seccionlogver', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Criterio:',form_input('operacion','').PHP_EOL);
			$this->table->add_row('Del dia:',form_input($valoresinputfecha1ini).PHP_EOL.' al '.form_input($valoresinputfecha1fin).PHP_EOL);
			$this->table->add_row('Por intranet:', form_input('sessioncarga','').br().PHP_EOL);
		echo $this->table->generate();
		echo form_hidden('accionejecutado',$accionejecutado).br().PHP_EOL;
		echo form_hidden('accionejecutara','seccionlogver').br().PHP_EOL;
		echo form_submit('gastofiltrarya', 'Ver actividad', 'class="btn-primary btn"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	else if ($accionejecutado == 'seccionlogver')
	{
		echo br().PHP_EOL;
		echo form_fieldset('Actividades registradas de importancia </strong> SOLO LOS ULTIMOS REGISTROS!!!',array('class'=>'containerin ')) . PHP_EOL;
		echo form_hidden('accionejecutado',$accionejecutado).br().PHP_EOL;
		echo form_hidden('accionejecutara','seccionlogpre').br().PHP_EOL;
		$buttonnuevabusqueda = form_submit('gastofiltrarya', 'Nueva busqueda', 'class="btn-primary btn"');
		echo anchor('admgastoslog', $buttonnuevabusqueda);
		if( isset($css_files) )
			foreach($css_files as $file)
			{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		if( isset($js_files) )
			foreach($js_files as $file)
			{	echo '<script src="'.$file.'"></script>';	}
		echo $output;
		echo form_fieldset_close() . PHP_EOL;
	}
	?>

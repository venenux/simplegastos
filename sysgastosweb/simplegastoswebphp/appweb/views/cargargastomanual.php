	<?php

	//  cargar la session o aseguramiento que exista un objeto session
	if( !isset($this->session) )
		$usuariocodgernow = null;
	else
		$usuariocodgernow = $this->session->userdata('cod_entidad');

	// que parte del formulario y a donde ira a viajar el html
	if( !isset($accionejecutada) )
		$accionejecutada = 'gastomanualindex';

	// cargar las ubicciones/centro costo o aseguramiento que exista
	if( !isset($list_entidad) )
		$list_entidad = array($usuariocodgernow => 'Los propios gastos');

	// cargar las categorias y subcategorias o aseguramiento que exista
	if( !isset($list_categoria) ) $list_categoria = array('' => 'N/A');
	if( !isset($list_subcategoria) ) $list_subcategoria = array('' => 'Sin permiso para cargar');


	// pintar botones de gestion para carga manual ya que las acciones de agregar y ver son customizadas
	$this->table->clear();
	$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">' );
	$this->table->set_template($tmplnewtable);
	$this->table->add_row(
			anchor('cargargastomanual/gastomanualcargaruno',form_button('cargargastomanual/gastomanualcargaruno/add', 'Registrar Gasto', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('cargargastomanual/gastomanualrevisarlos',form_button('cargargastomanual/gastomanualrevisarlos/list', 'Revisar Gastos', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('cargargastomanual/gastomanualfiltrarlos',form_button('cargargastomanual/gastomanualfiltrarlos/veruno', 'Editar Gasto', 'class="btn btn-primary btn-large b10" '))
		);
	$tablabotonsgasto = form_fieldset('Gestion y Registro de Gastos',array('class'=>'container_blue containerin')) . PHP_EOL;
	$tablabotonsgasto .= $this->table->generate();
	$tablabotonsgasto .= form_fieldset_close() . PHP_EOL;

	$this->table->clear();
	$tmplnewtable = array ( 'table_open'  => '<table border="0" cellpadding="0" cellspacing="0" class="table">' );
	$this->table->set_template($tmplnewtable);
	$this->table->add_row($tablabotonsgasto);
	$botonesgestion = $this->table->generate();

	// detectar que mostrar segun lo enviado desde el controlador
	if ($accionejecutada == 'gastomanualindex')
	{
		echo br() . PHP_EOL;
		echo form_fieldset('¿Que desea realizar?',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_hidden('accionejecutada',$accionejecutada).PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
	}
	else if ($accionejecutada == 'gastomanualrevisarlos')
	{
		echo br().PHP_EOL;
		echo form_fieldset('Cargas y registros de gastos',array('class'=>'container_blue containerin ')) . PHP_EOL;
		echo form_hidden('accionejecutada',$accionejecutada).br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo $tabledelfiltrocualesgastos . PHP_EOL;
		if( isset($css_files) )
			foreach($css_files as $file)
			{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		if( isset($js_files) )
			foreach($js_files as $file)
			{	echo '<script src="'.$file.'"></script>';	}
		echo $output;
		echo form_fieldset_close() . PHP_EOL;
	}
	else if ($accionejecutada == 'gastomanualcargaruno')
	{
		$fec_registro=date('Ymd');
		$idfecdesde='fec_registro';
		$valoresinputfecha = array('name'=>$idfecdesde,'id'=>$idfecdesde, 'onclick'=>'javascript:NewCssCal(\''.$idfecdesde.'\',\'yyyyMMdd\',\'arrow\')','readonly'=>'readonly','value'=>set_value($idfecdesde, $$idfecdesde));

		$separadores = array(''=>'', '\t'=>'Tabulador (|)', ','=>'Coma (,)',';'=>'PuntoComa (;)');
		$htmlformaattributos = array('name'=>'formularioordendespachogenerar','class'=>'formularios','onSubmit'=>'return validageneric(this);');
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo form_fieldset('Ingrese los datos por favor',array('class'=>'container_blue containerin')) . PHP_EOL;
		echo form_open_multipart('cargargastoex/registrargasto/', $htmlformaattributos) . PHP_EOL;
		$this->table->clear();
			$this->table->add_row('Fecha del gasto:',form_input($valoresinputfecha).'(no mas de 3 dias atras)'.br().PHP_EOL);
			$this->table->add_row('Categoria - Concepto:', form_dropdown('cod_subcategoria', $list_subcategoria).br().PHP_EOL);
			$this->table->add_row('Centro de Costo:', form_dropdown('cod_entidad', $list_entidad, $usuariocodgernow ));
			$this->table->add_row('Monto adjudicar', form_input('mon_registro','').br().PHP_EOL);
			$this->table->add_row('Descripcion del detalle :', form_input('des_registro','').br().PHP_EOL);
			$this->table->add_row('Adjuntar documento', form_upload(array('name'  => 'nam_archivo', 'id'=>'nam_archivo')).br().PHP_EOL );
		echo $this->table->generate();
		echo form_submit('login', 'Registrar gasto', 'class="btn btn-primary btn-large b10"');
		echo form_close() . PHP_EOL;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
	}
	else if ($accionejecutada == 'gastomanualfiltrardouno')
	{
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
		echo form_fieldset('Gasto registrado adjudicado codigo: '.$cod_registro,array('class'=>'container_blue containerin ')) . PHP_EOL;
			if( isset($css_files) )
			foreach($css_files as $file)
			{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		if( isset($js_files) )
			foreach($js_files as $file)
			{	echo '<script src="'.$file.'"></script>';	}
		echo $output;
		echo form_fieldset_close() . PHP_EOL;
		echo br().PHP_EOL;
		echo $botonesgestion . PHP_EOL;
	}

	?>
	</div>

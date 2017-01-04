<?php
	$this->load->helper('html');
		foreach($css_files as $file)
		{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		foreach($js_files as $file)
		{	echo '<script src="'.$file.'"></script>';	}

	/*inicializo variables de botones html en vacio pues agrego todas generados o no generados */
/*	$tablabotonesusr = ''; $tablabotonsenti = ''; $tablabotonscatego = ''; $tablabotonssubcat = '';
	if($admvistaurlaccion != 'admusuarios')
	{
		$tablabotonesusr = '';
		$this->table->clear();
		$this->table->add_row(
			anchor('admusuarios/admusuariosavanzado/add',form_button('admusuarios/admusuariosavanzado/index/add', 'Agregar Usuario', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admusuarios/admusuariosavanzado/list',form_button('admusuarios/admusuariosavanzado/list', 'Ver/Edit Usuarios', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonesusr .= $this->table->generate() . PHP_EOL;
	}
	if($admvistaurlaccion != 'admentidades')
	{
		$tablabotonsenti = '';
		$this->table->clear();
		$this->table->add_row(
			anchor('admentidades/admsucursalesyusuarios/add',form_button('admentidades/admsucursalesyusuarios/add', 'Agregar Codger', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admentidades/admsucursalesyusuarios/list',form_button('admentidades/admsucursalesyusuarios/list', 'Ver/Edit Codgers', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonsenti .= $this->table->generate() . PHP_EOL;
	}
	if($admvistaurlaccion != 'admcategorias')
	{
		$tablabotonscatego = '';
		$this->table->clear();
		$this->table->add_row(
			anchor('admcategorias/admcategorias/add',form_button('admcategorias/admcategorias/add', 'Agregar Categoria', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admcategorias/admcategorias/list',form_button('admcategorias/admcategorias/list', 'Ver/Edit Categorias', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonscatego .= $this->table->generate() . PHP_EOL;
	}
	if($admvistaurlaccion != 'admsubcategorias')
	{
		$tablabotonssubcat = '';
		$this->table->clear();
		$this->table->add_row(
			anchor('admsubcategorias/admsubcategorias/add',form_button('admsubcategorias/admsubcategorias/add', 'Agregar Subcategoria', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admsubcategorias/admsubcategorias/list',form_button('admsubcategorias/admsubcategorias/list', 'Editar Subcategorias', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonssubcat .= $this->table->generate(). PHP_EOL;
	}

	$this->table->clear();
	$this->table->add_row($tablabotonesusr,$tablabotonsenti,$tablabotonscatego,$tablabotonssubcat);
	$botonesgestion = $this->table->generate();

	echo $botonesgestion;
*/	if( isset($mensage)) echo $mensage;
	echo $output;
//	echo $botonesgestion;


<?php
	//"<h3> ".$admins=anchor('admusuarios','Gestion')."</h3>".
	$this->load->helper('html');
		foreach($css_files as $file)
		{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		foreach($js_files as $file)
		{	echo '<script src="'.$file.'"></script>';	}

	if($admvistaurlaccion == 'admusuariosentidad')
	{
		$this->table->clear();
		$this->table->add_row(
			anchor('admusuarios',form_button('admusuarios/admusuariosavanzado/add', 'Agregar Usuario', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admusuarios',form_button('admusuarios/admusuariosavanzado/list', 'Editar Usuarios', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonsusurs = form_fieldset('Gestion de Usuarios',array('class'=>'container_blue containerin')) . PHP_EOL;
		$tablabotonsusurs .= $this->table->generate();
		$tablabotonsusurs .= form_fieldset_close() . PHP_EOL;

		$this->table->clear();
		$this->table->add_row(
			anchor('admentidades',form_button('admentidades/admsucursalesyusuarios/add', 'Agregar Codger', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admentidades',form_button('admentidades/admsucursalesyusuarios/list', 'Editar Codgers', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonsentid = form_fieldset('Gestion de Centros de costo',array('class'=>'container_blue containerin')) . PHP_EOL;
		$tablabotonsentid .= $this->table->generate();
		$tablabotonsentid .= form_fieldset_close() . PHP_EOL;

		$this->table->clear();
			$this->table->add_row($tablabotonsusurs,$tablabotonsentid);
		$botonesgestion = $this->table->generate();
	}
	if($admvistaurlaccion == 'admcategoriasconceptos')
	{
		$this->table->clear();
		$this->table->add_row(
			anchor('admcategorias/admcategorias/add',form_button('admcategorias/admcategorias/add', 'Agregar Categoria', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admcategorias/admcategorias/list',form_button('admcategorias/admcategorias/list', 'Editar Categorias', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonscatego = form_fieldset('Gestion de Usuarios',array('class'=>'container_blue containerin')) . PHP_EOL;
		$tablabotonscatego .= $this->table->generate();
		$tablabotonscatego .= form_fieldset_close() . PHP_EOL;

		$this->table->clear();
		$this->table->add_row(
			anchor('admsubcategorias/admsubcategorias/add',form_button('admsubcategorias/admsubcategorias/add', 'Agregar Subcategoria', 'class="btn btn-primary btn-large b10" '))
			,
			anchor('admsubcategorias/admsubcategorias/list',form_button('admsubcategorias/admsubcategorias/list', 'Editar Subcategorias', 'class="btn btn-primary btn-large b10" '))
		);
		$tablabotonssubcat = form_fieldset('Gestion de Centros de costo',array('class'=>'container_blue containerin')) . PHP_EOL;
		$tablabotonssubcat .= $this->table->generate();
		$tablabotonssubcat .= form_fieldset_close() . PHP_EOL;

		$this->table->clear();
			$this->table->add_row($tablabotonscatego,$tablabotonssubcat);
		$botonesgestion = $this->table->generate();
	}
	echo $botonesgestion;
	echo $output;
	echo $botonesgestion;


	<br>
	<?php
		echo form_fieldset('Registros de actividad',array('class'=>'container_blue containerin')) . PHP_EOL;
		foreach($css_files as $file)
		{	echo '<link type="text/css" rel="stylesheet" href="'.$file.'" />';	}
		foreach($js_files as $file)
		{	echo '<script src="'.$file.'"></script>';	}
		echo $output;
		echo form_fieldset_close() . PHP_EOL;
	?>

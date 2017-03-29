<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.3.1
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * HTML Table Generating Class
 *
 * Lets you create tables manually or from database result objects, or arrays.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	HTML Tables
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/uri.html
 */
class CI_Table {

	var $rows				= array();
	var $heading			= array();
	var $auto_heading		= TRUE;
	var $caption			= NULL;
	var $template			= NULL;
	var $newline			= "\n";
	var $empty_cells		= "";
	var	$function			= FALSE;
	var $tableid			= "";
	var $enabledatatable	= "0";
	var $topindexpager		= "0";
	var $datatableoptions	= "searchable: true, fixedHeight: true, perPage: 5";

	public function __construct()
	{
		log_message('debug', "Table Class Initialized");
		$this->tableid = 'table'.rand(100,999);
	}

	// --------------------------------------------------------------------

	/**
	 * Set the template
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function set_template($template)
	{
		if ( ! is_array($template))
		{
			return FALSE;
		}

		$this->template = $template;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the table heading
	 *
	 * Can be passed as an array or discreet params
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */
	function set_heading()
	{
		$args = func_get_args();
		$this->heading = $this->_prep_args($args);
	}

	// --------------------------------------------------------------------

	/**
	 * set_datatables option or enable it.  Takes a one-dimensional array as input
	 * and set defaults options in vanilla data table generation scripts.
	 * each option must be parset independient.
	 * if optionkey its "defaults" will generate defualt code js
	 *
	 * @access	public
	 * @param	array( optionkey: optionvalue)
	 * @return	void
	 */
	function set_datatables($options = array())
	{
		$jsoptions = '';
		$this->enabledatatable = '1';

		if ( ! is_array($options))
		{
			if ( $options === FALSE )
				$this->enabledatatable = '0';
			$options = array($options);
		}

		if (count($options) === 0)
		{
			$options = array("sortable" => "true", "searchable" => "true", "fixedHeight" => "true", "perPage" => "15", "perPageSelect"=>"false" );
		}

		foreach ($options as $key => $val)
		{
			$key = (string) $key;
			$val = (string) $val;

			if (count($options) === 1 AND strpos($key, 'perPage') === FALSE AND strpos($key, 'topindexpager') === FALSE )
				$jsoptions .= ' perPage: 5,';

			if (count($options) === 1 AND strpos($key, 'searchable') === FALSE)
				$jsoptions .= ' searchable: true,';

			$this->topindexpager = "0";
			if ( strpos($key, 'perPage') !== FALSE)
			{
				$this->topindexpager = "1";	// theres a problem here, i set and later cannot access;
				if ( ( (int)$val) < 16 AND strpos($key, 'topindexpager') !== FALSE) 
				$this->topindexpager = "1";	// theres a problem here, i set and later cannot access;
			}

			if ( strpos($key, 'topindexpager') !== FALSE AND $val == 'false' )
				$this->topindexpager = "0";

			if ( $this->topindexpager != "0" )
				$jsoptions .= ' topindexpager: '.$this->topindexpager.',';

			if ( !is_array($val) AND $key != 'topindexpager')
				$jsoptions .= ' '.$key.': '.$val.',';
		}

		$jsoptions = substr($jsoptions, 0, -1);
		$this->datatableoptions = $jsoptions;
	}

	// --------------------------------------------------------------------

	/**
	 * Set columns.  Takes a one-dimensional array as input and creates
	 * a multi-dimensional array with a depth equal to the number of
	 * columns.  This allows a single array with many elements to  be
	 * displayed in a table that has a fixed column count.
	 *
	 * @access	public
	 * @param	array
	 * @param	int
	 * @return	void
	 */
	function make_columns($array = array(), $col_limit = 0)
	{
		if ( ! is_array($array) OR count($array) == 0)
		{
			return FALSE;
		}

		// Turn off the auto-heading feature since it's doubtful we
		// will want headings from a one-dimensional array
		$this->auto_heading = FALSE;

		if ($col_limit == 0)
		{
			return $array;
		}

		$new = array();
		while (count($array) > 0)
		{
			$temp = array_splice($array, 0, $col_limit);

			if (count($temp) < $col_limit)
			{
				for ($i = count($temp); $i < $col_limit; $i++)
				{
					$temp[] = '&nbsp;';
				}
			}

			$new[] = $temp;
		}

		return $new;
	}

	// --------------------------------------------------------------------

	/**
	 * Set "empty" cells
	 *
	 * Can be passed as an array or discreet params
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */
	function set_empty($value)
	{
		$this->empty_cells = $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Add a table row
	 *
	 * Can be passed as an array or discreet params
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */
	function add_row()
	{
		$args = func_get_args();
		$this->rows[] = $this->_prep_args($args);
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Args
	 *
	 * Ensures a standard associative array format for all cell data
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	function _prep_args($args)
	{
		// If there is no $args[0], skip this and treat as an associative array
		// This can happen if there is only a single key, for example this is passed to table->generate
		// array(array('foo'=>'bar'))
		if (isset($args[0]) AND (count($args) == 1 && is_array($args[0])))
		{
			// args sent as indexed array
			if ( ! isset($args[0]['data']))
			{
				foreach ($args[0] as $key => $val)
				{
					if (is_array($val) && isset($val['data']))
					{
						$args[$key] = $val;
					}
					else
					{
						$args[$key] = array('data' => $val);
					}
				}
			}
		}
		else
		{
			foreach ($args as $key => $val)
			{
				if ( ! is_array($val))
				{
					$args[$key] = array('data' => $val);
				}
			}
		}

		return $args;
	}

	// --------------------------------------------------------------------

	/**
	 * Add a table caption
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_caption($caption)
	{
		$this->caption = $caption;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the table
	 *
	 * @access	public
	 * @param	mixed
	 * @return	string
	 */
	function generate($table_data = NULL)
	{
		// The table data can optionally be passed to this function
		// either as a database result object or an array
		if ( ! is_null($table_data))
		{
			if (is_object($table_data))
			{
				$this->_set_from_object($table_data);
			}
			elseif (is_array($table_data))
			{
				$set_heading = (count($this->heading) == 0 AND $this->auto_heading == FALSE) ? FALSE : TRUE;
				$this->_set_from_array($table_data, $set_heading);
			}
		}

		// Is there anything to display?  No?  Smite them!
		if (count($this->heading) == 0 AND count($this->rows) == 0)
		{
			return 'Undefined table data';
		}

		// Compile and validate the template date
		$this->_compile_template();

		// set a custom cell manipulation function to a locally scoped variable so its callable
		$function = $this->function;

		// autodetec the id if not present or if are present
		$this->_compile_id();

		// Build the table!
		$out = $this->template['table_open'];
		$out .= $this->newline;

		// Add any caption here
		if ($this->caption)
		{
			$out .= $this->newline;
			$out .= '<caption>' . $this->caption . '</caption>';
			$out .= $this->newline;
		}

		// Is there a table heading to display?
		if (count($this->heading) > 0)
		{
			$out .= $this->template['thead_open'];
			$out .= $this->newline;
			$out .= $this->template['heading_row_start'];
			$out .= $this->newline;

			foreach ($this->heading as $heading)
			{
				$temp = $this->template['heading_cell_start'];

				foreach ($heading as $key => $val)
				{
					if ($key != 'data')
					{
						$temp = str_replace('<th', "<th $key='$val'", $temp);
					}
				}

				$out .= $temp;
				$out .= isset($heading['data']) ? $heading['data'] : '';
				$out .= $this->template['heading_cell_end'];
			}

			$out .= $this->template['heading_row_end'];
			$out .= $this->newline;
			$out .= $this->template['thead_close'];
			$out .= $this->newline;
		}

		// Build the table rows
		if (count($this->rows) > 0)
		{
			$out .= $this->template['tbody_open'];
			$out .= $this->newline;

			$i = 1;
			foreach ($this->rows as $row)
			{
				if ( ! is_array($row))
				{
					break;
				}

				// We use modulus to alternate the row colors
				$name = (fmod($i++, 2)) ? '' : 'alt_';

				$out .= $this->template['row_'.$name.'start'];
				$out .= $this->newline;

				foreach ($row as $cell)
				{
					$temp = $this->template['cell_'.$name.'start'];

					foreach ($cell as $key => $val)
					{
						if ($key != 'data')
						{
							$temp = str_replace('<td', "<td $key='$val'", $temp);
						}
					}

					$cell = isset($cell['data']) ? $cell['data'] : '';
					$out .= $temp;

					if ($cell === "" OR $cell === NULL)
					{
						$out .= $this->empty_cells;
					}
					else
					{
						if ($function !== FALSE && is_callable($function))
						{
							$out .= call_user_func($function, $cell);
						}
						else
						{
							$out .= $cell;
						}
					}

					$out .= $this->template['cell_'.$name.'end'];
				}

				$out .= $this->template['row_'.$name.'end'];
				$out .= $this->newline;
			}

			$out .= $this->template['tbody_close'];
			$out .= $this->newline;
		}

		// try to detect if user wants datatables eye-candy behavior
		$this->_compile_datatable();

		// close the main table tag and lets out!
		$out .= $this->template['table_close'];

		// Clear table class properties before generating the table
		$this->clear();

		return $out;
	}

	// --------------------------------------------------------------------

	/**
	 * Clears the table arrays.  Useful if multiple tables are being generated
	 * id and datatable must be reset, due deafult template wil use that values, instanciated
	 *
	 * @access	public
	 * @return	void
	 */
	function clear()
	{
		$this->rows				= array();
		$this->heading			= array();
		$this->auto_heading		= TRUE;
		$this->tableid			= 'table'.rand(100,999);
		$this->enabledatatable	= '0';
		$this->template 		= $this->_default_template();
	}

	// --------------------------------------------------------------------

	/**
	 * Set table data from a database result object
	 *
	 * @access	public
	 * @param	object
	 * @return	void
	 */
	function _set_from_object($query)
	{
		if ( ! is_object($query))
		{
			return FALSE;
		}

		// First generate the headings from the table column names
		if (count($this->heading) == 0)
		{
			if ( ! method_exists($query, 'list_fields'))
			{
				return FALSE;
			}

			$this->heading = $this->_prep_args($query->list_fields());
		}

		// Next blast through the result array and build out the rows

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$this->rows[] = $this->_prep_args($row);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set table data from an array
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function _set_from_array($data, $set_heading = TRUE)
	{
		if ( ! is_array($data) OR count($data) == 0)
		{
			return FALSE;
		}

		$i = 0;
		foreach ($data as $row)
		{
			// If a heading hasn't already been set we'll use the first row of the array as the heading
			if ($i == 0 AND count($data) > 1 AND count($this->heading) == 0 AND $set_heading == TRUE)
			{
				$this->heading = $this->_prep_args($row);
			}
			else
			{
				$this->rows[] = $this->_prep_args($row);
			}

			$i++;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * compile and set a table with css responsive and good behavior, need css linked in header manually by now!
	 *  see documentation in scripts directory vanilla-datatables.js and style directory vanilla-datatables.css
	 *
	 * @access	private
	 * @return	void
	 */
	function _compile_datatable()
	{
		$tableclosetag = $this->template['table_close'];
		$tableopentag = $this->template['table_open'];
		// detecting if id was given or autogenerated, vanilla datatable js need it
		if (strpos($tableopentag, 'id') !== FALSE)
		{
			// detecting if id might be autogenerated js datatable code, got the id for
			$tableid = $this->tableid;

			// TODO: try to build datatables from custom template enabling
			$indexstartcut = (stripos($tableopentag,'vanilladatatable=') +17);
			$indexendscut = $indexstartcut + 2;
			$isjsenabled = substr($tableopentag , $indexstartcut, ( $indexendscut - $indexstartcut) );

			// due by default is '0', enable it if any order to enable data tables...
			if( $this->enabledatatable != '0' AND $this->datatableoptions != '' )
			{
				// lest generatin a vanilla datatable code for table id
				$tablejscodetags = '<script src="'.base_url() . APPPATH . 'scripts/vanilla-dataTables.js"></script>';
				$tablejscodetags .= '
					<script>
					var table'.$tableid.' = document.getElementById(\''.$tableid.'\');
					var options'.$tableid.' = { '.$this->datatableoptions.' };
					var data'.$tableid.' = new DataTable(table'.$tableid.', options'.$tableid.');'. $this->topindexpager.';';

				// TODO: still not detecting good the enabling of top pager in "set_datatables" so trying here
				$searchinoptions = $this->datatableoptions;
				if ( strpos($searchinoptions, 'topindexpager') !== FALSE)
				{
					$tablejscodetags .= '
						// top paginator custom render
						var topContainer'.$tableid.' = data'.$tableid.'.container.parentNode.firstElementChild;
						var topPager'.$tableid.' = data'.$tableid.'.paginator.parentNode.cloneNode(true);
						// Append the new pager
						topContainer'.$tableid.'.appendChild(topPager'.$tableid.');
						// Enable the new pager
						topContainer'.$tableid.'.addEventListener("click", function(e) {
							var target = e.target;
							if ( target.nodeName.toLowerCase() === \'a\' && target.hasAttribute("data-page") ) {
									var page = target.getAttribute("data-page");
									data'.$tableid.'.page(page);
								}	}	);
						// Update the new pager
						data'.$tableid.'.on("datatable.update", updatePager'.$tableid.');
						data'.$tableid.'.on("datatable.page", updatePager'.$tableid.');
						// define hack function to update when click new pager
							function updatePager'.$tableid.'() 
							{
								topContainer'.$tableid.'.replaceChild(data'.$tableid.'.paginator.parentNode.cloneNode(true), topContainer'.$tableid.'.lastElementChild);
							}';
				}
				$tablejscodetags .= '
				</script>'.PHP_EOL;

				// added the js code to the close tag with the table
				$this->template['table_close'] = $tableclosetag.$tablejscodetags;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * autodetect id and set for table
	 *
	 * @access	_compile_id
	 * @return	void
	 */
	function _compile_id()
	{
		$tableopentag = $this->template['table_open'];
		// detecting if id might be autogenerated
		if (strpos($tableopentag, 'id') === FALSE)
		{
			$tableidtag = ' id="'.$this->tableid.'">';
			// setup missing id in template open
			$this->template['table_open'] = str_replace('>', $tableidtag, $tableopentag);
		}
		// lest extrac the id provided by user or already exist
		else
		{
			$indexstartcut = (stripos($tableopentag,'id="') +4);
			$indexendscut = (stripos($tableopentag,'"', $indexstartcut));
			$idname = substr($tableopentag , $indexstartcut, ( $indexendscut - $indexstartcut) );
			// template already has id, so configure instance class for script usage
			$this->tableid = $idname;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Compile Template, if an array was given, isset will set only the provides, if none, use default
	 *
	 * @access	private
	 * @return	void
	 */
	function _compile_template()
	{
		if ($this->template == NULL)
		{
			$this->template = $this->_default_template();
			return;
		}

		$this->temp = $this->_default_template();
		foreach (array('table_open', 'thead_open', 'thead_close', 'heading_row_start', 'heading_row_end', 'heading_cell_start', 'heading_cell_end', 'tbody_open', 'tbody_close', 'row_start', 'row_end', 'cell_start', 'cell_end', 'row_alt_start', 'row_alt_end', 'cell_alt_start', 'cell_alt_end', 'table_close') as $val)
		{
			if ( ! isset($this->template[$val]))
			{
				$this->template[$val] = $this->temp[$val];
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Default Template
	 *
	 * @access	private
	 * @return	void
	 */
	function _default_template()
	{
		return  array (
						'table_open'			=> '<table border="0" cellpadding="1" cellspacing="0" vanilladatatable='.$this->enabledatatable.' >',

						'thead_open'			=> '<thead>',
						'thead_close'			=> '</thead>',

						'heading_row_start'		=> '<tr>',
						'heading_row_end'		=> '</tr>',
						'heading_cell_start'	=> '<th>',
						'heading_cell_end'		=> '</th>',

						'tbody_open'			=> '<tbody>',
						'tbody_close'			=> '</tbody>',

						'row_start'				=> '<tr>',
						'row_end'				=> '</tr>',
						'cell_start'			=> '<td>',
						'cell_end'				=> '</td>',

						'row_alt_start'		=> '<tr>',
						'row_alt_end'			=> '</tr>',
						'cell_alt_start'		=> '<td>',
						'cell_alt_end'			=> '</td>',

						'table_close'			=> '</table>'
					);
	}


}


/* End of file Table.php */
/* Location: ./system/libraries/Table.php */

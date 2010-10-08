<?php

/**
 * @author		Dimas Begunoff
 * @copyright	Copyright (c) 2010, Dimas Begunoff, http://farinspace.com
 * @license		http://en.wikipedia.org/wiki/MIT_License The MIT License
 * @package		google-spreadsheet
 * @version		1.1
 * @link		http://github.com/farinspace/google-spreadsheet
 * @link		http://farinspace.com
 */

class Google_Worksheet
{
	protected $id;

	protected $title;

	public function __construct($title)
	{
		if (isset($title))
		{
			$this->set_title($title);
		}
		else
		{
			throw new Exception('Worksheet title required');
		}
	}

	public function set_id($id)
	{
		$this->id = $id;
	}

	public function get_id()
	{
		return $this->id;
	}

	public function get_title()
	{
		return $this->title;
	}

	public function set_title($title)
	{
		$this->title = $title;
	}

	public function delete()
	{

	}

	public function update()
	{
		
	}
}

/* end of file */
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

require_once 'Google_Worksheet.php';

class Google_Spreadsheet
{
	protected $auth_header;

	protected $spreadsheets;

	protected $worksheets;

	protected $worksheet;

	protected $worksheet_id;

	protected $spreadsheet_id;

	protected $worksheet_id;

	/**
	 * @since	1.1
	 * @access	public
	 * @param	string $username
	 * @param	string $password
	 * @param	string $spreadsheet
	 * @see		use_spreadsheet()
	 */
	public function __construct($username, $password, $spreadsheet = NULL)
	{
		if (isset($username) AND isset($password))
		{
			$this->authenticate($username, $password);

			if (isset($spreadsheet))
			{
				$this->use_spreadsheet($spreadsheet);
			}
		}
		else
		{
			throw new Exception('Google account username and password required');
		}
	}

	/**
	 * @since	1.1
	 * @access	protected
	 * @param	string $username
	 * @param	string $password
	 */
	protected function authenticate($username, $password)
	{
		$content = $this->do_post('https://www.google.com/accounts/ClientLogin', array
		(
			'accountType' => 'GOOGLE',
			'Email' => $username,
			'Passwd' => $password,
			'service' => 'wise',
			'source' => 'farinspace-Google_Spreadsheet_Helper-1.0',
		));

		if (stristr($content, 'badauthentication'))
		{
			throw new Exception('Google account username and/or password are incorrect');
		}

		$a = preg_split('/\n|\r/', trim($content));

		if (is_array($a))
		{
			foreach ($a as $v)
			{
				if ('Auth' == substr($v, 0, 4))
				{
					$auth = trim(str_replace('Auth=', '', $v));

					break;
				}
			}
		}

		if ( ! isset($auth))
		{
			throw new Exception('Unable to authorize');
		}

		$this->auth_header = 'Authorization: GoogleLogin auth=' . $auth;
	}

	protected function check_auth_header()
	{
		if ( ! isset($this->auth_header))
		{
			throw new Exception('Authorization required');
		}
	}

	protected function check_spreadsheet_id()
	{
		if ( ! isset($this->spreadsheet_id))
		{
			throw new Exception('A spreadsheet name or ID is required');
		}
	}

	/**
	 * @since	1.1
	 * @access	public
	 * @return	array
	 */
	public function get_spreadsheets()
	{
		$this->check_auth_header();

		if (isset($this->spreadsheets))
		{
			return $this->spreadsheets;
		}

		$content = $this->do_get('https://spreadsheets.google.com/feeds/spreadsheets/private/full');

		if ('<?xml' !== substr($content, 0, 5))
		{
			throw new Exception('Google API bad URI endpoint');
		}

		try
		{
			$xml = new SimpleXmlElement($content);

			$this->spreadsheets = array();

			foreach ($xml->entry as $entry)
			{
				array_push($this->spreadsheets, array
				(
					'id' => basename($entry->id),
					'name' => trim($entry->title),
				));
			}

			return $this->spreadsheets;
		}
		catch (Exception $e)
		{
			throw new Exception('Unable to get list of spreadsheets');
		}
	}

	/**
	 * @since	1.1
	 * @access	public
	 * @param	string $name
	 * @return	Google_Spreadsheet
	 */
	public function use_spreadsheet($name)
	{
		$this->get_spreadsheets();

		if (is_array($this->spreadsheets))
		{
			foreach ($this->spreadsheets as $v)
			{
				if ($name == $v['name'])
				{
					$found = TRUE;
					
					break;
				}
			}

			if (isset($found))
			{
				$this->set_spreadsheet_id($v['id']);

				return $this;
			}
		}
		
		throw new Exception('Unable to find spreadsheet');
	}

	/**
	 * @since	1.1
	 * @access	public
	 * @param	string $id
	 * @return	Google_Spreadsheet
	 */
	public function set_spreadsheet_id($id)
	{
		$this->spreadsheet_id = $id;

		return $this;
	}

	function get_worksheets()
	{
		$this->check_auth_header();

		$this->check_spreadsheet_id();

		if (isset($this->worksheets))
		{
			return $this->worksheets;
		}

		$content = $this->do_get('https://spreadsheets.google.com/feeds/worksheets/' . $this->spreadsheet_id . '/private/full');

		if ('<?xml' !== substr($content, 0, 5))
		{
			throw new Exception('Google API bad URI endpoint');
		}

		try
		{
			$xml = new SimpleXmlElement($content);

			$this->worksheets = array();

			foreach ($xml->entry as $entry)
			{
				$title = trim(strval($entry->title));

				$ws = new Google_Worksheet($title);

				$ws->set_id(basename($entry->id));

				$this->worksheets[$title] = $ws;
			}

			return $this->worksheets;
		}
		catch (Exception $e)
		{
			throw new Exception('Unable to get list of worksheets');
		}
	}

	function use_worksheet($title = 'Sheet1')
	{
		$this->get_worksheets();

		if (is_array($this->worksheets))
		{
			if (isset($this->worksheets[$title]))
			{
				$this->set_worksheet_id($this->worksheets[$title]->get_id());

				return $this->worksheets[$title];
			}
		}

		throw new Exception('Unable to find worksheet');
	}

	function set_worksheet_id($id)
	{
		$this->worksheet_id = $id;
	}

	function find_rows($q)
	{

	}

	function getRows($q = NULL)
	{
		return $this->get_rows($q);
	}

	function get_rows($q = NULL)
	{
		if ( ! isset($this->spreadsheet_id))
		{
			throw new Exception('A spreadsheet name or ID must be defined');
		}

		if ( ! isset($this->worksheet_id))
		{
			throw new Exception('A worksheet name or ID must be defined');
		}
		
		$url = 'https://spreadsheets.google.com/feeds/list/' . $this->spreadsheet_id . '/' . $this->worksheet_id . '/private/full';

		if (is_array($q))
		{
			$params = $q;
		}
		elseif (is_string($q))
		{
			$params = array('sq' => $q);
		}
		else
		{
			$params = NULL;
		}

		$content = $this->get($url, $params);

		if ('parse error' == strtolower(substr($content, 0, 11)))
		{
			throw new Exception('Bad query');
		}

		$xml = new SimpleXmlElement($content);

		$namespaces = $xml->getNameSpaces(TRUE);

		$rows = array();

		foreach ($xml->entry as $entry)
		{
			if (isset($namespaces['gsx']))
			{
				$row = (array)$entry->children($namespaces['gsx']);

				array_push($rows, new Google_Spreadsheet_Row($row));
			}
		}

		return $rows;
	}

	/**
	* Used to make GET requests
	*
	* @access	protected
	* @param	string $url absolute URL
	* @param	string $params optional assocative array of parameters to send with the request
	* @param	string $headers optional headers to send along with the request
	* @return	mixed
	* @see		request()
	*/
	protected function do_get($url ,$params = NULL, $headers = NULL)
	{
		return $this->do_request($url, $params, 'get', $headers);
	}

	/**
	* Used to make POST requests
	*
	* @access	protected
	* @param	string $url absolute URL
	* @param	string $params optional assocative array of parameters to send with the request
	* @param	string $headers optional headers to send along with the request
	* @return	mixed
	* @see		request()
	*/
	protected function do_post($url, $params = NULL, $headers = NULL)
	{
		return $this->do_request($url, $params, 'post', $headers);
	}

	/**
	* Used to make GET or POST requests
	*
	* @access	protected
	* @param	string $url absolute URL
	* @param	string $params optional assocative array of parameters to send with the request
	* @param	string $method optional request method, 'get' or 'post', defaults to 'get'
	* @param	string $headers optional headers to send along with the request
	* @return	mixed
	* @see		get(), post()
	*/
	protected function do_request($url, $params = NULL, $method = 'get', $headers = NULL)
	{
		$ch = curl_init();

		if (is_array($params))
		{
			if ('post' === strtolower($method))
			{
				curl_setopt($ch, CURLOPT_POST, TRUE);

				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}
			else
			{
				$url .= '?' . http_build_query($params);
			}
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		//curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);

		$headers = array_merge(array($this->auth_header), (array)$headers);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$content = curl_exec($ch);

		curl_close($ch);

		return $content;
	}
}

/* end of file */
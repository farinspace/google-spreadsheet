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

class Google_Ss
{
	var $auth_header = NULL;

	var $available_spreadsheets = NULL;

	var $available_worksheets = NULL;

	var $spreadsheet_id = NULL;

	var $worksheet_id = NULL;

	function Google_Ss($a)
	{
		if (is_array($a))
		{
			$this->_login($a['username'], $a['password']);
		}
		else
		{
			throw new Exception('An associative array of parameters is required');
		}
		//if ($ss) $this->useSpreadsheet($ss);
		//if ($ws) $this->useWorksheet($ws);
	}

	function _login($u, $p)
	{
		$url = 'https://www.google.com/accounts/ClientLogin';

		$params = array 
		(
			'accountType' => 'GOOGLE',
			'Email' => $u,
			'Passwd' => $p,
			'service' => 'wise',
			'source' => 'farinspace-Google_Spreadsheet_Helper-1.0',
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$content = curl_exec($ch);
		curl_close($ch);

		$a = preg_split('/\n|\r/', trim($content));

		foreach ($a as $v)
		{
			if ('Auth' == substr($v, 0, 4))
			{
				$auth = trim(str_replace('Auth=', '', $v));

				break;
			}
		}

		$this->auth_header = 'Authorization: GoogleLogin auth=' . $auth;

		return $this->auth_header;
	}

	function get_available_spreadsheets()
	{
		if (isset($this->available_spreadsheets))
		{
			return $this->available_spreadsheets;
		}

		$url = 'https://spreadsheets.google.com/feeds/spreadsheets/private/full';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth_header));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$content = curl_exec($ch);
		curl_close($ch);

		$xml = new SimpleXmlElement($content);

		$this->available_spreadsheets = array();

		foreach ($xml->entry as $entry)
		{
			array_push($this->available_spreadsheets, array
			(
				'id' => basename($entry->id),
				'name' => trim($entry->title),
			));
		}

		return $this->available_spreadsheets;
	}

	function get_available_worksheets()
	{
		if (isset($this->spreadsheet_id))
		{
			if (isset($this->available_worksheets))
			{
				return $this->available_worksheets;
			}

			$url = 'https://spreadsheets.google.com/feeds/worksheets/' . $this->spreadsheet_id . '/private/full';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth_header));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$content = curl_exec($ch);
			curl_close($ch);

			$xml = new SimpleXmlElement($content);

			$this->available_worksheets = array();

			foreach ($xml->entry as $entry)
			{
				array_push($this->available_worksheets, array
				(
					'id' => basename($entry->id),
					'name' => trim($entry->title),
				));
			}

			return $this->available_worksheets;
		}

		throw new Exception('Spreadsheet Name or ID must be defined');
	}

	function use_spreadsheet($name)
	{
		$this->get_available_spreadsheets();

		foreach ($this->available_spreadsheets as $v)
		{
			if ($name == $v['name'])
			{
				$this->set_spreadsheet_id($v['id']);

				break;
			}
		}
	}

	function set_spreadsheet_id($id)
	{
		$this->spreadsheet_id = $id;
	}

	function use_worksheet($name)
	{
		$this->get_available_worksheets();

		foreach ($this->available_worksheets as $v)
		{
			if ($name == $v['name'])
			{
				$this->set_worksheet_id($v['id']);

				break;
			}
		}
	}

	function set_worksheet_id($id)
	{
		$this->worksheet_id = $id;
	}

	function get_rows()
	{
		if (isset($this->spreadsheet_id) AND isset($this->worksheet_id))
		{
			$url = 'https://spreadsheets.google.com/feeds/list/' . $this->spreadsheet_id . '/' . $this->worksheet_id . '/private/full';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth_header));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$content = curl_exec($ch);
			curl_close($ch);

			$xml = new SimpleXmlElement($content);

			$namespaces = $xml->getNameSpaces(TRUE);

			if (isset($namespaces['gsx']))
			{
				$xml = $xml->children($namespaces['gsx']);
			}

			var_dump($xml);
			exit;

			$this->available_worksheets = array();

			foreach ($xml->entry as $entry)
			{
				array_push($this->available_worksheets, array
				(
					'id' => basename($entry->id),
					'name' => trim($entry->title),
				));
			}

			return $this->available_worksheets;
		}

		throw new Exception('Spreadsheet Name or ID must be defined');
	}
}

/* End of file */
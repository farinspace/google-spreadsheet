<?php

	// proposed interface ... the code below are just ideas for an interface, nothing is working

	$ss = new Google_Ss(array
	(
		'username' => 'USERNAME', 
		'password' => 'PASSWORD', 
		'spreadsheet' => 'My Spreadsheet'
	));

	// getting wroksheets

	$worksheets = $ss->get_worksheets();

	$ws = $ss->get_worksheet('Sheet1');

	// getting rows

	$rows = $ws->get_rows();

	$rows = $ss->get_worksheet('Sheet1')->get_rows();


	// deleting a row

	$ws->delete_row($rows[0]);

	$ss->get_worksheet('Sheet1')->delete_row($rows[0]);

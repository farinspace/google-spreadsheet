<?php

// goals:
// use php5 oop
// dont make interface overly complex
// make it fast

require_once 'Google_Spreadsheet.php';

$ss = new Google_Spreadsheet('USER', 'PASS');

$ss->use_spreadsheet('My Spreadsheet');

//$ss->use_worksheet()->get_rows();
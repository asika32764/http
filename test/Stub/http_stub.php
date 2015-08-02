<?php
/**
 * Part of Asika\Http project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

$globals = array();
$_SERVER['HTTP_HOST'];

foreach ($GLOBALS as $key => $value)
{
	if ($key == 'GLOBALS' || $key == 'globals' || $key == 'value')
	{
		continue;
	}

	$globals[$key] = $value;
}

parse_str(file_get_contents('php://input'), $globals['data']);

header('Content-Type: text/json');
echo json_encode($globals);

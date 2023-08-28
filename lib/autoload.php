<?php
/**
 * @param $class
 */
function reports_autoload($class) {
	$class_arr = explode('\\', $class);

	if (count ($class_arr) > 1) {
		$class = implode('/', $class_arr);
	}

	include_once __DIR__ . "/$class.php";
}

spl_autoload_register('reports_autoload');
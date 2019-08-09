<?php

set_include_path(
	'.' . PATH_SEPARATOR .
	'class/' . PATH_SEPARATOR .
	'conf/'. PATH_SEPARATOR .
	'data/'. PATH_SEPARATOR .
	get_include_path()
);

function __autoload ($__classe) {
	include_once $__classe . '.class.php';
}

?>
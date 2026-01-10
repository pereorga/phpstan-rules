<?php

$snake_case_variable = 'valid';
$another_valid_var = 123;
$simple = true;
$with_numbers_456 = [];
$a = null;

// PHP superglobals should be allowed
$result = $_GET['key'];
$post_data = $_POST;
$session = $_SESSION;
$server_info = $_SERVER;
$cookies = $_COOKIE;
$request = $_REQUEST;
$env = $_ENV;
$files = $_FILES;
$globals = $GLOBALS;

<?php

function sanitizeInput(?string $data): string
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
	return $data;
}

foreach ($_GET as $key => $value) {
	$_GET[$key] = sanitizeInput($value);
}

foreach ($_POST as $key => $value) {
	$_POST[$key] = sanitizeInput($value);
}

<?php

require_once("connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$idRevenda = (isset($_POST['dadosRevenda'])) ? $_POST['dadosRevenda'] : '';

	if (validaRevenda($idRevenda) == true) {

		header("Location: principal.php");

	} else {

		expulsaVisitante();

	}
	
}

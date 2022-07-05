<?php

function capturaNumeroTel (string $formatoNumTel, string $arquivo, $qtde=NULL) {

	if($formatoNumTel == '00000000') {
		$formaNumTel = "([0-9]{8,11})";
		preg_match_all($formaNumTel,$arquivo,$numCapturado);

		if($qtde == 1) {
			if(empty($numCapturado[0][1])) {
				return NULL;
			}else {
				return $numCapturado[0][1];
			}
		}else {
			if(empty($numCapturado[0][0])) {
				return NULL;
			}else {
				return $numCapturado[0][0];
			}
		}
	}
}

function capturaData2 (string $formatoData, string $arquivo, $qtde=NULL) {

	$formatoData = strtoupper($formatoData);

	if($formatoData == 'DD/MM/AAAA') {
		$formaData = "([0-9]{2}\/[0-9]{2}\/[0-9]{4})";
		preg_match_all($formaData,$arquivo,$dataCapturado);

		if($qtde == 1) {
			if(empty($dataCapturado[0][1])) {
				return NULL;
			}else {
				return $dataCapturado[0][1];
			}
		}else {
			if(empty($dataCapturado[0][0])) {
				return NULL;
			}else {
				return $dataCapturado[0][0];
			}
		}
	}
	if($formatoData == 'MM/DD/AAAA') {
		$formaData = "([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4})";
		preg_match_all($formaData,$arquivo,$dataCapturado);

		if($qtde == 1) {
			if(empty($dataCapturado[0][1])) {
				return NULL;
			}else {
				return $dataCapturado[0][1];
			}
		}else {
			if(empty($dataCapturado[0][0])) {
				return NULL;
			}else {
				return $dataCapturado[0][0];
			}
		}
	}
	if($formatoData == 'AAAA-MM-DD') {
		$formaData = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
		preg_match_all($formaData,$arquivo,$dataCapturado);

		if($qtde == 1) {
			if(empty($dataCapturado[0][1])) {
				return NULL;
			}else {
				return $dataCapturado[0][1];
			}
		}else {
			if(empty($dataCapturado[0][0])) {
				return NULL;
			}else {
				return $dataCapturado[0][0];
			}
		}
	}
	if($formatoData == 'DD/MM/AA') {
		$formaData = "([0-9]{2}\/[0-9]{2}\/[0-9]{2})";
		preg_match_all($formaData,$arquivo,$dataCapturado);

		if($qtde == 1) {
			if(empty($dataCapturado[0][1])) {
				return NULL;
			}else {
				$dataTratado = substr($dataCapturado[0][1], 0, 2).'/'.substr($dataCapturado[0][1], 3, 2).'/20'.substr($dataCapturado[0][1], 6, 2);
				return $dataTratado;
				// return $dataCapturado[0][1];
			}
		}else {
			if(empty($dataCapturado[0][0])) {
				return NULL;
			}else {
				$dataTratado = substr($dataCapturado[0][0], 0, 2).'/'.substr($dataCapturado[0][0], 3, 2).'/20'.substr($dataCapturado[0][0], 6, 2);
				return $dataTratado;
				// return $dataCapturado[0][0];
			}
		}
	}
	if($formatoData == 'DD-MM-AAAA') {
		//$formaData = "/[0-9]{2}-[0-9]{2}-[0-9]{4}/";
	}
	if($formatoData == 'DD-MM-AA') {
		//$formaData = "/[0-9]{2}-[0-9]{2}-[0-9]{2}/";
	}
	if($formatoData == 'DD.MM.AAAA') {
		//$formaData = "/[0-9]{2}.[0-9]{2}.[0-9]{4}/";
	}
	if($formatoData == 'DD.MM.AA')	{
		//$formaData = "/[0-9]{2}.[0-9]{2}.[0-9]{2}/";
	}
}
//*******************************************************************************************************************

function capturaHora2 (string $formatoHora, string $arquivo, $qtde=NULL) {

	if($formatoHora == '00:00:00') {
		$formaHora = "([0-9]{2}:[0-9]{2}:[0-9]{2})";
		preg_match_all($formaHora,$arquivo,$horaCapturado);

		if($qtde == 1) {
			if(empty($horaCapturado[0][1])) {
				return NULL;
			}else {
				return $horaCapturado[0][1];
			}
		}else {
			if(empty($horaCapturado[0][0])) {
				return NULL;
			}else {
				return $horaCapturado[0][0];
			}
		}
	}
	if($formatoHora == '00M00S') {
		
	}	
}
// --------------------------------------------------------------------------------------------------------------------------

function capturaDuracao2 (string $formatoDuracao, string $arquivo, $qtde=NULL) {

	if($formatoDuracao == '00:00:00') {
		$formaDuracao = "([0-9]{2}:[0-9]{2}:[0-9]{0,2})";
		preg_match_all($formaDuracao,$arquivo,$duracaoCapturado);

		if($qtde == 1) {
			return trim($duracaoCapturado[0][1]);
		}else {
			return trim($duracaoCapturado[0][0]);
		}
	}
	if($formatoDuracao == '0:00:00') {
		$formaDuracao = "([ ][0-9]{1}:[0-9]{2}:[0-9]{0,2})";
		preg_match_all($formaDuracao,$arquivo,$duracaoCapturado);

		if($qtde == 1) {
			return trim($duracaoCapturado[0][1]);
		}else {
			return trim($duracaoCapturado[0][0]);
		}
	}
	if($formatoDuracao == '00m00s') {
		$formaDuracao = "([0-9]{1,3}[m][0-9]{2}[s])";
		preg_match_all($formaDuracao,$arquivo,$duracaoCapturado);

		if($qtde == 1) {
			if(is_null($duracaoCapturado[0][1])) {
				$duracaoCapturado[0][1] = str_replace("m", ":", $duracaoCapturado[0][1]);
				$duracaoCapturado[0][1] = str_replace("s", "", $duracaoCapturado[0][1]);
				return $duracaoCapturado[0][1];
			}else {
				return NULL;
			}
		}else {
			if(empty($duracaoCapturado[0][0])) {
				return NULL;
			}else {
				$duracaoCapturado[0][0] = str_replace("m", ":", $duracaoCapturado[0][0]);
				$duracaoCapturado[0][0] = str_replace("s", "", $duracaoCapturado[0][0]);
				return $duracaoCapturado[0][0];
			}
		}
	}
	if($formatoDuracao == '0h00m00s') {
		$formaDuracao = "([0-9]{1}[h][0-9]{2}[m][0-9]{2}[s])";
		preg_match_all($formaDuracao, $arquivo, $duracaoCapturado);

		if($qtde == 1) {
			if(is_null($duracaoCapturado[0][1])) {
				$duracaoCapturado[0][1] = str_replace(array("h", "m", "s"), array(":", ":", ""), $duracaoCapturado[0][1]);
				return $duracaoCapturado[0][1];
			}else {
				return NULL;
			}
		}else {
			if(empty($duracaoCapturado[0][0])) {
				return NULL;
			}else {
				$duracaoCapturado[0][0] = str_replace(array("h", "m", "s"), array(":", ":", ""), $duracaoCapturado[0][0]);
				return $duracaoCapturado[0][0];
			}
		}
	}
	if($formatoDuracao == '00h00m00s') {
		$formaDuracao = "([0-9]{2}[h][0-9]{2}[m][0-9]{2}[s])";
		preg_match_all($formaDuracao,$arquivo,$duracaoCapturado);

		if($qtde == 1) {
			if(is_null($duracaoCapturado[0][1])) {
				$duracaoCapturado[0][1] = str_replace(array("h", "m", "s"), array(":", ":", ""), $duracaoCapturado[0][1]);
				return $duracaoCapturado[0][1];
			}else {
				return NULL;
			}
		}else {
			if(empty($duracaoCapturado[0][0])) {
				return NULL;
			}else {
				$duracaoCapturado[0][0] = str_replace(array("h", "m", "s"), array(":", ":", ""), $duracaoCapturado[0][0]);
				return $duracaoCapturado[0][0];
			}
		}
	}
}
// --------------------------------------------------------------------------------------------------------------------------

// --------------------------------------------------------------------------------------------------------------------------

// -----[ DETECTA A PRESENÇA DE DADOS ]-----[ EM DESENVOLVIMENTO ]-----

function capturaMBKB (string $arquivo) {

	// ***** CAPTURA MB e KB E CONVERTE PARA MB. *****
	$capturaKBMBGB = 0;
	$linhaKBMBGB = '';

	$encontrarKBMBGB =    array("KB","MB","GB");
	$linhaKBMBGB = utf8_encode(substr($arquivo,0,strlen($arquivo)));
	str_replace($encontrarKBMBGB, "",$linhaKBMBGB,$capturaKBMBGB);

	if($capturaKBMBGB > 0) {

		$formaKB = "(([0-9]{1,3})[\s]{0,}KB)";
		preg_match_all($formaKB,utf8_encode(substr($arquivo,0,strlen($arquivo))),$valorKBCapturado);

		$formaMB = "((([0-9]{0,})[.])*([0-9]{1,3})[\s]{0,}MB)";
		preg_match_all($formaMB,utf8_encode(substr($arquivo,0,strlen($arquivo))),$valorMBCapturado);

		if(empty($valorKBCapturado[0][0])) {
				return NULL;
		}else {
			$kbParaMB = ConverterPrefixoBinario (str_replace($encontrarKBMBGB,"",$valorKBCapturado[0][0]), 'KB', 'MB');
		}
		if(empty($valorMBCapturado[0][0])) {
				return NULL;
		}else {
			$MBParaMB = ConverterPrefixoBinario (str_replace($encontrarKBMBGB,"",$valorMBCapturado[0][0]), 'MB', 'MB');
		}

		$EncontradoMBKB = $MBParaMB + $kbParaMB;

		return $EncontradoMBKB;

	}else {
		return NULL;
	}
}
function detectaGBMBKB (String $arquivo) {
	$encontrarKBMBGB =    array("KB","MB","GB");
	str_replace($encontrarKBMBGB, "",$arquivo,$capturaKBMBGB);
	if($capturaKBMBGB > 0) {
		return TRUE;
	}else {
		return FALSE;
	}
}
// --------------------------------------------------------------------------------------------------------------------------

function capturaValor2 (string $arquivo, $qtde=NULL) {

	$formaValor = "(([+-?]?)[0-9]{1,3}([.]([0-9]{3}))*[,][0-9]{0,2})";
	preg_match_all($formaValor, $arquivo, $valorCapturado);

	if($qtde == 1) {
		if(empty($valorCapturado[0][1])) {
			return NULL;
		}else {
			return str_replace(array(";", ".", ","), array("", "", "."), $valorCapturado[0][1]);
		}
	}else {
		if(empty($valorCapturado[0][0])) {
			return NULL;
		}else {
			return str_replace(array(";", ".", ","), array("", "", "."), $valorCapturado[0][0]);
		}
	}
}
function capturaValorNegativo (string $arquivo, $qtde=NULL) {

	$formaValor = "(([-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2})";
	preg_match_all($formaValor,$arquivo,$valorCapturado);

	if($qtde == 1) {
		if(empty($valorCapturado[0][1])) {
			return NULL;
		}else {
			return $valorCapturado[0][1];
		}
	}else {
		if(empty($valorCapturado[0][0])) {
			return NULL;
		}else {
			return $valorCapturado[0][0];
		}
	}
}
// --------------------------------------------------------------------------------------------------------------------------

function decimo ($tipo, $time, $qtde=NULL) {

	if($tipo == '00:00:00') {

		$timeRetorno = 0.0;
		$timeExplodido = explode(":",$time);

		if($timeExplodido[2] >= 1 and $timeExplodido[2] <= 6) {
			$timeRetorno = 0.1;
		}
		elseif($timeExplodido[2] >= 7 and $timeExplodido[2] <= 12) {
			$timeRetorno = 0.2;
		}
		elseif($timeExplodido[2] >= 13 and $timeExplodido[2] <= 18) {
			$timeRetorno = 0.3;
		}
		elseif($timeExplodido[2] >= 19 and $timeExplodido[2] <= 24) {
			$timeRetorno = 0.4;
		}
		elseif($timeExplodido[2] >= 25 and $timeExplodido[2] <= 30) {
			$timeRetorno = 0.5;
		}
		elseif($timeExplodido[2] >= 31 and $timeExplodido[2] <= 36) {
			$timeRetorno = 0.6;
		}
		elseif($timeExplodido[2] >= 37 and $timeExplodido[2] <= 42) {
			$timeRetorno = 0.7;
		}
		elseif($timeExplodido[2] >= 43 and $timeExplodido[2] <= 48) {
			$timeRetorno = 0.8;
		}
		elseif($timeExplodido[2] >= 49 and $timeExplodido[2] <= 54) {
			$timeRetorno = 0.9;
		}
		elseif ($timeExplodido[2] >= 55 and $timeExplodido[2] <= 59) {
			$timeRetorno = 1.0;
		}else {
			$timeRetorno = 0.0;
		}
		$resultado = ((float)$timeExplodido[0]*60)+((float)$timeExplodido[1])+($timeRetorno);
		return number_format($resultado,1,',','.');
	}
	if($tipo == '00:00') {

		$timeRetorno = 0.0;
		$timeExplodido = explode(":",$time);

		if($timeExplodido[1] >= 1 and $timeExplodido[1] <= 6) {
			$timeRetorno = 0.1;
		}
		elseif($timeExplodido[1] >= 7 and $timeExplodido[1] <= 12) {
			$timeRetorno = 0.2;
		}
		elseif($timeExplodido[1] >= 13 and $timeExplodido[1] <= 18) {
			$timeRetorno = 0.3;
		}
		elseif($timeExplodido[1] >= 19 and $timeExplodido[1] <= 24) {
			$timeRetorno = 0.4;
		}
		elseif($timeExplodido[1] >= 25 and $timeExplodido[1] <= 30) {
			$timeRetorno = 0.5;
		}
		elseif($timeExplodido[1] >= 31 and $timeExplodido[1] <= 36) {
			$timeRetorno = 0.6;
		}
		elseif($timeExplodido[1] >= 37 and $timeExplodido[1] <= 42) {
			$timeRetorno = 0.7;
		}
		elseif($timeExplodido[1] >= 43 and $timeExplodido[1] <= 48) {
			$timeRetorno = 0.8;
		}
		elseif($timeExplodido[1] >= 49 and $timeExplodido[1] <= 54) {
			$timeRetorno = 0.9;
		}
		elseif ($timeExplodido[1] >= 55 and $timeExplodido[1] <= 59) {
			$timeRetorno = 1.0;
		}else {
			$timeRetorno = 0.0;
		}
		$minuto = str_replace("'","",$timeExplodido[0]);
		$resultado = (float)$minuto + $timeRetorno;
		return number_format($resultado,1,',','.');
	}
	if($tipo == '00m00s') {

		$formaDuracao = "([0-9]{1,3}[m][0-9]{2}[s])";
		preg_match_all($formaDuracao, $time, $duracaoCapturado);

		if($qtde == 1) {
			if(is_null($duracaoCapturado[0][1])) {
				$duracaoCapturado[0][1] = str_replace(array("m", "s"), array(":", ""), $duracaoCapturado[0][1]);
				// $duracaoCapturado[0][1] = str_replace("s", "", $duracaoCapturado[0][1]);

				$timeRetorno = 0.0;
				$timeExplodido = explode(":", $duracaoCapturado[0][1]);

				if($timeExplodido[1] >= 1 and $timeExplodido[1] <= 6) {
					$timeRetorno = 0.1;
				}
				elseif($timeExplodido[1] >= 7 and $timeExplodido[1] <= 12) {
					$timeRetorno = 0.2;
				}
				elseif($timeExplodido[1] >= 13 and $timeExplodido[1] <= 18) {
					$timeRetorno = 0.3;
				}
				elseif($timeExplodido[1] >= 19 and $timeExplodido[1] <= 24) {
					$timeRetorno = 0.4;
				}
				elseif($timeExplodido[1] >= 25 and $timeExplodido[1] <= 30) {
					$timeRetorno = 0.5;
				}
				elseif($timeExplodido[1] >= 31 and $timeExplodido[1] <= 36) {
					$timeRetorno = 0.6;
				}
				elseif($timeExplodido[1] >= 37 and $timeExplodido[1] <= 42) {
					$timeRetorno = 0.7;
				}
				elseif($timeExplodido[1] >= 43 and $timeExplodido[1] <= 48) {
					$timeRetorno = 0.8;
				}
				elseif($timeExplodido[1] >= 49 and $timeExplodido[1] <= 54) {
					$timeRetorno = 0.9;
				}
				elseif ($timeExplodido[1] >= 55 and $timeExplodido[1] <= 59) {
					$timeRetorno = 1.0;
				}else {
					$timeRetorno = 0.0;
				}
				$minuto = str_replace("'","",$timeExplodido[0]);
				$resultado = (float)$minuto + $timeRetorno;
				return number_format($resultado,1,',','.');
				// return $duracaoCapturado[0][1];
			}else {
				return NULL;
			}
		}else {
			if(empty($duracaoCapturado[0][0])) {
				return NULL;
			}else {
				$duracaoCapturado[0][0] = str_replace(array("m", "s"), array(":", ""), $duracaoCapturado[0][0]);
				// $duracaoCapturado[0][0] = str_replace("s", "", $duracaoCapturado[0][0]);

				$timeRetorno = 0.0;
				$timeExplodido = explode(":", $duracaoCapturado[0][0]);

				if($timeExplodido[1] >= 1 and $timeExplodido[1] <= 6) {
					$timeRetorno = 0.1;
				}
				elseif($timeExplodido[1] >= 7 and $timeExplodido[1] <= 12) {
					$timeRetorno = 0.2;
				}
				elseif($timeExplodido[1] >= 13 and $timeExplodido[1] <= 18) {
					$timeRetorno = 0.3;
				}
				elseif($timeExplodido[1] >= 19 and $timeExplodido[1] <= 24) {
					$timeRetorno = 0.4;
				}
				elseif($timeExplodido[1] >= 25 and $timeExplodido[1] <= 30) {
					$timeRetorno = 0.5;
				}
				elseif($timeExplodido[1] >= 31 and $timeExplodido[1] <= 36) {
					$timeRetorno = 0.6;
				}
				elseif($timeExplodido[1] >= 37 and $timeExplodido[1] <= 42) {
					$timeRetorno = 0.7;
				}
				elseif($timeExplodido[1] >= 43 and $timeExplodido[1] <= 48) {
					$timeRetorno = 0.8;
				}
				elseif($timeExplodido[1] >= 49 and $timeExplodido[1] <= 54) {
					$timeRetorno = 0.9;
				}
				elseif ($timeExplodido[1] >= 55 and $timeExplodido[1] <= 59) {
					$timeRetorno = 1.0;
				}else {
					$timeRetorno = 0.0;
				}
				$minuto = str_replace("'","",$timeExplodido[0]);
				$resultado = (float)$minuto + $timeRetorno;
				return number_format($resultado,1,',','.');
				// return $duracaoCapturado[0][0];
			}
		}
	}
//--------------
	if($tipo == '0h00m00s') {

		$formaDuracao = "([0-9]{1,3}[h][0-9]{1,3}[m][0-9]{2}[s])";
		preg_match_all($formaDuracao, $time, $duracaoCapturado);

		if($qtde == 1) {
			if(is_null($duracaoCapturado[0][1])) {
				$duracaoCapturado[0][1] = str_replace(array("h", "m", "s"), array(":", ":", ""), $duracaoCapturado[0][1]);

				$timeRetorno = 0.0;
				$timeExplodido = explode(":", $duracaoCapturado[0][1]);
				
				echo '<br/>'.$timeExplodido[2].' - '.$timeExplodido[1].' - '.$timeExplodido[0];
				exit();

				if($timeExplodido[1] >= 1 and $timeExplodido[1] <= 6) {
					$timeRetorno = 0.1;
				}
				elseif($timeExplodido[1] >= 7 and $timeExplodido[1] <= 12) {
					$timeRetorno = 0.2;
				}
				elseif($timeExplodido[1] >= 13 and $timeExplodido[1] <= 18) {
					$timeRetorno = 0.3;
				}
				elseif($timeExplodido[1] >= 19 and $timeExplodido[1] <= 24) {
					$timeRetorno = 0.4;
				}
				elseif($timeExplodido[1] >= 25 and $timeExplodido[1] <= 30) {
					$timeRetorno = 0.5;
				}
				elseif($timeExplodido[1] >= 31 and $timeExplodido[1] <= 36) {
					$timeRetorno = 0.6;
				}
				elseif($timeExplodido[1] >= 37 and $timeExplodido[1] <= 42) {
					$timeRetorno = 0.7;
				}
				elseif($timeExplodido[1] >= 43 and $timeExplodido[1] <= 48) {
					$timeRetorno = 0.8;
				}
				elseif($timeExplodido[1] >= 49 and $timeExplodido[1] <= 54) {
					$timeRetorno = 0.9;
				}
				elseif ($timeExplodido[1] >= 55 and $timeExplodido[1] <= 59) {
					$timeRetorno = 1.0;
				}else {
					$timeRetorno = 0.0;
				}
				$minuto = str_replace("'","",$timeExplodido[0]);
				$resultado = (float)$minuto + $timeRetorno;
				return number_format($resultado,1,',','.');
				// return $duracaoCapturado[0][1];
			}else {
				return NULL;
			}
		}else {
			if(empty($duracaoCapturado[0][0])) {
				return NULL;
			}else {
				$duracaoCapturado[0][0] = str_replace(array("h", "m", "s"), array(":", ":", ""), $duracaoCapturado[0][0]);
				// $duracaoCapturado[0][0] = str_replace("s", "", $duracaoCapturado[0][0]);

				$timeRetorno = 0.0;
				$timeExplodido = explode(":", $duracaoCapturado[0][0]);
				
				// echo '<br/>'.$timeExplodido[2].' - '.$timeExplodido[1].' - '.$timeExplodido[0];
				// exit();

				if($timeExplodido[2] >= 1 and $timeExplodido[2] <= 6) {
					$timeRetorno = 0.1;
				}
				elseif($timeExplodido[2] >= 7 and $timeExplodido[2] <= 12) {
					$timeRetorno = 0.2;
				}
				elseif($timeExplodido[2] >= 13 and $timeExplodido[2] <= 18) {
					$timeRetorno = 0.3;
				}
				elseif($timeExplodido[2] >= 19 and $timeExplodido[2] <= 24) {
					$timeRetorno = 0.4;
				}
				elseif($timeExplodido[2] >= 25 and $timeExplodido[2] <= 30) {
					$timeRetorno = 0.5;
				}
				elseif($timeExplodido[2] >= 31 and $timeExplodido[2] <= 36) {
					$timeRetorno = 0.6;
				}
				elseif($timeExplodido[2] >= 37 and $timeExplodido[2] <= 42) {
					$timeRetorno = 0.7;
				}
				elseif($timeExplodido[2] >= 43 and $timeExplodido[2] <= 48) {
					$timeRetorno = 0.8;
				}
				elseif($timeExplodido[2] >= 49 and $timeExplodido[2] <= 54) {
					$timeRetorno = 0.9;
				}
				elseif ($timeExplodido[2] >= 55 and $timeExplodido[2] <= 59) {
					$timeRetorno = 1.0;
				}else {
					$timeRetorno = 0.0;
				}
				$hora = $timeExplodido[0] / 60;
				$minuto = $timeExplodido[1];
				$resultado = ((float)$hora + (float)$minuto) + $timeRetorno;
				return number_format($resultado,1,',','.');
				// return $duracaoCapturado[0][0];
			}
		}
	}
}


// -----[ DETECTA A PRESENÇA DE NUMERO, A PARTIR DA POSIÇÃO INICIAL ATÉ UM DETERMINADA POSIÇÃO ]-----
//
// RETORNA APENAS NUMERO.
//
//
function capturaApenasNumero (string $arquivo) {

	$formaNumero = "/[0-9]{1}/";

	$numeroEncontrado = '';
	for($forIniciaColeta = 0; $forIniciaColeta <= strlen($arquivo); $forIniciaColeta++) {
		if(preg_match($formaNumero,substr($arquivo,$forIniciaColeta,1))) {
			$numeroEncontrado .= substr($arquivo,$forIniciaColeta,1);
		}
	}
	return trim($numeroEncontrado);
}
function capturaNumeroDiscado (string $arquivo, $qtde=NULL) {

	$formaNumeroDiscado = "([\s]+[0-9A-Z\-]{8,})";

	preg_match_all($formaNumeroDiscado, $arquivo, $numeroDiscadoCapturado);

	if($qtde == 1) {
		if(empty($numeroDiscadoCapturado[0][1])) {
			return NULL;
		}else {
			return $numeroDiscadoCapturado[0][1];
		}
	}else {
		if(empty($numeroDiscadoCapturado[0][0])) {
			return NULL;
		}else {
			return $numeroDiscadoCapturado[0][0];
		}
	}
}
// --------------------------------------------------------------------------------------------------------------------------

function ConverterPrefixoBinario ($Valor, $UnidadeOrigem, $UnidadeDestino, $Precisão=NULL) {
	$Unidades = array (
		'B' => 1, 
		'KB' => 1000, 'MB' => 1000000, 'GB' => 1000000000, 'TB' => 1000000000,
		'KiB' => 1024, 'MiB' => 1048576, 'GiB' => 1073741824, 'TiB' => 1099511627776
	);

	$ValorBytes = $Valor * $Unidades [$UnidadeOrigem];

	return $ValorBytes / $Unidades [$UnidadeDestino];
}

$listaEspacos = array(" ","  ","   ","    ","     ","      ","       ","        ","        ","          ");

?>
<?php

set_time_limit(1440);

function detectaOperadora(string $nomeArquivo) {

	$arquivo = file($nomeArquivo);
	if($arquivo == false) die('O arquivo não existe.');

	$quantidadeTotalLinhas = count($arquivo);
	$passaUm = 0;
	$semOperadora = 1;
	$flagPontoComunicacao = 0;
	
	$retorno1 = 0;

	for ($for1 = 0; $for1 < $quantidadeTotalLinhas; $for1++) {

		//$cnpjOperadora1 = '02.558.157'; // VIVO
		$cnpjOperadora1 = '02.558.888'; // VIVO
		$cnpjOperadora2 = '04.206.050'; // TIM
		$cnpjOperadora3 = '02.558.157'; // NEXTEL
		$cnpjOperadora4 = '40.432.544'; // CLARO
		$cnpjOperadora5 = '76.535.764'; // OI

		$cnpjOperadora6 = '71.208.516'; // ALGAR
		$cnpjOperadora61 = '04.622.116'; // ALGAR MULTIMIDIA S/A

		$cnpjOperadora7 = '00.108.786'; // NET-CLARO
		$cnpjOperadora8 = '02.558.124'; // EMBRATEL-CLARO

		$flagPontoComunicacaoOperadora = "((Ponto)([\s]+)(de)([\s]+)([\w\W]+))"; // EXPRESSÃO PARA IDENTIFICAR SE A FATURA É PONTO DE COMUNICAÇÃO.
		//preg_match_all($flagPontoComunicacaoOperadora, $arquivo[$for1], $FLAG_capturado);
		//echo '</p>FLAG.: '.$FLAG_capturado[0][0].'</p></p>';

		if(preg_match($flagPontoComunicacaoOperadora, utf8_encode($arquivo[$for1]))) {
			$flagPontoComunicacao = 1;
			// $retorno1 = 22;
		}

		$flagCNPJOperadora = "([0-9]{2}[.][0-9]{3}[.][0-9]{3}\/[0-9]{4}[-][0-9]{2})"; // EXPRESSÃO PARA IDENTIFICAR CNPJ.
		
		if(preg_match($flagCNPJOperadora,$arquivo[$for1]) AND $passaUm < 2) {

			preg_match_all($flagCNPJOperadora, $arquivo[$for1], $CNPJ_capturado);
			var_dump($CNPJ_capturado);
			
			//-----------------------------------------------------------------------------------------------------
			// echo '</p>CNPJ Operadora.: '.$CNPJ_capturado[0][0].'</p></p>';
			// echo '<br/>ARQ TXT.: '.$nomeArquivo;
			// exit();
			//-----------------------------------------------------------------------------------------------------

			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora1 AND $flagPontoComunicacao == 0) {	// VIVO
				$retorno = 1;
			}
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora1 AND $flagPontoComunicacao == 1) {	// VIVO
				$retorno = 11;
			}
			//-----------------------------------------------------------------------------------------------------
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora2) {	// TIM
				$retorno = 2;
			}
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora3) {	// NEXTEL
				return  3;
			}
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora4) {	// CLARO
				$retorno = 4;
			}
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora5) {	// OI
				$retorno = 5;
			}
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora6) {	// ALGAR
				$retorno = 6;
			}
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora61) {	// ALGAR
				$retorno = 6;
			}
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora7) {	// NET-CLARO
				$retorno = 7;
			}
			if(substr($CNPJ_capturado[0][0], 0, 10) == $cnpjOperadora8) {	// EMBRATEL-CLARO
				$retorno = 8;
			}
			$passaUm += 1;
		}else {
			$retorno = 99;
		}

	}
	return $retorno;
}
?>

<?php

include_once("funcoesParaFaturas2.php");

set_time_limit(1440);

function converteFaturaALGARFixo2018(string $arquivoConvertidoTXT) {

	//ABRE O ARQUIVO TXT
	$ponteiro = fopen ($arquivoConvertidoTXT,"r");
	$fp = fopen($arquivoConvertidoTXT.".csv", "w+");

	$arquivo = file($arquivoConvertidoTXT);

	if($arquivo == false) die('O arquivo não existe 1.');

	if($ponteiro == false) die('O arquivo não existe 2.');

	if($fp == false) die('O arquivo não foi criado.');

	$capturar = 0;
	$conta = 0;
	$conta2 = 0;
	$total_negado = 0;

	$resultado1 = 0;
	$resultado2 = 0;

	$verifica_detalhamento = 0;

	$total_itens_ignorado = 0;

	$inicio = 0;
	$capturar01 = 0;
	$capturar0101 = 0;
	$capturar02 = 0;
	$capturar03 = 0;
	$capturar04 = 0;
	$capturar05 = 0;
	$capturar06 = 0;
	$capturar07 = 0;

	$iniciaCapturaPlanos = 0;
	$itensColetaDetalhamento = 0;
	$capturaLigacaoIniciada = 0;
	$contadorLigacao1 = 0;
	$contadorLigacao2 = 0;
	$contadorLigacao3 = 0;

	GLOBAL $avisaTipoCaptura;
			$avisaTipoCaptura = 0;
			
			
	GLOBAL $descricaoTipo1;

	$totalFatura = 0;

	$tudo =  'OPERADORA;'
			.'NOME DA ORIGEM;'
			.'NUMERO TELEFONE;'
			.'RAMAL ASSOCIADO;'
			.'DATA LIGACAO;'
			.'HORARIO LIGACAO;'
			.'TELEFONE CHAMADO;'
			.'TRONCO;'
			.'DESCRICAO;'
			.'DURACAO/UNID.;'
			.'TARIFA;'
			.'DEPTO.;'
			.'CONTA DE FATURA;'
			.'MES_REF;'
			."\r\n";

	if (!fwrite($fp, $tudo)) {
			print "Erro escrevendo no arquivo ou esta sendo usado por outro programa.";
			exit;
	}
	$contou = 0;

	$numero_fatura = '0'; // É melhor informar o código da fatura em vez de tentar capturar.
	$listaEspacos = array(" ","  ","   ","    ","     ","      ","       ","        ","        ","          ");
	$alertaDescricaoNaocadastrado = 0;

	while (!feof ($ponteiro)) {

		//LÊ UMA LINHA DO ARQUIVO
		$linha = fgets($ponteiro,4096);

		$conta2 = $conta;

		// CAPTURA O PERIODO DE UTILIZAÇÃO DOS SERVIÇOS.
		$flagPeriodoUtilizouServico = "(EMISSÃO[\s]+DESTA[\s]+CONTA:([\s:space]?)[\w\W])";
		if(preg_match($flagPeriodoUtilizouServico,$arquivo[$conta])) {
			$periodoUtilizacao = capturaData2('DD/MM/AA', substr($arquivo[$conta2],0,100));
		}

		if(utf8_encode(trim(substr($arquivo[$conta],122,22))) == 'Código  da  Conta:') {
			$codigoCliente = capturaApenasNumero(trim(substr($arquivo[$conta2],145,20)));
		}

		//===============================================================================================================================================
		str_replace(array("valortotaldaconta","Valortotaldaconta"),"", str_replace($listaEspacos,"",utf8_encode(substr($arquivo[$conta2-1],0,180))), $flagTotalPagar);
		if($flagTotalPagar > 0) {
			$capturaServicosContratados = 1;
			$valorTotalPagar = "([\s]+(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

			$conta2 = $conta;

			if(preg_match($valorTotalPagar,$arquivo[$conta])) {
				preg_match_all($valorTotalPagar,$arquivo[$conta],$pegaTotalPagar);
				$totalFatura = str_replace(array(".",","), array("","."), $pegaTotalPagar[0][0]);
			}
			if(preg_match($valorTotalPagar,substr($arquivo[$conta2+1],0,1024))) {
				preg_match_all($valorTotalPagar,substr($arquivo[$conta2+1],0,1024),$pegaTotalPagar);
				$totalFatura = str_replace(array(".",","), array("","."), $pegaTotalPagar[0][0]);
			}
		}
		//===============================================================================================================================================

		// CAPTURA O NÚMERO QUE UTILIZOU OS SERVIÇOS.
		$flagPeriodoUtilizouServico = "(TELEFONE[\s]+FIXO[\s]+:([\s:space]?)[\w\W])";
		if(preg_match($flagPeriodoUtilizouServico,$arquivo[$conta])) {
			$numero_origem = capturaApenasNumero(trim(substr($linha,22,25))); // NUMERO DESTINO
		}

		//===============================================================================================================================================

		// TIPO LIGAÇÕES 1 - OK
		$formatoDataLigacao1 = '([0-9]{2}.[0-9]{2}.[0-9]{4})';
		$formatoHoraLigacao1 = '([0-9]{2}h[0-9]{2}m[0-9]{2}s)';
		$formatoDuracaoLigacao1 = "(([0-9]{2}h[0-9]{2}m[0-9]{2}s))"; // 00:30

		$formatoLigacao1 = "({$formatoDataLigacao1}([\s]+){$formatoHoraLigacao1}([\s]+){$formatoDuracaoLigacao1}([\w\W]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

		if(preg_match($formatoLigacao1, $arquivo[$conta])) {

			$capturar01 = 1;
			$conta2 = $conta;

			if($contadorLigacao1 == 0) {

				$descicaoLigacao1 = array(
								"LIGAÇÕESLOCAIS"						=> "LIGAÇÕES LOCAIS",
								"LIGAÇÕESNACIONAIS" 					=> "LIGAÇÕES NACIONAIS",
								"LIGAÇÕESPARACELULAR"					=> "LIGAÇÕES PARA CELULAR",
								"LIGAÇÕESRECEBIDASDECELULAREMROAMING"	=> "LIGAÇÕES RECEBIDAS DE CELULAR EM ROAMING"
								);

				$linhaEspacosRemovidosLinha1 = '';
				$linhaEspacosRemovidosLinha1 = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2],0,183)));

				$copia_descicaoLigacao1 = $descicaoLigacao1;

				foreach($copia_descicaoLigacao1 as $novaDescLigacao1) {

					str_replace(key($copia_descicaoLigacao1), "", $linhaEspacosRemovidosLinha1, $encontrouDescricaoLigacao);

					// if(key($copia_descicaoLigacao1) == $linhaEspacosRemovidosLinha1) {
					if($encontrouDescricaoLigacao > 0) {
						$descricaoLigacao1 = $novaDescLigacao1;
						$encontrouLigacao1 = 1;
					}
					next($copia_descicaoLigacao1);
				}

				// PARA DESCIÇÕES NÃO CADASTRADAS.
				if ($encontrouLigacao1 == 0 AND $contadorLigacao1 == 0) {

					$descPacote1 = utf8_encode(substr($arquivo[$conta2-2],0,183)).' - '.$conta;
					$todosdescricoesNaoCadastrados["{$descPacote1}"] = 1;
					$alertaDescricaoNaocadastrado = 1;
				}
			}
			$contadorLigacao1 += 1;
		}
		else {
			$encontrouLigacao1 = 0;
			$contadorLigacao1 = 0;
		}

		if($capturar01 == 1) {

			if(strlen(trim($numero_origem)) > 0) {
				$arrayCampos[1] = $numero_origem; // NUMERO
			}elseif(strlen(trim($codigoCliente)) > 0) {
				$arrayCampos[1] = $codigoCliente;
			}else {
				$arrayCampos[1] = $numero_fatura;
			}

			$arrayCampos[2] = str_replace(".", "/", capturaData2 ('DD.MM.AAAA', substr($linha,0,30)));
			$arrayCampos[3] = str_replace(array("h","m","s"), ":", capturaHora2('00h00m00s',substr($linha,0,25)));
			$arrayCampos[4] = $descricaoLigacao1; //DESCRIÇÂO
			$arrayCampos[5] = capturaApenasNumero(trim(substr($linha,105,20))); // NUMERO DESTINO
			$arrayCampos[6] = ''; // OPERADORA
			$arrayCampos[7] = decimo ("00h00m00s", substr($linha,0,50), 1);
			// $arrayCampos[7] = capturaHora2('00h00m00s',substr($linha,10,30), 1);
			$arrayCampos[8] = ''; // QUANTIDADE
			$arrayCampos[9] = ''; // MEGA
			$arrayCampos[10] = capturaValor2(substr($arquivo[$conta],-20)); // VALOR
			
			$valor = str_replace(array(".",","), array("","."), $arrayCampos[10]);
			$totalCapturado += $valor;

			$tudo01 = 
			 ';'	// OPERADORA
			.$codigoCliente.';'	// NOME DA ORIGEM
			.$arrayCampos[1].';'	// NUMERO TELEFONE
			.$arrayCampos[1].';'	// RAMAL ASSOCIADO
			.$arrayCampos[2].';'	// DATA LIGACAO
			.$arrayCampos[3].';'	// HORA LIGACAO
			.$arrayCampos[5].';'	// TELEFONE CHAMADO
			.$arrayCampos[1].';'	// TRONCO
			//.$arrayCampos[4].';'	// DESCRICAO
			.iconv("UTF-8", "Windows-1252",$arrayCampos[4]).';'	// DESCRICAO
			.$arrayCampos[7].';'	// DURACAO
			.$arrayCampos[10].';'	// TARIFA
			.';'	// DEPTO.
			.';'	// CONTA DE FATURA
			.';'	// MES_REF
			."\r\n";

			if (!fwrite($fp, $tudo01)) { 
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
			$tudo01 = '';

		} // FINAL TIPO1
		$capturar01 = 0;
		// ----------------------------------------------------------------------------------------------------------------------------------------------------------------

		// TIPO LIGAÇÕES 1 - OK
		$formatoDataLigacao2 = '([0-9]{2}.[0-9]{4})';
		$formatoDuracaoLigacao2 = "(([0-9]{2,6}h[0-9]{2}m[0-9]{2}s))"; // 00:30

		$formatoLigacao2 = "({$formatoDataLigacao2}([\s]{10,}){$formatoDuracaoLigacao2}([\w\W]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

		if(preg_match($formatoLigacao2, $arquivo[$conta])) {

			$capturar0101 = 1;
			$conta2 = $conta;

			if($contadorLigacao2 == 0) {

				$descicaoLigacao2 = array(
								"LIGAÇÕESLOCAIS"								=> "LIGAÇÕES LOCAIS",
								"LIGAÇÕESNACIONAIS" 							=> "LIGAÇÕES NACIONAIS",
								"LIGAÇÕESPARACELULAR"							=> "LIGAÇÕES PARA CELULAR",
								"LIGAÇÕESRECEBIDASDECELULAREMROAMING"			=> "LIGAÇÕES RECEBIDAS DE CELULAR EM ROAMING"
								);

				$linhaEspacosRemovidosLinha2 = '';
				$linhaEspacosRemovidosLinha2 = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2],0,183)));

				$copia_descicaoLigacao2 = $descicaoLigacao2;

				foreach($copia_descicaoLigacao2 as $novaDescLigacao2) {

					str_replace(key($copia_descicaoLigacao2), "", $linhaEspacosRemovidosLinha2, $encontrouDescricaoLigacao);

					// if(key($copia_descicaoLigacao1) == $linhaEspacosRemovidosLinha1) {
					if($encontrouDescricaoLigacao > 0) {
						$descricaoLigacao2 = $novaDescLigacao2;
						$encontrouLigacao2 = 1;
					}
					next($copia_descicaoLigacao2);
				}

				// PARA DESCIÇÕES NÃO CADASTRADAS.
				if ($encontrouLigacao2 == 0 AND $contadorLigacao2 == 0) {

					$descPacote2 = utf8_encode(substr($arquivo[$conta2-2],0,183)).' - '.$conta;
					$todosdescricoesNaoCadastrados["{$descPacote2}"] = 1;
					$alertaDescricaoNaocadastrado = 1;
				}
			}
			$contadorLigacao2 += 1;
		}
		else {
			$encontrouLigacao2 = 0;
			$contadorLigacao2 = 0;
		}

		if($capturar0101 == 1) {

			if(strlen(trim($numero_origem)) > 0) {
				$arrayCampos[1] = $numero_origem; // NUMERO
			}elseif(strlen(trim($codigoCliente)) > 0) {
				$arrayCampos[1] = $codigoCliente;
			}else {
				$arrayCampos[1] = $numero_fatura;
			}

			$arrayCampos[2] = str_replace(".", "/", capturaData2 ('MM.AAAA', substr($linha,0,30)));
			$arrayCampos[3] = str_replace(array("h","m","s"), ":", capturaHora2('00h00m00s',substr($linha,0,25)));
			$arrayCampos[4] = $descricaoLigacao2; //DESCRIÇÂO
			$arrayCampos[5] = capturaApenasNumero(trim(substr($linha,105,20))); // NUMERO DESTINO
			$arrayCampos[6] = ''; // OPERADORA
			$arrayCampos[7] = decimo ("00h00m00s", substr($linha,0,50), 1);
			// $arrayCampos[7] = capturaHora2('00h00m00s',substr($linha,10,30), 1);
			$arrayCampos[8] = ''; // QUANTIDADE
			$arrayCampos[9] = ''; // MEGA
			$arrayCampos[10] = capturaValor2(substr($arquivo[$conta],-20)); // VALOR
			
			$valor = str_replace(array(".",","), array("","."), $arrayCampos[10]);
			$totalCapturado += $valor;

			$tudo0101 = 
			 ';'	// OPERADORA
			.$codigoCliente.';'	// NOME DA ORIGEM
			.$arrayCampos[1].';'	// NUMERO TELEFONE
			.$arrayCampos[1].';'	// RAMAL ASSOCIADO
			.$arrayCampos[2].';'	// DATA LIGACAO
			.$arrayCampos[3].';'	// HORA LIGACAO
			.$arrayCampos[5].';'	// TELEFONE CHAMADO
			.$arrayCampos[1].';'	// TRONCO
			//.$arrayCampos[4].';'	// DESCRICAO
			.iconv("UTF-8", "Windows-1252",$arrayCampos[4]).';'	// DESCRICAO
			.$arrayCampos[7].';'	// DURACAO
			.$arrayCampos[10].';'	// TARIFA
			.';'	// DEPTO.
			.';'	// CONTA DE FATURA
			.';'	// MES_REF
			."\r\n";

			if (!fwrite($fp, $tudo0101)) { 
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
			$tudo0101 = '';

		} // FINAL TIPO1
		$capturar0101 = 0;







		// -------------------------[ TIPO MENSAGENS SMS ]-------------------------
		// ----------[ INICIA A CAPTURA DOS SMS E DESCRIÇÃO DOS SERVIÇOS]----------

		// $formatoDataLigacao2 = '[0-9]{2}\/[0-9]{2}\/[0-9]{4}'; // DD/MM/AAAA
		// $formatoHoraLigacao2 = '[0-9]{2}:[0-9]{2}:[0-9]{2}';   // 00:00:00

		// $formatoLigacao2 = "(({$formatoDataLigacao2}[\s]+{$formatoHoraLigacao2})((.+[0-9]{2,15}[\s]+[\W\w]{4,10}[\s]+[\s]{20})|(.+[\s]+[\s]{30}[0-9]{1}[\s]+[\s]{30}))(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

		// if(preg_match($formatoLigacao2, $arquivo[$conta])) {

			// $capturar03 = 1;
			// $conta2 = $conta;

			// if($contadorLigacao3 == 0) {

				// $descicaoLigacao2 = array(
								// "SMSNextel"							=> "SMS Nextel",
								// "OutrasChamadas"					=> "Outras Chamadas",
								// "ServiçosdeTerceiroAssinatura"		=> "Serviços de Terceiro Assinatura"
								// );

				// $linhaEspacosRemovidosLinha2 = '';
				// $linhaEspacosRemovidosLinha2 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2],0,183))));

				// $copia_descicaoLigacao2 = $descicaoLigacao2;

				// foreach($copia_descicaoLigacao2 as $novaDescLigacao2) {

					// if(key($copia_descicaoLigacao2) == $linhaEspacosRemovidosLinha2) {
						// $descricaoLigacao2 = $novaDescLigacao2;
						// $encontrouLigacao2 = 1;
					// }
					// next($copia_descicaoLigacao2);
				// }

				// // PARA DESCIÇÕES NÃO CADASTRADAS.
				// if ($encontrouLigacao2 == 0 AND $contadorLigacao3 == 0) {

					// $descLigacao2 = utf8_encode(substr($arquivo[$conta2-2],0,183)).' - '.$conta;
					// $todosdescricoesNaoCadastrados["{$descLigacao2}"] = 1;
					// $alertaDescricaoNaocadastrado = 1;
				// }
			// }
			// $contadorLigacao3 += 1;
		// }
		// else {
			// $encontrouLigacao2 = 0;
			// $contadorLigacao3 = 0;
		// }

		// if($capturar03 == 1) {

			// if(strlen(trim($numero_origem)) > 0) {
				// $arrayCampos3[1] = $numero_origem; // NUMERO
			// }elseif(strlen(trim($codigoCliente)) > 0) {
				// $arrayCampos3[1] = $codigoCliente;
			// }else {
				// $arrayCampos3[1] = $numero_fatura;
			// }

			// $arrayCampos3[2] = capturaData2('DD/MM/AAAA', substr($linha,0,20));
			// $arrayCampos3[3] = capturaHora2('00:00:00', substr($linha,20,25));
			// $arrayCampos3[4] = $descricaoLigacao2; //DESCRIÇÂO
			// $arrayCampos3[5] = capturaApenasNumero(trim(substr($linha,95,20))); // NUMERO DESTINO
			// $arrayCampos3[6] = ''; // OPERADORA
			// $arrayCampos3[7] = ''; // MINUTOS
			// $arrayCampos3[8] = ''; // QUANTIDADE
			// $arrayCampos3[9] = ''; // MEGA
			// $arrayCampos3[10] = capturaValor2(substr($arquivo[$conta],-13)); // VALOR

			// $valor = str_replace(array(".",","), array("","."), $arrayCampos3[10]);
			// $totalCapturado += $valor;

			// $tudo3 = 
			 // ';'	// OPERADORA
			// .$codigoCliente.';'	// NOME DA ORIGEM
			// .$arrayCampos3[1].';'	// NUMERO TELEFONE
			// .$arrayCampos3[1].';'	// RAMAL ASSOCIADO
			// .$arrayCampos3[2].';'	// DATA LIGACAO
			// .$arrayCampos3[3].';'	// HORA LIGACAO
			// .$arrayCampos3[5].';'	// TELEFONE CHAMADO
			// .$arrayCampos3[1].';'	// TRONCO
			// .$arrayCampos3[4].';'	// DESCRICAO
			// .$arrayCampos3[7].';'	// DURACAO
			// .$arrayCampos3[10].';'	// TARIFA
			// .';'	// DEPTO.
			// .';'	// CONTA DE FATURA
			// .';'	// MES_REF
			// ."\r\n";

			// if (!fwrite($fp, $tudo3)) {
				// print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				// exit;
			// }
		// }
		// $capturar03 = 0;
		// -----------------------------------------------------------------------------------------------------------------------------------

		// // TIPO DADOS POR DADOS - ESTA EM MB
		// $itensDescricaoEncontrado = 0;
		// $encontrar_itens_descricao =    array(
								// "Internet"
								// );
		// str_replace($encontrar_itens_descricao,"",utf8_encode(substr($arquivo[$conta2-2],0,183)),$itensDescricaoEncontrado);
		// if($itensDescricaoEncontrado > 0) {
			// $capturar05 = 1;
			// $descricaoDados = 'Internet'; // CAPTURA A DESCRÇÃO DO SERVIÇO.
		// }
		// // -----------------------------------------------------

		// // ----------[ ENCERRA CAPTURA DAS LIGAÇÕES SE NÃO ENCONTRAR DATA ]----------
		// $fecha4 = 1;
		// //echo '<br/>'.utf8_encode(substr($linha,0,10)).'---------------------------------->FORA DO LOOP';
		// for($forEncerra = 0; $forEncerra <= 20; $forEncerra++) {
			// if(preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/",trim(substr($arquivo[$conta2],$forEncerra,10)))) {
				// $fecha4 = 0;
			// }
		// }

		// if($fecha4 > 0) {
			// $capturar05 = 0;
		// }
		// // -----------------------------------------------------

		// // ----------[ ENCERRA CAPTURA DOS DADOS ]----------
		// $encerraCapturaDados = 0;
		// $encontrarItensEncerraDados =    array(
								// "Subtotal",
								// "Conta:"
								// );
		// str_replace($encontrarItensEncerraDados,"",utf8_encode(substr($arquivo[$conta2],0,183)),$encerraCapturaDados);
		// if($encerraCapturaDados > 0) {
			// $capturar05 = 0;
		// }
		// // ----------------------------------------------------

		// if($capturar05 == 1) {

			// if(strlen(trim($numero_origem)) > 0) {
				// $arrayCampos5[1] = $numero_origem; // NUMERO
			// }elseif(strlen(trim($codigoCliente)) > 0) {
				// $arrayCampos5[1] = $codigoCliente;
			// }else {
				// $arrayCampos5[1] = $numero_fatura;
			// }

			// $arrayCampos5[2] = capturaData2 ('DD/MM/AAAA', $linha);
			// $arrayCampos5[3] = capturaHora2('00:00:00',$linha);
			// $arrayCampos5[4] = trim($descricaoDados); //DESCRIÇÂO
			// $arrayCampos5[5] = ''; // NUMERO DESTINO
			// $arrayCampos5[6] = ''; // OPERADORA
			// $arrayCampos5[7] = ''; // MINUTOS
			// $arrayCampos5[8] = ''; // QUANTIDADE

			// // ***** CAPTURA MB e KB E CONVERTE PARA MB. *****
			// $capturaKBMBGB = 0;
			// $linhaKBMBGB = '';
			// if($encontrouKBMBGB == 0) {
				// $encontrarKBMBGB =    array("KB","MB","GB");
				// $linhaKBMBGB = utf8_encode(substr($arquivo[$conta],0,180));
				// str_replace($encontrarKBMBGB, "",$linhaKBMBGB,$capturaKBMBGB);

				// if($capturaKBMBGB > 0) {

					// $formaKB = "(([0-9]{1,3})[\s]{0,}KB)";
					// preg_match_all($formaKB,utf8_encode(substr($arquivo[$conta],0,180)),$valorKBCapturado);

					// $formaMB = "((([0-9]{0,})[.])*([0-9]{1,3})[\s]{0,}MB)";
					// preg_match_all($formaMB,utf8_encode(substr($arquivo[$conta],0,180)),$valorMBCapturado);

					// $kbParaMB = ConverterPrefixoBinario ($valorKBCapturado[0][0], 'KB', 'MB');
					// $MBParaMB = ConverterPrefixoBinario ($valorMBCapturado[0][0], 'MB', 'MB');

					// $arrayCampos5[9] = $MBParaMB + $kbParaMB;

					// $encontrouKBMBGB = 0;
				// }
			// }
			// // **********

			// $arrayCampos5[10] = capturaValor2(substr($arquivo[$conta],-13)); // VALOR
			
			// $valor = str_replace(array(".",","), array("","."), $arrayCampos5[10]);
			// $totalCapturado += $valor;

			// $tudo5 = 
			 // ';'	// OPERADORA
			// .$codigoCliente.';'	// NOME DA ORIGEM
			// .$arrayCampos5[1].';'	// NUMERO TELEFONE
			// .$arrayCampos5[1].';'	// RAMAL ASSOCIADO
			// .$arrayCampos5[2].';'	// DATA LIGACAO
			// .$arrayCampos5[3].';'	// HORA LIGACAO
			// .$arrayCampos5[5].';'	// TELEFONE CHAMADO
			// .$arrayCampos5[1].';'	// TRONCO
			// .$arrayCampos5[4].';'	// DESCRICAO
			// .$arrayCampos5[9].';'	// DURACAO/UNID.
			// .$arrayCampos5[10].';'	// TARIFA
			// .';'	// DEPTO.
			// .';'	// CONTA DE FATURA
			// .';'	// MES_REF
			// ."\r\n";

			// if (!fwrite($fp, $tudo5)) {
				// print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				// exit;
			// }
			// $tudo5 = '';
		// }

		//-------------------------------------------------------------------------------------------------------

		//TIPO SERVIÇOS E MENSALIDADES

		str_replace("SERVIÇOS", "",utf8_encode(substr($arquivo[$conta],0,180)),$achouDemonstrativo);
		if($achouDemonstrativo > 0) {
			$servicosContratados = 1;
		}

		if($servicosContratados > 0) {

			$servicosCadastrados =    array(
											"ContaMinimaGarantidaVozTotal"					=> "Conta Minima Garantida Voz Total",
											"CREDITOREFERENTEAINTERRUPÇÃODESERVIÇO-FIXO"	=> "CREDITO REFERENTE A INTERRUPÇÃO DE SERVIÇO - FIXO"
											);

			$encontrouServ1 = 0;
			$servicosEspacosRemovidos = trim(str_replace($listaEspacos, "",utf8_encode(substr($arquivo[$conta],0,180))));

			$cesta = $servicosCadastrados;
			foreach($cesta as $novaDescServicos) {

				$VelhaDescServicos = trim(key($cesta));

				str_replace($VelhaDescServicos, "",$servicosEspacosRemovidos,$achouServico);

				if($achouServico > 0) {

					$descricaoServicosContratados1 = $novaDescServicos;
					$encontrouServ1 = 1;
					$capturar06 = 1;

				}
				next($cesta);
			}

			// if($encontrouServ1 == 0) {
				// $descricoesServicosNaoCadastrados = utf8_encode(substr($arquivo[$conta],0,55)).'+';
				// $alertaDescricaoNaocadastrado = 1;

				// if(empty($descricoesServicosNaoCadastrados)) {
					// $todosdescricoesNaoCadastrados["{$descricoesServicosNaoCadastrados}"] = 1;
				// }else {
					// $todosdescricoesNaoCadastrados["{$descricoesServicosNaoCadastrados}"] = 1;
				// }
				// $encontrouServ1 = 0;
			// }
		}
		
		// ---------------------------------------------------------------------------------------------

		if($capturar06 == 1) {

			if(strlen(trim($numero_origem)) > 0) {
				$arrayCampos6[1] = $numero_origem; // NUMERO
				$arrayCampos6[5] = $numero_origem; // NUMERO
			}elseif(strlen(trim($codigoCliente)) > 0) {
				$arrayCampos6[1] = $codigoCliente;
				$arrayCampos6[5] = $codigoCliente; // NUMERO DESTINO
			}else {
				$arrayCampos6[1] = $numero_fatura;
				$arrayCampos6[5] = $numero_fatura; // NUMERO DESTINO
			}

			$arrayCampos6[2] = capturaData2('DD/MM/AAAA', utf8_encode(substr($arquivo[$conta],0,30)));
			$arrayCampos6[3] = ''; // HORA
			$arrayCampos6[4] = $descricaoServicosContratados1; //DESCRIÇÂO
			//$arrayCampos6[5] = ''; // NUMERO DESTINO ESTA SENDO CAPTURADO ACIMA.
			$arrayCampos6[6] = ''; // OPERADORA
			$arrayCampos6[7] = ''; // MINUTOS
			$arrayCampos6[8] = ''; // QUANTIDADE
			$arrayCampos6[9] = ''; // KB
			$arrayCampos6[10] = capturaValor2(substr($arquivo[$conta],-25)); // VALOR

			$valor = str_replace(array(".",","), array("","."), $arrayCampos6[10]);
			$totalCapturado += $valor;

			$tudo6 = 
			 ';'	// OPERADORA
			.$codigoCliente.';'	// NOME DA ORIGEM
			.$arrayCampos6[1].';'	// NUMERO TELEFONE
			.$arrayCampos6[1].';'	// RAMAL ASSOCIADO
			.$arrayCampos6[2].';'	// DATA LIGACAO
			.$arrayCampos6[3].';'	// HORA LIGACAO
			.$arrayCampos6[5].';'	// TELEFONE CHAMADO
			.$arrayCampos6[1].';'	// TRONCO
			.iconv("UTF-8", "Windows-1252",$arrayCampos6[4]).';'	// DESCRICAO
			.$arrayCampos6[7].';'	// DURACAO
			.$arrayCampos6[10].';'	// TARIFA
			.';'	// DEPTO.
			.';'	// CONTA DE FATURA
			.';'	// MES_REF
			."\r\n";
			
			if (!fwrite($fp, $tudo6)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
			$tudo6 = '';
			$capturar06 = 0;  // ENCERRA APÓS CAPTURA.
		}

		//-------------------------------------------------------------------------------------------------------
		
		//--------[ DESCONTOS ]----------
		
		$itensDescontoEncontrado = 0;
		$encontrarItensDesconto =    array(
								"Locação  de Switch",
								"Acesso  Ethernet - 200 MBPS",
								"Banda  IP - 200 MBPS",
								"Acesso  Ethernet - 1 GBPS",
								"Instalação do Acesso"
								);
		str_replace($encontrarItensDesconto,"",utf8_encode(substr($arquivo[$conta],0,183)),$itensDescontoEncontrado);
					
		if($itensDescontoEncontrado > 0) {
			$capturar07 = 1;
		}
		// -----------------------------------------------------
		
		// ----------[ ENCERRA CAPTURA DOS DESCONTOS ]----------
		$encerraCapturaDesconto = 0;
		$encontrarItensEncerraDesconto =    array(
								"Subtotal",
								"Conta:"
								);
		str_replace($encontrarItensEncerraDesconto,"",utf8_encode(substr($arquivo[$conta2],0,183)),$encerraCapturaDesconto);
		if($encerraCapturaDesconto > 0) {
			$capturar07 = 0;
		}
		// ----------------------------------------------------
		
		if($capturar07 == 1) {
			
			if(strlen(trim($numero_origem)) > 0) {
				$arrayCampos7[1] = $numero_origem; // NUMERO
			}elseif(strlen(trim($codigoCliente)) > 0) {
				$arrayCampos7[1] = $codigoCliente;
				$arrayCampos7[5] = $codigoCliente;
			}else {
				// $arrayCampos7[1] = $numero_fatura;
			}

			$arrayCampos7[2] = capturaData2('DD.MM.AAAA', substr($linha,0,12));
			// $arrayCampos7[2] = ''; // DATA
			$arrayCampos7[3] = ''; // HORA
			$arrayCampos7[4] = trim(substr($linha,30,35)); //DESCRIÇÂO
			// $arrayCampos7[5] = ''; // NUMERO DESTINO
			$arrayCampos7[6] = ''; // OPERADORA
			$arrayCampos7[7] = ''; // MINUTOS
			$arrayCampos7[8] = ''; // QUANTIDADE
			$arrayCampos7[9] = ''; // MEGA
			$arrayCampos7[10] = capturaValor2(substr($linha,-35)); // VALOR

			$valor = str_replace(array(".",","), array("","."), $arrayCampos7[10]);
			$totalCapturado += $valor;

			$tudo7 = 
			 ';'	// OPERADORA
			.$codigoCliente.';'	// NOME DA ORIGEM
			.$arrayCampos7[1].';'	// NUMERO TELEFONE
			.$arrayCampos7[1].';'	// RAMAL ASSOCIADO
			.$arrayCampos7[2].';'	// DATA LIGACAO
			.$arrayCampos7[3].';'	// HORA LIGACAO
			.$arrayCampos7[5].';'	// TELEFONE CHAMADO
			.$arrayCampos7[1].';'	// TRONCO
			.$arrayCampos7[4].';'	// DESCRICAO
			.$arrayCampos7[7].';'	// DURACAO
			.$arrayCampos7[10].';'	// TARIFA
			.';'	// DEPTO.
			.';'	// CONTA DE FATURA
			.';'	// MES_REF
			."\r\n";
			
			if (!fwrite($fp, $tudo7)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
			$capturar07 = 0;  // ENCERRA APÓS CAPTURA.
		}
		// --------------------------------------------
		
		
		//------------------------------------------------------------[ CANCELAMENTO ]---------------------------------------------------------------------------
		
		$itensCancelamentoEncontrado = 0;
		$encontrarItensCancelamento =    array(
								"CANCELAMENTO        DE CONTRATO",
								"xxxxxxxx"
								);
		str_replace($encontrarItensCancelamento,"",substr($arquivo[$conta2-2],0,183),$itensCancelamentoEncontrado);
					
		if($itensCancelamentoEncontrado > 0) {
			$capturar09 = 1;
		}
		// -----------------------------------------------------
		
		// ----------[ ENCERRA CAPTURA DOS DESCONTOS ]----------
		$encerraCapturaCancelamento = 0;
		$encontrarItensEncerraCancelamento =    array(
								"Subtotal",
								"Conta:"
								);
		str_replace($encontrarItensEncerraCancelamento,"",utf8_encode(substr($arquivo[$conta2],0,183)),$encerraCapturaCancelamento);
		if($encerraCapturaCancelamento > 0) {
			$capturar09 = 0;
		}
		// ----------------------------------------------------
		
		if($capturar09 == 1) {
			
			$arrayCampos8[1] = $numero_conta; // NUMERO
			$arrayCampos8[2] = ''; // DATA
			$arrayCampos8[3] = ''; // HORA
			$arrayCampos8[4] = trim(substr($linha,0,70)); //DESCRIÇÂO
			$arrayCampos8[5] = ''; // NUMERO DESTINO
			$arrayCampos8[6] = ''; // OPERADORA
			$arrayCampos8[7] = ''; // MINUTOS
			$arrayCampos8[8] = ''; // QUANTIDADE
			$arrayCampos8[9] = ''; // MEGA
			$arrayCampos8[10] = capturaValor2(substr($arquivo[$conta],-13)); // VALOR
			
			$valor = str_replace(array(".",","), array("","."), $arrayCampos8[10]);
			$totalCapturado += $valor;

			$tudo8 = 
			 ';'	// OPERADORA
			.$codigoCliente.';'	// NOME DA ORIGEM
			.$arrayCampos8[1].';'	// NUMERO TELEFONE
			.$arrayCampos8[1].';'	// RAMAL ASSOCIADO
			.$arrayCampos8[2].';'	// DATA LIGACAO
			.$arrayCampos8[3].';'	// HORA LIGACAO
			.$arrayCampos8[5].';'	// TELEFONE CHAMADO
			.$arrayCampos8[1].';'	// TRONCO
			.$arrayCampos8[4].';'	// DESCRICAO
			.$arrayCampos8[7].';'	// DURACAO
			.$arrayCampos8[10].';'	// TARIFA
			.';'	// DEPTO.
			.';'	// CONTA DE FATURA
			.';'	// MES_REF
			."\r\n";

			if (!fwrite($fp, $tudo8)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
		}
		// ------------------------------------------------------------[ FIM CANCELAMENTO ]------------------------------------------------------------------
		
		
		//--------[ PARCELAMENTOS ]----------
		
		$itensParcelamentoEncontrado = 0;
		$encontrarItensParcelamento =    array(
								"PARCELAMENTOS",
								"xxxxxxxx"
								);
		str_replace($encontrarItensParcelamento,"",substr($arquivo[$conta2-2],0,183),$itensParcelamentoEncontrado);
					
		if($itensParcelamentoEncontrado > 0) {
			$capturar10 = 1;
		}
		// -----------------------------------------------------
		
		// ----------[ ENCERRA CAPTURA DOS DESCONTOS ]----------
		$encerraCapturaParcelamento = 0;
		$encontrarItensEncerraParcelamento =    array(
								"Subtotal",
								"Conta:"
								);
		str_replace($encontrarItensEncerraParcelamento,"",utf8_encode(substr($arquivo[$conta2],0,183)),$encerraCapturaParcelamento);
		if($encerraCapturaParcelamento > 0) {
			$capturar10 = 0;
		}
		// ----------------------------------------------------
		
		if($capturar10 == 1) {
			
			$arrayCampos9[1] = $numero_conta; // NUMERO
			$arrayCampos9[2] = ''; // DATA
			$arrayCampos9[3] = ''; // HORA
			$arrayCampos9[4] = trim(substr($linha,0,70)); //DESCRIÇÂO
			$arrayCampos9[5] = ''; // NUMERO DESTINO
			$arrayCampos9[6] = ''; // OPERADORA
			$arrayCampos9[7] = ''; // MINUTOS
			$arrayCampos9[8] = ''; // QUANTIDADE
			$arrayCampos9[9] = ''; // MEGA		
			$arrayCampos9[10] = capturaValor2(substr($arquivo[$conta],-13)); // VALOR
			
			$valor = str_replace(array(".",","), array("","."), $arrayCampos9[10]);
			$totalCapturado += $valor;

			$tudo9 = 
			 ';'	// OPERADORA
			.$codigoCliente.';'	// NOME DA ORIGEM
			.$arrayCampos9[1].';'	// NUMERO TELEFONE
			.$arrayCampos9[1].';'	// RAMAL ASSOCIADO
			.$arrayCampos9[2].';'	// DATA LIGACAO
			.$arrayCampos9[3].';'	// HORA LIGACAO
			.$arrayCampos9[5].';'	// TELEFONE CHAMADO
			.$arrayCampos9[1].';'	// TRONCO
			.$arrayCampos9[4].';'	// DESCRICAO
			.$arrayCampos9[7].';'	// DURACAO
			.$arrayCampos9[10].';'	// TARIFA
			.';'	// DEPTO.
			.';'	// CONTA DE FATURA
			.';'	// MES_REF
			."\r\n";

			if (!fwrite($fp, $tudo9)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
		}
		
		// ---------------------------------------[ EQUIPAMENTOS ACESSORIOS e REPAROS ]------------------------------------------

		$encerraOutrosDescontos = 0;
		$encontrarOutrosDescontos =    array(
								"Equipamentos,    Acessórios  e Reparos",
								"Equipamentos,      Acessórios     e Reparos"
								);
		str_replace($encontrarOutrosDescontos,"",utf8_encode(substr($arquivo[$conta2],0,183)),$encerraOutrosDescontos);
		if($encerraOutrosDescontos > 0) {
			$capturar11 = 1;
		}
		// ----------------------------------------------------
		
		if($capturar11 == 1) {
			
			if(strlen(trim($numero_origem)) > 0) {
				$arrayCampos10[1] = $numero_origem; // NUMERO
				$arrayCampos10[5] = $numero_origem; // NUMERO DESTINO
			}elseif(strlen(trim($codigoCliente)) > 0) {
				$arrayCampos10[1] = $codigoCliente;
				$arrayCampos10[5] = $codigoCliente; // NUMERO DESTINO
			}else {
				$arrayCampos10[1] = $numero_fatura;
				$arrayCampos10[5] = $numero_fatura; // NUMERO DESTINO
			}

			if(strlen($periodoUtilizacao) > 0) {
				$arrayCampos10[2] = $periodoUtilizacao; // DATA
			}else {
				$arrayCampos10[2] = ''; // DATA
			}
			$arrayCampos10[3] = ''; // HORA
			$arrayCampos10[4] = trim(substr($linha,0,70)); //DESCRIÇÂO
			// $arrayCampos10[5] = 'xx'; // NUMERO DESTINO SENDO DEFINIDO ACIMA.
			$arrayCampos10[6] = ''; // OPERADORA
			$arrayCampos10[7] = ''; // MINUTOS
			$arrayCampos10[8] = ''; // QUANTIDADE
			$arrayCampos10[9] = ''; // MEGA
			$arrayCampos10[10] = capturaValor2(substr($arquivo[$conta],-13)); // VALOR

			$valor = str_replace(array(".",","), array("","."), $arrayCampos10[10]);
			$totalCapturado += $valor;

			$tudo11 = 
			 ';'	// OPERADORA
			.$codigoCliente.';'	// NOME DA ORIGEM
			.$arrayCampos10[1].';'	// NUMERO TELEFONE
			.$arrayCampos10[1].';'	// RAMAL ASSOCIADO
			.$arrayCampos10[2].';'	// DATA LIGACAO
			.$arrayCampos10[3].';'	// HORA LIGACAO
			.$arrayCampos10[5].';'	// TELEFONE CHAMADO
			.$arrayCampos10[1].';'	// TRONCO
			.$arrayCampos10[4].';'	// DESCRICAO
			.$arrayCampos10[7].';'	// DURACAO
			.$arrayCampos10[10].';'	// TARIFA
			.';'	// DEPTO.
			.';'	// CONTA DE FATURA
			.';'	// MES_REF
			."\r\n";

			if (!fwrite($fp, $tudo11)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
			$capturar11 = 0;
		}

		$conta += 1;
		
	}//FECHA WHILE
	
	// Fecha o arquivo
	fclose($fp);

	//FECHA O PONTEIRO DO ARQUIVO
	fclose ($ponteiro);
	
	// return array(number_format($totalCapturado, 2, ',', '.'), number_format($totalFatura, 2, ',', '.'));
	return array($totalCapturado, $totalFatura);

}



?>

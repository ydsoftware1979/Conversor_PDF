<?php
include_once("funcoesParaFaturas2.php");

set_time_limit(1440);

function converteFaturaNEXTELFixo2019(string $arquivoConvertidoTXT) {

	$ARQUIVO_A_CONVERTER = $arquivoConvertidoTXT; // APENAS O NOME DO ARQUIVO SEM O PONTO EXTENSÃO.

	//ABRE O ARQUIVO TXT
	$fp = fopen($ARQUIVO_A_CONVERTER.".csv", "w+");
	$arquivo = file($ARQUIVO_A_CONVERTER);

	if($fp == false) die('O arquivo não foi criado.');
	if($arquivo == false) die('O arquivo não existe 1.');

	$conta = 0;
	$conta2 = 0;

	$capturar01 = 0;
	$capturar02 = 0;
	$capturar03 = 0;
	$capturar04 = 0;
	$capturar05 = 0;
	$capturar06 = 0;
	$capturar07 = 0;
	$capturar09 = 0;
	$capturar10 = 0;
	$capturar11 = 0;

	$numero_origem = '';
	$encontrouKBMBGB = 0;
	$totalCapturado = 0;
	$iniciaCapturaPlanos = 0;
	$itensColetaDetalhamento = 0;
	$capturaLigacaoIniciada = 0;
	$contadorLigacao1 = 0;
	$contadorLigacao2 = 0;
	$totalFatura = 0;
	$servicosContratados = 0;

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

	$numero_fatura = '0'; // É melhor informar o código da fatura em vez de tentar capturar.
	$listaEspacos = array(" ","  ","   ","    ","     ","      ","       ","        ","        ","          ");
	$alertaDescricaoNaocadastrado = 0;

	$quantidadeTotalLinhas = count($arquivo);

	for ($for1 = 0; $for1 < $quantidadeTotalLinhas; $for1++) {

		if($for1 > 3) {

			$conta = $for1;
			$conta2 = $for1;

			// CAPTURA O PERIODO DE UTILIZAÇÃO DOS SERVIÇOS.

			$flagPeriodoUtilizouServico = "(PER[\w\W]ODO([\s:space]?)[\w\W])";
			if(preg_match($flagPeriodoUtilizouServico,$arquivo[$conta])) {
				$periodoUtilizacao = capturaData2('DD/MM/AA', substr($arquivo[$conta2],0,100));
			}

			if(utf8_encode(trim(substr($arquivo[$conta],0,8))) == 'CLIENTE:') {
				$codigoCliente = capturaApenasNumero(trim(substr($arquivo[$conta2],9,20)));
			}

			//===============================================================================================================================================
			str_replace("Total:","", str_replace($listaEspacos,"",utf8_encode(substr($arquivo[$conta],0,180))), $flagTotalPagar);
			if($flagTotalPagar > 0) {
				$capturaServicosContratados = 1;
				$valorTotalPagar = "([\s]+(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

				$conta2 = $conta;

				if(preg_match($valorTotalPagar,$arquivo[$conta])) {
					preg_match_all($valorTotalPagar,$arquivo[$conta], $pegaTotalPagar);
					$totalFatura = str_replace(array(".",","), array("","."), $pegaTotalPagar[0][0]);
				}
				if(preg_match($valorTotalPagar, substr($arquivo[$conta2+1], 0, 1024))) {
					preg_match_all($valorTotalPagar,substr($arquivo[$conta2+1], 0, 1024), $pegaTotalPagar);
					$totalFatura = str_replace(array(".",","), array("","."), $pegaTotalPagar[0][0]);
				}
			}
			//===============================================================================================================================================

			if(utf8_encode(trim(substr($arquivo[$conta],0,19))) == 'Detalhes  da linha:') {
				$numero_origem = capturaApenasNumero(trim(substr($arquivo[$conta],20,25)));	// CAPTURA O NÚMERO DA LINHA QUE UTILIZOU O SERVIÇO.
			}

			if(utf8_encode(trim(substr($arquivo[$conta],105,15))) == 'TELEFONE:') {
				$numero_origem = capturaApenasNumero(trim(substr($arquivo[$conta],120,25)));	// CAPTURA O NÚMERO DA LINHA QUE UTILIZOU O SERVIÇO.
			}

			if(utf8_encode(trim(substr($arquivo[$conta],60,27))) == 'Código     da   Conta:') {
				$numero_fatura = capturaApenasNumero(trim(substr($arquivo[$conta2+1],88,15)));	// CAPTURA O NÚMERO DA LINHA QUE UTILIZOU O SERVIÇO.
			}

			if(utf8_encode(trim(substr($arquivo[$conta],0,31))) == 'Identificação       do  Cliente') {
				$codigoCliente = capturaApenasNumero(trim(substr($arquivo[$conta2+1],0,20)));	// CAPTURA O NÚMERO DA LINHA QUE UTILIZOU O SERVIÇO.
			}

			//-----------------------------------------------------------------------------------------------------------------------------------------------------------------	

			// TIPO LIGAÇÕES 1 - OK
			$formatoDataLigacao1 = '([0-9]{2}\/[0-9]{2}\/[0-9]{4})';
			$formatoHoraLigacao1 = '([0-9]{2}:[0-9]{2}:[0-9]{2})';
			$formatoDuracaoLigacao1 = "([0-9]{2,4}[:][0-9]{2})"; // 00:30

			$formatoLigacao1 = "({$formatoDataLigacao1}([\s]+){$formatoHoraLigacao1}([\W\w]+){$formatoDuracaoLigacao1}([\s]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

			if(preg_match($formatoLigacao1, $arquivo[$conta])) {

				$capturar01 = 1;
				$conta2 = $conta;

				if($contadorLigacao1 == 0) {

					$descicaoLigacao1 = array(
									"Minutoslocais" 					=> "Minutos locais",
									"MinutoslongadistânciaNextel"		=> "Minutos longa distância Nextel",
									"OutrasChamadas"					=> "Outras chamadas",
									"ParaNextel"						=> "Para Nextel",
									"ParacelularesdeOutrasOperadoras"	=> "Para celulares de Outras Operadoras",
									"ParaTelefonesFixos"				=> "Para Telefones Fixos",
									"OutrasChamadas"					=> "Outras Chamadas"
									);

					$linhaEspacosRemovidosLinha1 = '';
					$linhaEspacosRemovidosLinha1 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2],0,183))));

					$copia_descicaoLigacao1 = $descicaoLigacao1;

					foreach($copia_descicaoLigacao1 as $novaDescLigacao1) {

						if(key($copia_descicaoLigacao1) == $linhaEspacosRemovidosLinha1) {
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

				$arrayCampos[2] = capturaData2 ('DD/MM/AAAA', $arquivo[$conta]);
				$arrayCampos[3] = capturaHora2('00:00:00', $arquivo[$conta]);
				$arrayCampos[4] = $descricaoLigacao1; //DESCRIÇÂO
				$arrayCampos[5] = capturaApenasNumero(trim(substr($arquivo[$conta], 90, 20))); // NUMERO DESTINO
				$arrayCampos[6] = ''; // OPERADORA
				$arrayCampos[7] = decimo ("00:00", substr($arquivo[$conta], 135, 15));
				$arrayCampos[8] = ''; // QUANTIDADE
				$arrayCampos[9] = ''; // MEGA
				$arrayCampos[10] = capturaValor2(substr($arquivo[$conta], -13)); // VALOR

				$valor = str_replace(array(".",","), array("","."), $arrayCampos[10]);

				$totalCapturado += $valor;

				$tudo = 
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

				if (!fwrite($fp, $tudo)) { 
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}
				$tudo = '';

			} // FINAL TIPO1
			$capturar01 = 0;
			// ----------------------------------------------------------------------------------------------------------------------------------------------------------------

			// -------------------------[ TIPO MENSAGENS SMS ]-------------------------
			// ----------[ INICIA A CAPTURA DOS SMS E DESCRIÇÃO DOS SERVIÇOS]----------

			$formatoDataLigacao2 = '[0-9]{2}\/[0-9]{2}\/[0-9]{4}'; // DD/MM/AAAA
			$formatoHoraLigacao2 = '[0-9]{2}:[0-9]{2}:[0-9]{2}';   // 00:00:00

			$formatoLigacao2 = "(({$formatoDataLigacao2}[\s]+{$formatoHoraLigacao2})((.+[0-9]{2,15}[\s]+[\W\w]{4,10}[\s]+[\s]{20})|(.+[\s]+[\s]{30}[0-9]{1}[\s]+[\s]{30}))(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

			if(preg_match($formatoLigacao2, $arquivo[$conta])) {

				$capturar03 = 1;

				if($contadorLigacao2 == 0) {

					$descicaoLigacao2 = array(
									"SMSNextel"							=> "SMS Nextel",
									"OutrasChamadas"					=> "Outras Chamadas",
									"ServiçosdeTerceiroAssinatura"		=> "Servicos de Terceiro Assinatura"
									);

					$linhaEspacosRemovidosLinha2 = '';
					$linhaEspacosRemovidosLinha2 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2], 0, 183))));

					$copia_descicaoLigacao2 = $descicaoLigacao2;

					foreach($copia_descicaoLigacao2 as $novaDescLigacao2) {

						if(key($copia_descicaoLigacao2) == $linhaEspacosRemovidosLinha2) {
							$descricaoLigacao2 = $novaDescLigacao2;
							$encontrouLigacao2 = 1;
						}
						next($copia_descicaoLigacao2);
					}

					// PARA DESCIÇÕES NÃO CADASTRADAS.
					if ($encontrouLigacao2 == 0 AND $contadorLigacao2 == 0) {

						$descLigacao2 = utf8_encode(substr($arquivo[$conta2-2], 0, 183)).' - '.$conta;
						$todosdescricoesNaoCadastrados["{$descLigacao2}"] = 1;
						$alertaDescricaoNaocadastrado = 1;
					}
				}
				$contadorLigacao2 += 1;
			}
			else {
				$encontrouLigacao2 = 0;
				$contadorLigacao2 = 0;
			}

			if($capturar03 == 1) {

				if(strlen(trim($numero_origem)) > 0) {
					$arrayCampos3[1] = $numero_origem; // NUMERO
				}elseif(strlen(trim($codigoCliente)) > 0) {
					$arrayCampos3[1] = $codigoCliente;
				}else {
					$arrayCampos3[1] = $numero_fatura;
				}

				$arrayCampos3[2] = capturaData2('DD/MM/AAAA', substr($arquivo[$conta], 0, 20));
				$arrayCampos3[3] = capturaHora2('00:00:00', substr($arquivo[$conta], 20, 25));
				$arrayCampos3[4] = $descricaoLigacao2; //DESCRIÇÂO
				$arrayCampos3[5] = capturaApenasNumero(trim(substr($arquivo[$conta], 95, 20))); // NUMERO DESTINO
				$arrayCampos3[6] = ''; // OPERADORA
				$arrayCampos3[7] = ''; // MINUTOS
				$arrayCampos3[8] = ''; // QUANTIDADE
				$arrayCampos3[9] = ''; // MEGA
				$arrayCampos3[10] = capturaValor2(substr($arquivo[$conta], -13)); // VALOR

				$valor = str_replace(array(".",","), array("","."), $arrayCampos3[10]);

				$totalCapturado += $valor;
				
				// TRATAMENTO DE CODIFICAÇÃO NA DESCRIÇÃO
				//if(mb_detect_encoding($arrayCampos3[4], "UFT-8")) {
				//	$arrayCampos3[4] = iconv("UTF-8", "Windows-1252", $arrayCampos3[4]);
				//}else {
				//	$arrayCampos3[4] = utf8_encode($arrayCampos3[4]);
				//}

				$tudo3 = 
				 ';'	// OPERADORA
				.$codigoCliente.';'	// NOME DA ORIGEM
				.$arrayCampos3[1].';'	// NUMERO TELEFONE
				.$arrayCampos3[1].';'	// RAMAL ASSOCIADO
				.$arrayCampos3[2].';'	// DATA LIGACAO
				.$arrayCampos3[3].';'	// HORA LIGACAO
				.$arrayCampos3[5].';'	// TELEFONE CHAMADO
				.$arrayCampos3[1].';'	// TRONCO
				.$arrayCampos3[4].';'	// DESCRICAO
				.$arrayCampos3[7].';'	// DURACAO
				.$arrayCampos3[10].';'	// TARIFA
				.';'	// DEPTO.
				.';'	// CONTA DE FATURA
				.';'	// MES_REF
				."\r\n";

				if (!fwrite($fp, $tudo3)) {
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}
			}
			$capturar03 = 0;

			// -----------------------------------------------------------------------------------------------------------------------------------

			// TIPO DADOS POR DADOS - ESTA EM MB

			$itensDescricaoEncontrado = 0;
			$encontrar_itens_descricao =    array(
									"Internet",
									"INTERNET  MÓVEL   2"  => "Internet"
									);

			str_replace($encontrar_itens_descricao, "", utf8_encode(substr($arquivo[$conta2-2], 0, 183)), $itensDescricaoEncontrado);
			if($itensDescricaoEncontrado > 0) {
				$capturar05 = 1;
				$descricaoDados = 'Internet'; // CAPTURA A DESCRÇÃO DO SERVIÇO.
			}
			// -----------------------------------------------------

			// ----------[ ENCERRA CAPTURA DAS LIGAÇÕES SE NÃO ENCONTRAR DATA ]----------
			$fecha4 = 1;
			for($forEncerra = 0; $forEncerra <= 20; $forEncerra++) {
				if(preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", trim(substr($arquivo[$conta2], $forEncerra, 10)))) {
					$fecha4 = 0;
				}
			}

			if($fecha4 > 0) {
				$capturar05 = 0;
			}
			// -----------------------------------------------------

			// ----------[ ENCERRA CAPTURA DOS DADOS ]----------
			$encerraCapturaDados = 0;
			$encontrarItensEncerraDados =    array(
									"Subtotal",
									"Conta:"
									);
			str_replace($encontrarItensEncerraDados, "", utf8_encode(substr($arquivo[$conta2],0,183)), $encerraCapturaDados);
			if($encerraCapturaDados > 0) {
				$capturar05 = 0;
			}
			// ----------------------------------------------------

			if($capturar05 == 1) {

				if(strlen(trim($numero_origem)) > 0) {
					$arrayCampos5[1] = $numero_origem; // NUMERO
				}elseif(strlen(trim($codigoCliente)) > 0) {
					$arrayCampos5[1] = $codigoCliente;
				}else {
					$arrayCampos5[1] = $numero_fatura;
				}

				$arrayCampos5[2] = capturaData2 ('DD/MM/AAAA', $arquivo[$conta]);
				$arrayCampos5[3] = capturaHora2('00:00:00', $arquivo[$conta]);
				$arrayCampos5[4] = trim($descricaoDados); //DESCRIÇÂO
				$arrayCampos5[5] = ''; // NUMERO DESTINO
				$arrayCampos5[6] = ''; // OPERADORA
				$arrayCampos5[7] = ''; // MINUTOS
				$arrayCampos5[8] = ''; // QUANTIDADE

				// ***** CAPTURA MB e KB E CONVERTE PARA MB. *****
				$capturaKBMBGB = 0;
				$linhaKBMBGB = '';
				if($encontrouKBMBGB == 0) {
					$encontrarKBMBGB =    array("KB","MB","GB");
					$linhaKBMBGB = utf8_encode(substr($arquivo[$conta],0,180));
					str_replace($encontrarKBMBGB, "", $linhaKBMBGB, $capturaKBMBGB);

					if($capturaKBMBGB > 0) {

						$formaKB = "(([0-9]{1,3})[\s]{0,}KB)";
						preg_match_all($formaKB,utf8_encode(substr($arquivo[$conta], 0, 180)), $valorKBCapturado);

						$formaMB = "((([0-9]{0,})[.])*([0-9]{1,3})[\s]{0,}MB)";
						preg_match_all($formaMB, utf8_encode(substr($arquivo[$conta], 0, 180)), $valorMBCapturado);

						$kbParaMB = ConverterPrefixoBinario (str_replace(array("KB","MB"),array("",""), $valorKBCapturado[0][0]), 'KB', 'MB');
						$MBParaMB = ConverterPrefixoBinario (str_replace(array("KB","MB"),array("",""), $valorMBCapturado[0][0]), 'MB', 'MB');

						$arrayCampos5[9] = $MBParaMB + $kbParaMB;

						$encontrouKBMBGB = 0;
					}
				}
				// ***********************************************

				$arrayCampos5[10] = capturaValor2(substr($arquivo[$conta], -13)); // VALOR

				$valor = str_replace(array(".",","), array("","."), $arrayCampos5[10]);

				$totalCapturado += $valor;

				$tudo5 = 
				 ';'	// OPERADORA
				.$codigoCliente.';'	// NOME DA ORIGEM
				.$arrayCampos5[1].';'	// NUMERO TELEFONE
				.$arrayCampos5[1].';'	// RAMAL ASSOCIADO
				.$arrayCampos5[2].';'	// DATA LIGACAO
				.$arrayCampos5[3].';'	// HORA LIGACAO
				.$arrayCampos5[5].';'	// TELEFONE CHAMADO
				.$arrayCampos5[1].';'	// TRONCO
				.$arrayCampos5[4].';'	// DESCRICAO
				.$arrayCampos5[9].';'	// DURACAO/UNID.
				.$arrayCampos5[10].';'	// TARIFA
				.';'	// DEPTO.
				.';'	// CONTA DE FATURA
				.';'	// MES_REF
				."\r\n";

				if (!fwrite($fp, $tudo5)) {
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}
				$tudo5 = '';
			}

			//-------------------------------------------------------------------------------------------------------

			//TIPO SERVIÇOS E MENSALIDADES

			str_replace("Demonstrativos       de  serviços", "",utf8_encode(substr($arquivo[$conta], 0, 180)), $achouDemonstrativo);
			if($achouDemonstrativo > 0) {
				$servicosContratados = 1;
			}

			if($servicosContratados > 0) {

				$servicosCadastrados =    array(
												"Escolha - 3GRadio Pooling 19 155/pos/smp"		=> "3GRadio Pooling 19 155/pos/smp",
												"RoamingNac.Pos"								=> "RoamingNac.Pos",
												"BonusRecorrente2GB"							=> "BonusRecorrente2GB",
												"Internet3G2GB"									=> "Internet3G2GB",
												"CTR2GB+ilimitado194/PÓS/SMP"					=> "CTR2GB+ilimitado194/PÓS/SMP",
												"Escolha-3GRadioPooling19155/pos/smp"			=> "Escolha-3GRadioPooling19155/pos/smp",
												"Escolha - 3GRadio Pooling 19 155/pos/smp" 		=> "Escolha - 3GRadio Pooling 19 155/pos/smp",
												"CTR6GB+Ilimitado196/Pós/SMP"					=> "CTR6GB+Ilimitado196/Pós/SMP",
												"CTR 2GB+ilimitado 194/PÓS/SMP"					=> "CTR 2GB+ilimitado 194/PÓS/SMP",
												"Escolha-CTR8GB+Ilimitado196/Pós/SMP"			=> "CTR8GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTROnNetOnly190/Pós/SM"				=> "CTROnNetOnly190/Pós/SM",
												"Escolha-CTR4GB+Ilimitado196/Pós/SMP"			=> "CTR4GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR4GB+Ilimitado196/Pós/SMP"			=> "CTR4GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR4GB+Ilimitado196/Pós/SMP"			=> "CTR4GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR4GB+Ilimitado196/Pós/SMP"			=> "CTR4GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR4GB+Ilimitado196/Pós/SMP"			=> "CTR4GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR4GB+Ilimitado196/Pós/SMP"			=> "CTR4GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR4GB+Ilimitado196/Pós/SMP"			=> "CTR4GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR4GB+Ilimitado196/Pós/SMP"			=> "CTR4GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR1,5GB+Ilimitado196/Pós/SMP"			=> "CTR1,5GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR6GB+Ilimitado196/Pós/SMP"			=> "Escolha-CTR6GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR3GB+Ilimitado196/Pós/SMP"			=> "CTR3GB+Ilimitado 196/Pós/SMP",
												"Escolha-CTR3GB+Ilimitado196/Pós/SMP"			=> "CTR3GB+Ilimitado 196/Pós/SMP",
												"Escolha-SMART4GB+Ilimitado197/Pós/SMP"			=> "SMART4GB+Ilimitado 197/Pós/SMP",
												"Escolha-SMART7GB+Ilimitado193/Pós/SMP"			=> "SMART7GB+Ilimitado 193/Pós/SMP",
												"Escolha-SMART10GB+Ilimitado193/Pós/SMP"		=> "SMART10GB+Ilimitado 193/Pós/SMP",
												"Escolha-CTR 2GB+ilimitado 194/PÓS/SMP"			=> "CTR 2GB+ilimitado 194/PÓS/SMP",
												"Escolha-CTR500MB+ilimitado 194/PÓS/SMP"		=> "CTR500MB+ilimitado 194/PÓS/SMP",
												"3G+ Família 500 171/PÓS/SMP"					=> "3G + Família 500 171/PÓS/SMP",
												"SMART5GB+Ilimitado193/Pós/SMP"					=> "SMART 5GB+Ilimitado193/Pós/SMP",
												"Bonus500MB DebitoAutomatico"					=> "Bonus 500MB Debito Automatico",
												"Escolha - 3GRadio Pooling 19 155/pos/smp"		=> "3GRadio Pooling 19 155/pos/smp",
												"BonusARegraeClara1GB"							=> "Bonus A Regra e Clara 1GB",
												"SMART2GB+Ilimitado193/Pós/SMP" 				=> "SMART2GB +Ilimitado193/Pós/SMP",
												"Serviçoaregraéclara"							=> "Servico a regra é clara",
												"SMART5GB+Ilimitado193/Pós/SMP"					=> "SMART 5GB+Ilimitado193/Pós/SMP", 
												"Escolha - CTR 2GB+ilimitado 194/PÓS/SMP"		=> "CTR 2GB+ilimitado 194/PÓS/SMP",
												"Escolha - 3GRadio Pooling 19 155/pos/smp"		=> "3GRadio Pooling 19 155/pos/smp",
												"CTR 2GB+ilimitado 194/PÓS/SMP"					=> "CTR 2GB + ilimitado 194/PÓS/SMP",
												"Habilitação"									=> "Habilitacão",
												"Jurosfatura*"									=> "Juros"
												);

				$encontrouServ1 = 0;
				$servicosEspacosRemovidos = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 180))));

				$cesta = $servicosCadastrados;
				foreach($cesta as $novaDescServicos) {

					$VelhaDescServicos = trim(key($cesta));

					str_replace($VelhaDescServicos, "", $servicosEspacosRemovidos,$achouServico);

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

				$arrayCampos6[2] = capturaData2('DD/MM/AAAA', utf8_encode(substr($arquivo[$conta], 0, 30)));
				$arrayCampos6[3] = ''; // HORA
				$arrayCampos6[4] = $descricaoServicosContratados1; //DESCRIÇÂO
				//$arrayCampos6[5] = ''; // NUMERO DESTINO ESTA SENDO CAPTURADO ACIMA.
				$arrayCampos6[6] = ''; // OPERADORA
				$arrayCampos6[7] = ''; // MINUTOS
				$arrayCampos6[8] = ''; // QUANTIDADE
				$arrayCampos6[9] = ''; // KB
				$arrayCampos6[10] = capturaValor2(substr($arquivo[$conta], -13)); // VALOR

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
									"Desconto   Prom.  Internet",
									"Desconto   promo.  franquia",
									"Desconto  Prom. Internet"
									);
			str_replace($encontrarItensDesconto, "", substr($arquivo[$conta2], 0, 183), $itensDescontoEncontrado);

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
			str_replace($encontrarItensEncerraDesconto, "", utf8_encode(substr($arquivo[$conta2], 0, 183)), $encerraCapturaDesconto);
			if($encerraCapturaDesconto > 0) {
				$capturar07 = 0;
			}
			// ----------------------------------------------------

			if($capturar07 == 1) {

				if(trim($numero_origem) == '') {
					$arrayCampos7[1] = $numero_fatura;
				}else {
					$arrayCampos7[1] = $numero_origem;
				}

				//$arrayCampos7[1] = $numero_conta; // NUMERO
				$arrayCampos7[2] = ''; // DATA
				$arrayCampos7[3] = ''; // HORA
				$arrayCampos7[4] = trim(substr($arquivo[$conta], 3, 85)); //DESCRIÇÂO
				$arrayCampos7[5] = ''; // NUMERO DESTINO
				$arrayCampos7[6] = ''; // OPERADORA
				$arrayCampos7[7] = ''; // MINUTOS
				$arrayCampos7[8] = ''; // QUANTIDADE
				$arrayCampos7[9] = ''; // MEGA
				$arrayCampos7[10] = capturaValor2(substr($arquivo[$conta], 156, 20)); // VALOR

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
									"MultaFidelização",
									"xxxxxxxx"
									);
			str_replace($encontrarItensCancelamento, "", substr($arquivo[$conta2-2], 0, 183), $itensCancelamentoEncontrado);

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
			str_replace($encontrarItensEncerraCancelamento, "", utf8_encode(substr($arquivo[$conta2], 0, 183)), $encerraCapturaCancelamento);
			if($encerraCapturaCancelamento > 0) {
				$capturar09 = 0;
			}
			// ----------------------------------------------------

			if($capturar09 == 1) {

				$arrayCampos8[1] = $numero_conta; // NUMERO
				$arrayCampos8[2] = ''; // DATA
				$arrayCampos8[3] = ''; // HORA
				$arrayCampos8[4] = trim(substr($arquivo[$conta], 0, 70)); //DESCRIÇÂO
				$arrayCampos8[5] = ''; // NUMERO DESTINO
				$arrayCampos8[6] = ''; // OPERADORA
				$arrayCampos8[7] = ''; // MINUTOS
				$arrayCampos8[8] = ''; // QUANTIDADE
				$arrayCampos8[9] = ''; // MEGA
				$arrayCampos8[10] = capturaValor2(substr($arquivo[$conta], -13)); // VALOR

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
									/*"Jurosfatura",
									"Multafatura",*/
									"xxxxxxxx"
									);
			str_replace($encontrarItensParcelamento, "", substr($arquivo[$conta2-2], 0, 183), $itensParcelamentoEncontrado);

			if($itensParcelamentoEncontrado > 0) {
				$capturar10 = 1;
			}
			// -----------------------------------------------------

			// ----------[ ENCERRA CAPTURA DOS PARCELAMENTOS ]----------
			$encerraCapturaParcelamento = 0;
			$encontrarItensEncerraParcelamento =    array(
									"Subtotal",
									"Conta:"
									);
			str_replace($encontrarItensEncerraParcelamento, "", utf8_encode(substr($arquivo[$conta2], 0, 183)), $encerraCapturaParcelamento);
			if($encerraCapturaParcelamento > 0) {
				$capturar10 = 0;
			}
			// ----------------------------------------------------

			if($capturar10 == 1) {

				$arrayCampos9[1] = $numero_conta; // NUMERO
				$arrayCampos9[2] = ''; // DATA
				$arrayCampos9[3] = ''; // HORA
				$arrayCampos9[4] = trim(substr($arquivo[$conta], 0, 70)); //DESCRIÇÂO
				$arrayCampos9[5] = ''; // NUMERO DESTINO
				$arrayCampos9[6] = ''; // OPERADORA
				$arrayCampos9[7] = ''; // MINUTOS
				$arrayCampos9[8] = ''; // QUANTIDADE
				$arrayCampos9[9] = ''; // MEGA		
				$arrayCampos9[10] = capturaValor2(substr($arquivo[$conta], -13)); // VALOR

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
			/*
			// ---------------------------------------[ Juros e Multas ]------------------------------------------

			$itensJuruseMultas = 0;
			$encontrarJuruseMultas =    array(
									"PARCELAMENTOS",
									
									"xxxxxxxx"
									);
			str_replace($encontrarJuruseMultas, "", substr($arquivo[$conta2-2], 0, 183), $itensJuruseMultas);

			if($itensJuruseMultas > 0) {
				$capturar10 = 1;
			}
			// -----------------------------------------------------

			// ----------[ ENCERRA CAPTURA DOS PARCELAMENTOS ]----------
			$encerraCapturaParcelamento = 0;
			$encontrarItensEncerraParcelamento =    array(
									"Subtotal",
									"Conta:"
									);
			str_replace($encontrarItensEncerraParcelamento, "", utf8_encode(substr($arquivo[$conta2], 0, 183)), $encerraCapturaParcelamento);
			if($encerraCapturaParcelamento > 0) {
				$capturar10 = 0;
			}
			
*/
			// ---------------------------------------[ EQUIPAMENTOS ACESSORIOS e REPAROS ]------------------------------------------

			$encerraOutrosDescontos = 0;
			$encontrarOutrosDescontos =    array(
									"Equipamentos,    Acessórios  e Reparos",
									"Equipamentos,      Acessórios     e Reparos"
									);
			str_replace($encontrarOutrosDescontos, "", utf8_encode(substr($arquivo[$conta2], 0, 183)), $encerraOutrosDescontos);
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
				$arrayCampos10[4] = trim(substr($arquivo[$conta], 0, 70)); //DESCRIÇÂO
				// $arrayCampos10[5] = 'xx'; // NUMERO DESTINO SENDO DEFINIDO ACIMA.
				$arrayCampos10[6] = ''; // OPERADORA
				$arrayCampos10[7] = ''; // MINUTOS
				$arrayCampos10[8] = ''; // QUANTIDADE
				$arrayCampos10[9] = ''; // MEGA
				$arrayCampos10[10] = capturaValor2(substr($arquivo[$conta], 0, 103)); // VALOR

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

		}

	}//FECHA WHILE

	// Fecha o arquivo
	fclose($fp);

	//FECHA O PONTEIRO DO ARQUIVO
	fclose ($ponteiro);

	return array($totalCapturado, $totalFatura);
}

?>

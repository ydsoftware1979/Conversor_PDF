<?php

include_once("funcoesParaFaturas2.php");

set_time_limit(1440);

function converteFaturaVivoFixoPontoComunicacao2019(string $arquivoConvertidoTXT) {

	$ARQUIVO_A_CONVERTER = $arquivoConvertidoTXT; // APENAS O NOME DO ARQUIVO SEM O PONTO EXTENSÃO.

	$arquivo = file($ARQUIVO_A_CONVERTER);
	$fp = fopen($ARQUIVO_A_CONVERTER.".csv", "w+");

	if($arquivo == false) die('O arquivo não existe 1.');
	if($fp == false) die('O arquivo não foi criado.');

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

	$passaUm = true;
	$passa = 0;
	$codigoCliente = '';
	$numero_origem = '';
	$passaUmTotal = 0;
	$totalCapturado = 0;
	$iniciaCapturaPlanos = 0;
	$itensColetaDetalhamento = 0;

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

	$quantidadeTotalLinhas = count($arquivo);

	for ($for1 = 0; $for1 < $quantidadeTotalLinhas; $for1++) {

		if($for1 > 3) {

			$conta = $for1;
			$conta2 = $for1;

			if(utf8_encode(substr($arquivo[$conta],0,138)) == 'Total                                                                                                                                     ') {
				$passaUmTotal += 1;
				if($passaUmTotal == 1) {
					$totalFatura = str_replace(array(".",","), array("","."),trim(substr($arquivo[$conta],128,60)));	// CAPTURA O TOTAL DA FATURA.
				}
			}

			$listaEspacos = array(" ","  ","   ","    ","     ","      ","       ","        ","        ","          ");

			if(utf8_encode(trim(substr($arquivo[$conta],0,10))) == 'Ramal:') {
				$numero_origem = trim(substr($arquivo[$conta],30,25));	// CAPTURA O NÚMERO DA LINHA QUE UTILIZOU O SERVIÇO.
			}

			if(utf8_encode(trim(substr($arquivo[$conta],0,20))) == 'Cód.  Cliente:') {
				$numero_fatura = trim(capturaApenasNumero (substr($arquivo[$conta],30,15))); // CAPTURA O NÚMERO DA LINHA QUE UTILIZOU O SERVIÇO.
			}

			// NOME DA ORIGEM
			if(utf8_encode(trim(substr($arquivo[$conta],0,18))) == 'Código  do cliente' AND $passaUm == true) {
				$codigoCliente = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta],20,25)));
				$passaUm = false;
			}
			//-----------------------------------------------------------------------------------------------------------------------------------------------------------------	

			// TIPO LIGAÇÕES
			if(strlen(capturaData2 ("DD/MM/AA", substr($arquivo[$conta],15,15))) > 0 AND capturaHora2 ("00:00:00", substr($arquivo[$conta],120,15)) > 0 AND strlen(trim(substr($arquivo[$conta],150,15))) > 0) {
				$capturar01 = 1;
				$passa += 1;
			}else {
				$passa = 0;
			}

			if($capturar01 == 1) {
				$itensDescricaoEncontrado = 0;
				$encontrar_itens_descricao =    array(
										"Uso   de  Recurso    Local",
										"Uso   de  Recurso    Móvel    Local",
										"Uso   de  Recursos     Diferenciados",
										"Uso   de  Recurso    Longa    Distância    DDD",
										"Uso   de  Recurso    Móvel    de Longa    Distância"
										);
				str_replace($encontrar_itens_descricao,"",utf8_encode(substr($arquivo[$conta2-2],0,183)),$itensDescricaoEncontrado);
				if($itensDescricaoEncontrado > 0 AND $passa == 1) {
					$conta2 = $conta;
					$descricao_ligacao = trim(substr($arquivo[$conta2-2],0,120)); // CAPTURA A DESCRÇÃO DO SERVIÇO.
				}else {
					
				}
				if($itensDescricaoEncontrado == 0 AND $passa == 1) {
					$conta2 = $conta;
					$descricao_ligacao = 'DESCRICAO NAO CADASTRADA -> '.trim(substr($arquivo[$conta2-2],0,120));
				}
				// ------------------------------------------------------------------------------------
				if(strlen(trim($numero_origem)) > 0) {
					$arrayCampos[1] = $numero_origem; // NUMERO
				}else {
					$arrayCampos[1] = $numero_fatura; // NUMERO
				}
				if(strlen(trim($codigoCliente)) > 0) {
					
				}else {
					$codigoCliente = $numero_fatura;
				}

				$arrayCampos[2] = capturaData2 ("DD/MM/AA", substr($arquivo[$conta],15,15));
				$arrayCampos[3] = capturaHora2 ("00:00:00", substr($arquivo[$conta],120,15));
				$arrayCampos[4] = $descricao_ligacao; //DESCRIÇÂO
				$arrayCampos[5] = capturaApenasNumero(trim(substr($arquivo[$conta],30,35))); // NUMERO DESTINO
				$arrayCampos[6] = ''; // OPERADORA

				$arrayCampos[7] = trim(substr($arquivo[$conta],150,15));
				if(strlen($arrayCampos[7]) > 6) {
					$arrayCampos[7] = number_format(time_to_decimal(capturaHora2("00:00:00",substr($arrayCampos[7]))), 1, ',', '.');
				}

				$arrayCampos[8] = ''; // QUANTIDADE
				$arrayCampos[9] = ''; // MEGA
				$arrayCampos[10] = trim(substr($arquivo[$conta],strlen($arquivo[$conta])-12)); // VALOR

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
				.$arrayCampos[4].';'	// DESCRICAO
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
			}
			$capturar01 = 0;
			// ----------------------------------------------------------------------------------------------------------------------------------------------------------------

			// TIPO MENSAGENS SMS

			// ----------[ INICIA A CAPTURA DOS SMS E DESCRIÇÃO DOS SERVIÇOS]---------- 820
			$itensSmsEncontrado = 0;
			$encontrarItensSms =    array(
									"Data          Hora                         N",
									"xxxxxxxx"
									);
			str_replace($encontrarItensSms,"",substr($arquivo[$conta2-1],0,183),$itensSmsEncontrado);

			if($itensSmsEncontrado > 0) {

				$capturar03 = 1;
				// ----------[ APENAS PARA CAPTURA DA DESCRIÇÃO ]----------
				$itensDescricaoSmsEncontrado = 0;
				$encontrarItensDescricaoSms =    array(
										"Torpedo  SMS  -Diária Vivo Travel",
										"Ligações  para Serviços de Terceiros (Ex.:0300,0500 e Outros )",
										"Acesso  a Caixa Postal"
										);
				str_replace($encontrarItensDescricaoSms,"",utf8_encode(substr($arquivo[$conta2-2],0,183)),$itensDescricaoSmsEncontrado);
				if($itensDescricaoSmsEncontrado > 0) {
					$descricaoSms = substr($arquivo[$conta2-2],0,120); // CAPTURA A DESCRÇÃO DO SERVIÇO.
				}else {
					$descricaoSms = 'DESCRICAO NAO CADASTRADA -> '.$descricaoSms = substr($arquivo[$conta2-2],0,120);
					//$descricaoSms = 'DESCRICAO NAO CADASTRADA';
				}
				// --------------------------------------------------------
			}
			// -----------------------------------------------------

			// ----------[ ENCERRA CAPTURA DAS LIGAÇÕES ]----------
			$encerraCapturaLigacao = 0;
			$encontrar_itens_encerra_ligacao =    array(
									"Subtotal",
									"Conta:"
									);
			str_replace($encontrar_itens_encerra_ligacao,"",utf8_encode(substr($arquivo[$conta2],0,183)),$encerraCapturaLigacao);
			if($encerraCapturaLigacao > 0) {
				$capturar03 = 0;
			}
			// ----------------------------------------------------

			if($capturar03 == 1) {

				$ignorar_itens =    array(
									"Fecha"
									);

				$ignorado = 0;
				str_replace($ignorar_itens,"",substr($arquivo[$conta],0,183),$ignorado);

				if($ignorado > 0) {
					
				}else{

					if(strlen(trim($numero_origem)) > 0) {
						$arrayCampos3[1] = $numero_origem; // NUMERO
					}else {
						$arrayCampos3[1] = $numero_fatura; // NUMERO
					}
					if(strlen(trim($codigoCliente)) > 0) {
						
					}else {
						$codigoCliente = $numero_fatura;
					}

					$ignorado = 0;

					$arrayCampos3[1] = $numero_origem; // NUMERO
					$arrayCampos3[2] = trim(substr($arquivo[$conta],0,10)); // DATA
					$arrayCampos3[3] = trim(substr($arquivo[$conta],11,10)); // HORA
					$arrayCampos3[4] = trim($descricaoSms); //DESCRIÇÂO
					$arrayCampos3[5] = trim(substr($arquivo[$conta],39,17)); // NUMERO DESTINO
					$arrayCampos3[6] = ''; // OPERADORA
					$arrayCampos3[7] = ''; // MINUTOS
					$arrayCampos3[8] = trim(substr($arquivo[$conta],115,15)); // QUANTIDADE
					$arrayCampos3[9] = ''; // MEGA
					$arrayCampos3[10] = trim(substr($arquivo[$conta],144,13)); // VALOR

					$valor = str_replace(array(".",","), array("","."), $arrayCampos3[10]);
					$totalCapturado += $valor;

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
			}

			// -----------------------------------------------------------------------------------------------------------------------------------

			// TIPO DADOS POR DADOS - ESTA EM MB

			$itensDadosEncontrado = 0;
			$encontrarItensDados =    array(
									"Data          Hora                                                           Tipo",
									"xxxxxxxx"
									);
			str_replace($encontrarItensDados,"",substr($arquivo[$conta2-1],0,183),$itensDadosEncontrado);

			if($itensDadosEncontrado > 0) {
				$capturar05 = 1;
				// ----------[ APENAS PARA CAPTURA DA DESCRIÇÃO ]----------
				$itensDescricaoDadosEncontrado = 0;
				$encontrarItensDescricaoDados =    array(
										"Internet- Tarifaçãoem  MB/KB",
										"Dados  -Diária Vivo Travel",
										"Diárias Vivo Travel",
										"InternetRollover -Tarifação em  GB/MB"
										);
				str_replace($encontrarItensDescricaoDados,"",utf8_encode(substr($arquivo[$conta2-2],0,183)),$itensDescricaoDadosEncontrado);
				if($itensDescricaoDadosEncontrado > 0) {
					$descricaoDados = substr($arquivo[$conta2-2],0,120); // CAPTURA A DESCRÇÃO DO SERVIÇO.
				}else {
					$descricaoDados = 'DESCRICAO NAO CADASTRADA -> '.$descricao_ligacao = substr($arquivo[$conta2-2],0,120);
					//$descricao_ligacao = 'DESCRICAO NAO CADASTRADA';
				}
				// --------------------------------------------------------
			}
			// -----------------------------------------------------

			// ----------[ ENCERRA CAPTURA DOS DADOS ]----------
			$encerraCapturaDados = 0;
			$encontrarItensEncerraDados =    array(
									"Subtotal",
									"Conta:"
									);
			str_replace($encontrarItensEncerraDados,"",utf8_encode(substr($arquivo[$conta2],0,183)),$encerraCapturaDados);
			if($encerraCapturaDados > 0) {
				$capturar05 = 0;
			}
			// ----------------------------------------------------

			if($capturar05 == 1) {
				$ignorar_itens =    array(
									"Fecha"
									);

				$ignorado = 0;
				str_replace($ignorar_itens,"",substr($arquivo[$conta],0,183),$ignorado);

				if($ignorado > 0) {

				}else{

					if(strlen(trim($numero_origem)) > 0) {
						$arrayCampos5[1] = $numero_origem; // NUMERO
					}else {
						$arrayCampos5[1] = $numero_fatura; // NUMERO
					}
					if(strlen(trim($codigoCliente)) > 0) {
						
					}else {
						$codigoCliente = $numero_fatura;
					}

					$arrayCampos5[1] = $numero_origem; // NUMERO
					$arrayCampos5[2] = trim(substr($arquivo[$conta],0,10));
					$arrayCampos5[3] = trim(substr($arquivo[$conta],11,10));
					$arrayCampos5[4] = trim($descricaoDados); //DESCRIÇÂO
					$arrayCampos5[5] = ''; // NUMERO DESTINO
					$arrayCampos5[6] = ''; // OPERADORA
					$arrayCampos5[7] = ''; // MINUTOS
					$arrayCampos5[8] = ''; // QUANTIDADE
					$arrayCampos5[9] = trim(substr($arquivo[$conta],113,18)); // MEGA
					$arrayCampos5[10] = trim(substr($arquivo[$conta],144,13)); // VALOR

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
					.$arrayCampos5[7].';'	// DURACAO
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
			}

			//-------------------------------------------------------------------------------------------------------

			//TIPO SERVIÇOS E MENSALIDADES

			$inicioColetaDetalhamento =    array("Mensalidades        e  Condições       Especiais",
												 "DETALHAMENTO      DOS  SERVIÇOS   POR   NÚMERO    TELEFÔNICO"
												);
			str_replace($inicioColetaDetalhamento,"",utf8_encode(substr($arquivo[$conta2],0,180)),$itensColetaDetalhamento);

			if($itensColetaDetalhamento > 0) {
				$iniciaCapturaPlanos = 1;
			}

			if($iniciaCapturaPlanos == 1) {

				$itensPlanoEncontrado = 0;
				$encontrarItensPlano =    array(
										"Vox  Fácil- Gerenciamento",
										"Vox  Fácil- Mensalidade",
										"- Uso  de Recurso  Móvel  Longa  Distância  DDD  - DESC",
										"- Uso  de Recurso  Longa  Distância  DDD  - DESC",
										"- Uso  de Recurso  Local",
										"- Uso  de Recurso  Local  -DESC",
										"- Uso  de Recurso  Móvel  Local - DESC"
										);
				str_replace($encontrarItensPlano,"",utf8_encode(substr($arquivo[$conta2],0,101)),$itensPlanoEncontrado);

				if($itensPlanoEncontrado > 0) {
					$capturar06 = 1;
				}

			}
			// -----------------------------------------------------

			// ----------[ ENCERRA CAPTURA DOS PLANOS ]----------

			// O ENCERRAMENTO ESTÁ APÓS CAPTURA PORQUE A CAPTURA É POR PLANO.

			// ----------------------------------------------------

			if($capturar06 == 1) {
				$ignorar_itens =    array(
									"cuscus"
									);

				$ignorado = 0;
				str_replace($ignorar_itens,"",utf8_encode(substr($arquivo[$conta],0,183)),$ignorado);

				if($ignorado > 0) {
					$ignorado = 0;
				}else{

					if(strlen(trim($numero_origem)) > 0) {
						$arrayCampos6[1] = $numero_origem; // NUMERO
					}else {
						$arrayCampos6[1] = $numero_fatura; // NUMERO
					}
					if(strlen(trim($codigoCliente)) > 0) {
						
					}else {
						$codigoCliente = $numero_fatura;
					}

					$arrayCampos6[2] = ''; // DATA
					$arrayCampos6[3] = ''; // HORA
					$arrayCampos6[4] = str_replace("- ","",trim(substr($arquivo[$conta],0,85)),$ignorado);  //trim(substr($arquivo[$conta],0,85)); //DESCRIÇÂO
					$arrayCampos6[5] = ''; // NUMERO DESTINO
					$arrayCampos6[6] = ''; // OPERADORA
					$arrayCampos6[7] = ''; // MINUTOS
					$arrayCampos6[8] = ''; // QUANTIDADE
					$arrayCampos6[9] = ''; // KB
					$arrayCampos6[10] = trim(substr($arquivo[$conta],156,25)); // VALOR

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
					.$arrayCampos6[4].';'	// DESCRICAO
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
				}
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
			str_replace($encontrarItensDesconto,"",substr($arquivo[$conta2],0,183),$itensDescontoEncontrado);

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
				}else {
					$arrayCampos7[1] = $numero_fatura; // NUMERO
				}
				if(strlen(trim($codigoCliente)) > 0) {
					
				}else {
					$codigoCliente = $numero_fatura;
				}

				//$arrayCampos7[1] = $numero_conta; // NUMERO
				$arrayCampos7[2] = ''; // DATA
				$arrayCampos7[3] = ''; // HORA
				$arrayCampos7[4] = trim(substr($arquivo[$conta],3,85)); //DESCRIÇÂO
				$arrayCampos7[5] = ''; // NUMERO DESTINO
				$arrayCampos7[6] = ''; // OPERADORA
				$arrayCampos7[7] = ''; // MINUTOS
				$arrayCampos7[8] = ''; // QUANTIDADE
				$arrayCampos7[9] = ''; // MEGA
				$arrayCampos7[10] = trim(substr($arquivo[$conta],156,20)); // VALOR

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

				if(strlen(trim($numero_origem)) > 0) {
					$arrayCampos8[1] = $numero_origem; // NUMERO
				}else {
					$arrayCampos8[1] = $numero_fatura; // NUMERO
				}
				if(strlen(trim($codigoCliente)) > 0) {
					
				}else {
					$codigoCliente = $numero_fatura;
				}

				$arrayCampos8[1] = $numero_conta; // NUMERO
				$arrayCampos8[2] = ''; // DATA
				$arrayCampos8[3] = ''; // HORA
				$arrayCampos8[4] = trim(substr($arquivo[$conta],0,70)); //DESCRIÇÂO
				$arrayCampos8[5] = ''; // NUMERO DESTINO
				$arrayCampos8[6] = ''; // OPERADORA
				$arrayCampos8[7] = ''; // MINUTOS
				$arrayCampos8[8] = ''; // QUANTIDADE
				$arrayCampos8[9] = ''; // MEGA
				$arrayCampos8[10] = trim(substr($arquivo[$conta],145,13)); // VALOR

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

				if(strlen(trim($numero_origem)) > 0) {
					$arrayCampos9[1] = $numero_origem; // NUMERO
				}else {
					$arrayCampos9[1] = $numero_fatura; // NUMERO
				}
				if(strlen(trim($codigoCliente)) > 0) {
					
				}else {
					$codigoCliente = $numero_fatura;
				}

				$arrayCampos9[1] = $numero_conta; // NUMERO
				$arrayCampos9[2] = ''; // DATA
				$arrayCampos9[3] = ''; // HORA
				$arrayCampos9[4] = trim(substr($arquivo[$conta],0,70)); //DESCRIÇÂO
				$arrayCampos9[5] = ''; // NUMERO DESTINO
				$arrayCampos9[6] = ''; // OPERADORA
				$arrayCampos9[7] = ''; // MINUTOS
				$arrayCampos9[8] = ''; // QUANTIDADE
				$arrayCampos9[9] = ''; // MEGA
				$arrayCampos9[10] = trim(substr($arquivo[$conta],145,13)); // VALOR

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

			// ---------------------------------------[ OUTROS DESCONTOS - RESSARCIMENTO ]------------------------------------------

			$encerraOutrosDescontos = 0;
			$encontrarOutrosDescontos =    array(
									"Ressarcimento     por  interrupção  do   serviço  de  telefonia fixa",
									"Ressarcimento por interrupção  do serviçode  telefoniafixa",
									"Ressarcimento por interrupção  do serviçode  internet",
									"Isenção   de  Cob.  por  Interrupção   Pontual   do Serviço   Dados",
									"Ressarcimento     por  interrupção  do   serviço  de  internet"
									);
			str_replace($encontrarOutrosDescontos,"",utf8_encode(substr($arquivo[$conta2],0,183)),$encerraOutrosDescontos);
			if($encerraOutrosDescontos > 0) {
				$capturar11 = 1;
			}
			// ----------------------------------------------------

			if($capturar11 == 1) {

				if(strlen(trim($numero_origem)) > 0) {
					$arrayCampos8[1] = $numero_origem; // NUMERO
				}else {
					$arrayCampos8[1] = $numero_fatura; // NUMERO
				}
				if(strlen(trim($codigoCliente)) > 0) {
					
				}else {
					$codigoCliente = $numero_fatura;
				}

				//$arrayCampos10[1] = $numero_conta; // NUMERO
				$arrayCampos10[2] = ''; // DATA
				$arrayCampos10[3] = ''; // HORA
				$arrayCampos10[4] = trim(substr($arquivo[$conta],0,75)); //DESCRIÇÂO
				$arrayCampos10[5] = ''; // NUMERO DESTINO
				$arrayCampos10[6] = ''; // OPERADORA
				$arrayCampos10[7] = ''; // MINUTOS
				$arrayCampos10[8] = ''; // QUANTIDADE
				$arrayCampos10[9] = ''; // MEGA
				$arrayCampos10[10] = trim(substr($arquivo[$conta],85,20)); // VALOR

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

		}
	}//FECHA WHILE

	// Fecha o arquivo
	fclose($fp);

	//FECHA O PONTEIRO DO ARQUIVO
	// fclose ($arquivo);

	return array($totalCapturado, $totalFatura);
}

?>

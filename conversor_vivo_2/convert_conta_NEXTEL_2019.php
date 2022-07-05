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

	
	
		// TIPO LIGAÇÕES
		// APENAS SERVIÇOS COM CARACTERÌSTICAS DE LIGAÇÃO.
		$formatoDataLigacao = "([0-9]{2}\/[0-9]{2}\/[0-9]{2})";
		$formatoHoraLigacao = "([0-9]{2}:[0-9]{2}:[0-9]{2})";
		$formatoDuracaoLigacao = "([0-9]{1,6}[m][0-9]{2}[s])";

		$formatoLigacao1 = "({$formatoDataLigacao}([\s]+){$formatoHoraLigacao}([\W\w]+){$formatoDuracaoLigacao}([\s]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

		if(preg_match($formatoLigacao1, $arquivo[$for1])) {

			$capturar01 = 1;
			$conta2 = $conta;
			if ($contadorLigacao1 ==  0) {

				if($modoDebug == 1) {

					$descicaoLigacoes1 = array(
									"AcessoaCaixaPostal(Continuação)" 												=> "Acesso a Caixa Postal",
									"AcessoaCaixaPostal" 															=> "Acesso a Caixa Postal",
									"LigaçõesLocais-ParaCelularesdeOutrasOperadoras(Continuação)"					=> "Ligações Locais",
									"LigaçõesLocais-ParaCelularesdeOutrasOperadoras"								=> "Ligações Locais",
									"LigaçõesLocais-ParaCelularesVivo(Continuação)"									=> "Ligações Locais",
									"LigaçõesLocais-ParaCelularesVivo"												=> "Ligações Locais",
									"LigaçõesLocais-ParaFixodeOutrasOperadoras(Continuação)"						=> "Ligações Locais", // ok
									"LigaçõesLocais-ParaFixodeOutrasOperadoras"										=> "Ligações Locais",
									"LigaçõesLocais-ParaFixoVivo(Continuação)"										=> "Ligações Locais",
									"LigaçõesLocais-ParaFixoVivo"													=> "Ligações Locais",
									"LigaçõesLocais-ParaGrupo(Continuação)"											=> "Ligações Locais",
									"LigaçõesLocais-ParaGrupo"														=> "Ligações Locais",
									"LigaçõesLocaisRecebidasaCobrar-DeCelularesdeOutrasOperadoras"					=> "Ligações Locais",
									"LigaçõesLocaisRecebidasaCobrar-DeCelularesdeOutrasOperadoras(Continuação)"		=> "Ligações Locais",
									"LigaçõesLocaisRecebidasaCobrar-DeCelularesVivo"								=> "Ligações Locais",
									"LigaçõesLocaisRecebidasaCobrar-DeCelularesVivo(Continuação)"					=> "Ligações Locais",
									"LigaçõesLocaisRecebidasaCobrar-DeFixodeOutrasOperadoras"						=> "Ligações Locais",
									"LigaçõesLocaisRecebidasaCobrar-DeFixoVivo"										=> "Ligações Locais",

									"LigaçõesparaServiçosdeTerceiros(Ex.:102eOutros)"								=> "Ligações para Serviços de Terceiros (Ex.:102 e Outros)",
									"LigaçõesparaServiçosdeTerceiros(Ex.:0300,0500eOutros)"							=> "Ligações para Serviços de Terceiros (Ex.:0300,0500 e Outros)",
									"LigaçõesparaServiçosdeTerceiros(Ex.:0300,0500eOutros)(Continuação)"			=> "Ligações para Serviços de Terceiros (Ex.:0300,0500 e Outros)",

									"LigaçõesRecebidasemRoaming-(Continuação)"										=> "Ligações Recebidas em Roaming",
									"LigaçõesRecebidasemRoaming"													=> "Ligações Recebidas em Roaming",
									"LigaçõesRealizadas/Recebidas"													=> "Ligações Realizadas/Recebidas",

									"Internet-VivoWap-TarifaçãoemMinutos"											=> "Internet - Vivo Wap - Tarifação em Minutos",

									"RecebidasaCobrardeOutraLocalidade-DeCelularesVivo"								=> "Ligações de Longa Distância",
									"RecebidasaCobrardeOutraLocalidade-ParaFixoVivo"								=> "Ligações de Longa Distância",
									"ParaCelularesVivo(Continuação)"												=> "Ligações de Longa Distância",
									"ParaCelularesVivo"	                                                    		=> "Ligações de Longa Distância",
									"ParaCelularesdeOutrasOperadoras(Continuação)"	                        		=> "Ligações de Longa Distância",
									"ParaCelularesdeOutrasOperadoras"	                                    		=> "Ligações de Longa Distância",

									"ParaDentrodoEstado-ParaGrupo"													=> "Ligações de Longa Distância",
									"ParaDentrodoEstado-ParaCelularesVivo"											=> "Ligações de Longa Distância",
									"ParaDentrodoEstado-ParaCelularesVivo(Continuação)"								=> "Ligações de Longa Distância",
									"ParaDentrodoEstado-ParaCelularesdeOutrasOperadoras"	            			=> "Ligações de Longa Distância",
									"ParaDentrodoEstado-ParaCelularesdeOutrasOperadoras(Continuação)"				=> "Ligações de Longa Distância",
									"ParaDentrodoEstado-ParaFixodeOutrasOperadoras(Continuação)"					=> "Ligações de Longa Distância",
									"ParaDentrodoEstado-ParaFixodeOutrasOperadoras"	                    			=> "Ligações de Longa Distância",
									"ParaDentrodoEstado-ParaFixoVivo(Continuação)"									=> "Ligações de Longa Distância",
									"ParaDentrodoEstado-ParaFixoVivo"                                     			=> "Ligações de Longa Distância",

									"ParaFixodeOutrasOperadoras"	                                        		=> "Ligações de Longa Distância",
									"ParaFixodeOutrasOperadoras(Continuação)"										=> "Ligações de Longa Distância",
									"ParaOutrosEstados-ParaCelularesdeOutrasOperadoras"	                			=> "Ligações de Longa Distância",
									"ParaOutrosEstados-ParaCelularesdeOutrasOperadoras(Continuação)"				=> "Ligações de Longa Distância",
									"ParaOutrosEstados-ParaCelularesVivo(Continuação)"	                			=> "Ligações de Longa Distância",
									"ParaOutrosEstados-ParaCelularesVivo"	                                		=> "Ligações de Longa Distância",
									"ParaOutrosEstados-ParaFixodeOutrasOperadoras"	                    			=> "Ligações de Longa Distância",
									"ParaOutrosEstados-ParaFixodeOutrasOperadoras(Continuação)"						=> "Ligações de Longa Distância",
									"ParaOutrosEstados-ParaFixoVivo"	                                    		=> "Ligações de Longa Distância",
									"ParaOutrosEstados-ParaFixoVivo(Continuação)"									=> "Ligações de Longa Distância",
									"ParaOutrosPaíses"	                                                    		=> "Ligações de Longa Distância",
									"ParaOutrosPaíses(Continuação)"													=> "Ligações de Longa Distância",
									"ParaOutrosPaises"																=> "Ligações de Longa Distância",
									"ParaOutrosPaises(Continuação)"													=> "Ligações de Longa Distância",
									"ParaGrupo"																		=> "Para Grupo-",
									"ParaOutrosEstados-ParaGrupo"													=> "Para Outros Estados - Para Grupo-",
									"ParaFixoVivo"	                                                        		=> "Ligações de Longa Distância",
									"ParaFixoVivo(Continuação)"														=> "Ligações de Longa Distância",

									"VídeoChamadas-ParaCelularesVivo"												=> "Vídeo Chamadas - Para Celulares Vivo",

									"Voz-DiáriaVivoTravel(Continuação)"												=> "Voz - Diária Vivo Travel",
									"Voz-DiáriaVivoTravel"	                                                		=> "Voz - Diária Vivo Travel",
									"VozExcedente-DiáriaVivoTravel(Continuação)"									=> "Voz Excedente - Diária Vivo Travel",
									"VozExcedente-DiáriaVivoTravel"													=> "Voz Excedente - Diária Vivo Travel",
									);

				} else {

					$descicaoLigacoes1 = array(
									"AcessoaCaixaPostal(Continuação)" 												=> "Acesso a Caixa Postal",
									"AcessoaCaixaPostal" 															=> "Acesso a Caixa Postal",
									"LigaçõesLocais-ParaCelularesdeOutrasOperadoras(Continuação)"					=> "Ligações Locais - Para Celulares de Outras Operadoras",
									"LigaçõesLocais-ParaCelularesdeOutrasOperadoras"								=> "Ligações Locais - Para Celulares de Outras Operadoras",
									"LigaçõesLocais-ParaCelularesVivo(Continuação)"									=> "Ligações Locais - Para Celulares Vivo",
									"LigaçõesLocais-ParaCelularesVivo"												=> "Ligações Locais - Para Celulares Vivo",
									"LigaçõesLocais-ParaFixodeOutrasOperadoras(Continuação)"						=> "Ligações Locais - Para Fixo de Outras Operadoras", // ok
									"LigaçõesLocais-ParaFixodeOutrasOperadoras"										=> "Ligações Locais - Para Fixo de Outras Operadoras",
									"LigaçõesLocais-ParaFixoVivo(Continuação)"										=> "Ligações Locais - Para Fixo Vivo",
									"LigaçõesLocais-ParaFixoVivo"													=> "Ligações Locais - Para Fixo Vivo",
									"LigaçõesLocais-ParaGrupo(Continuação)"											=> "Ligações Locais - Para Grupo",
									"LigaçõesLocais-ParaGrupo"														=> "Ligações Locais - Para Grupo",
									"LigaçõesLocaisRecebidasaCobrar-DeCelularesdeOutrasOperadoras"					=> "Ligações Locais Recebidas a Cobrar - De Celulares de Outras Operadoras",
									"LigaçõesLocaisRecebidasaCobrar-DeCelularesdeOutrasOperadoras(Continuação)"		=> "Ligações Locais Recebidas a Cobrar - De Celulares de Outras Operadoras",
									"LigaçõesLocaisRecebidasaCobrar-DeCelularesVivo"								=> "Ligações Locais Recebidas a Cobrar - De Celulares Vivo",
									"LigaçõesLocaisRecebidasaCobrar-DeCelularesVivo(Continuação)"					=> "Ligações Locais Recebidas a Cobrar - De Celulares Vivo",
									"LigaçõesLocaisRecebidasaCobrar-DeFixodeOutrasOperadoras"						=> "Ligações Locais Recebidas a Cobrar - De Fixo de Outras Operadoras",
									"LigaçõesLocaisRecebidasaCobrar-DeFixoVivo"										=> "Ligações Locais Recebidas a Cobrar - De Fixo Vivo",

									"LigaçõesparaServiçosdeTerceiros(Ex.:102eOutros)"								=> "Ligações para Serviços de Terceiros (Ex.:102 e Outros)",
									"LigaçõesparaServiçosdeTerceiros(Ex.:0300,0500eOutros)"							=> "Ligações para Serviços de Terceiros (Ex.:0300,0500 e Outros)",
									"LigaçõesparaServiçosdeTerceiros(Ex.:0300,0500eOutros)(Continuação)"			=> "Ligações para Serviços de Terceiros (Ex.:0300,0500 e Outros)",

									"LigaçõesRecebidasemRoaming-(Continuação)"										=> "Ligações Recebidas em Roaming",
									"LigaçõesRecebidasemRoaming"													=> "Ligações Recebidas em Roaming",
									"LigaçõesRealizadas/Recebidas"													=> "Ligações Realizadas/Recebidas",

									"Internet-VivoWap-TarifaçãoemMinutos"											=> "Internet - Vivo Wap - Tarifação em Minutos",

									"RecebidasaCobrardeOutraLocalidade-DeCelularesVivo"								=> "Recebidas a Cobrar de Outra Localidade - De Celulares Vivo",
									"RecebidasaCobrardeOutraLocalidade-ParaFixoVivo"								=> "Recebidas a Cobrar de Outra Localidade - Para Fixo Vivo",
									"ParaCelularesVivo(Continuação)"												=> "Para Celulares Vivo",
									"ParaCelularesVivo"	                                                    		=> "Para Celulares Vivo",
									"ParaCelularesdeOutrasOperadoras(Continuação)"	                        		=> "Para Celulares de Outras Operadoras",
									"ParaCelularesdeOutrasOperadoras"	                                    		=> "Para Celulares de Outras Operadoras",

									"ParaDentrodoEstado-ParaGrupo"													=> "Para Dentro do Estado - Para Grupo",
									"ParaDentrodoEstado-ParaCelularesVivo"											=> "Para Dentro do Estado - Para Celulares Vivo",
									"ParaDentrodoEstado-ParaCelularesVivo(Continuação)"								=> "Para Dentro do Estado - Para Celulares Vivo",
									"ParaDentrodoEstado-ParaCelularesdeOutrasOperadoras"	            			=> "Para Dentro do Estado - Para Celulares de Outras Operadoras",
									"ParaDentrodoEstado-ParaCelularesdeOutrasOperadoras(Continuação)"				=> "Para Dentro do Estado - Para Celulares de Outras Operadoras",
									"ParaDentrodoEstado-ParaFixodeOutrasOperadoras(Continuação)"					=> "Para Dentro do Estado - Para Fixo de Outras Operadoras",
									"ParaDentrodoEstado-ParaFixodeOutrasOperadoras"	                    			=> "Para Dentro do Estado - Para Fixo de Outras Operadoras",
									"ParaDentrodoEstado-ParaFixoVivo(Continuação)"									=> "Para Dentro do Estado - Para Fixo Vivo (Continuação)",
									"ParaDentrodoEstado-ParaFixoVivo"                                     			=> "Para Dentro do Estado - Para Fixo Vivo",

									"ParaFixodeOutrasOperadoras"	                                        		=> "Para Fixo de Outras Operadoras",
									"ParaFixodeOutrasOperadoras(Continuação)"										=> "Para Fixo de Outras Operadoras",
									"ParaOutrosEstados-ParaCelularesdeOutrasOperadoras"	                			=> "Para Outros Estados - Para Celulares de Outras Operadoras",
									"ParaOutrosEstados-ParaCelularesdeOutrasOperadoras(Continuação)"				=> "Para Outros Estados - Para Celulares de Outras Operadoras",
									"ParaOutrosEstados-ParaCelularesVivo(Continuação)"	                			=> "Para Outros Estados - Para Celulares Vivo",
									"ParaOutrosEstados-ParaCelularesVivo"	                                		=> "Para Outros Estados - Para Celulares Vivo",
									"ParaOutrosEstados-ParaFixodeOutrasOperadoras"	                    			=> "Para Outros Estados - Para Fixo de Outras Operadoras",
									"ParaOutrosEstados-ParaFixodeOutrasOperadoras(Continuação)"						=> "Para Outros Estados - Para Fixo de Outras Operadoras",
									"ParaOutrosEstados-ParaFixoVivo"	                                    		=> "Para Outros Estados - Para Fixo Vivo",
									"ParaOutrosEstados-ParaFixoVivo(Continuação)"									=> "Para Outros Estados - Para Fixo Vivo",
									"ParaOutrosPaíses"	                                                    		=> "Para Outros Países",
									"ParaOutrosPaíses(Continuação)"													=> "Para Outros Países",
									"ParaOutrosPaises"																=> "Para Outros Países",
									"ParaOutrosPaises(Continuação)"													=> "Para Outros Países",
									"ParaGrupo"																		=> "Para Grupo",
									"ParaOutrosEstados-ParaGrupo"													=> "Para Outros Estados - Para Grupo",
									"ParaFixoVivo"	                                                        		=> "Para Fixo Vivo",
									"ParaFixoVivo(Continuação)"														=> "Para Fixo Vivo",

									"VídeoChamadas-ParaCelularesVivo"												=> "Vídeo Chamadas - Para Celulares Vivo",

									"Voz-DiáriaVivoTravel(Continuação)"												=> "Voz - Diária Vivo Travel",
									"Voz-DiáriaVivoTravel"	                                                		=> "Voz - Diária Vivo Travel",
									"VozExcedente-DiáriaVivoTravel(Continuação)"									=> "Voz Excedente - Diária Vivo Travel",
									"VozExcedente-DiáriaVivoTravel"													=> "Voz Excedente - Diária Vivo Travel",
									);

				}
				$linhaEspacosRemovidos1 = '';
				$linhaEspacosRemovidos1 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2], 0, 183))));
				$copia_descicaoLigacoes1 = $descicaoLigacoes1;
				foreach($copia_descicaoLigacoes1 as $novaDescLigacoes) {

					if(key($copia_descicaoLigacoes1) == $linhaEspacosRemovidos1) {
						$descricaoLigacao1 = $novaDescLigacoes;
						$encontrouLigacao1 = 1;
					}
					next($copia_descicaoLigacoes1);
				}
			}

			// PARA DESCIÇÕES NÃO CADASTRADAS.
			if ($encontrouLigacao1 == 0 AND $contadorLigacao1 == 0) {
				$descricaoLigacao1 = 'SERVIÇO NÃO CADASTRADO';
				$descLigacao1 = utf8_encode(substr($arquivo[$conta2-2], 0, 183));
				$todosdescricoesNaoCadastrados["{$descLigacao1}"] = 1;
				$alertaDescricaoNaocadastrado = 1;
			}
			$contadorLigacao1 += 1;
		}else {
			$contadorLigacao1 = 0;
			$encontrouLigacao1 = 0;
			$descricaoLigacao1 = '';
		}

		if($capturar01 == 1) {

			$ligacao1[1] = $numero_origem; // NUMERO QUE USOU O SERVIÇO.
			$ligacao1[2] = capturaData2('DD/MM/AA', utf8_encode(substr($arquivo[$for1], 0, 50))); // DATA DA LIGAÇÃO.
			$ligacao1[3] = capturaHora2('00:00:00', utf8_encode(substr($arquivo[$for1], 10, 60))); // HORA DA LIGAÇÃO.
			$ligacao1[4] = trim($descricaoLigacao1); // DESCRIÇÂO DO SERVIÇO.

			$ligacao1[5] = capturaApenasNumero(substr($arquivo[$for1], 40, 15)); // NUMERO DESTINO - CAPTURA SÓ NUMERO.
			$ligacao1[5] = capturaNumeroDiscado($ligacao1[5]); // VALIDA SE CONTÉM DE 8 OU MAIS NÚMEROS.

			$ligacao1[6] = ''; // OPERADORA

			//***** NÃO ESQUECER DE AJEITAR PARA TODOS. *****
			$ligacao1[7] = decimo('00m00s', substr($arquivo[$for1], 119, 15)); // DURAÇÃO - NOVO MÉTODO PARA CONVERSÃO DE (00m00s) PARA (000,0).

			$ligacao1[8] = ''; // QUANTIDADE
			$ligacao1[9] = ''; // MEGA
			$ligacao1[10] = capturaValor2(substr($arquivo[$for1], -13)); // VALOR

			$valor = str_replace(array(".", ","), array("", "."), $ligacao1[10]);
			$totalCapturado += $valor;

			if(empty($todosdescricaoServicos["{$ligacao1[4]}"])) {
				$str = trim($ligacao1[4]);
				$todosdescricaoServicos["{$str}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$ligacao1[4]}"])) {
				$str = trim($ligacao1[4]);
				$todosdescricaoServicos["{$str}"] += $valor;
			}else {
				$str = trim($ligacao1[4]);
				$todosdescricaoServicos["{$str}"] += $valor;
			}

			// ==============================================================
			if($modoDebug == 1) {
				$str = trim($ligacao1[4]);
				if(empty($debugLigacao1["{$str}"])) {

					$debugLigacao1["{$str}"] = 0;
				}

				if(empty($debugLigacao1["{$str}"])) {

					$debugLigacao1["{$str}"] += $valor;
				}else {

					$debugLigacao1["{$str}"] += $valor;
				}
			}
			// ==============================================================

			$linhaLigacao1 = 
				 $ligacao1[1].';'
				.$ligacao1[2].';'
				.$ligacao1[3].';'
				.iconv("UTF-8", "Windows-1252",$ligacao1[4]).';'
				.$ligacao1[5].';'
				.$ligacao1[6].';'
				.$ligacao1[7].';'
				.$ligacao1[8].';'
				.$ligacao1[9].';'
				.$ligacao1[10]
				.";\r\n";

			if (!fwrite($fp, $linhaLigacao1)) { 
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
			$linhaLigacao1 = '';
		}
		$capturar01 = 0;

	
	
	
	
	
} //Final




?>
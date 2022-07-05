<?php

include_once("funcoesParaFaturas2.php");

set_time_limit(1440);

function converteFaturaVivoMovel(string $arquivoConvertidoTXT) {

	$nomeArquivo = $arquivoConvertidoTXT;

	$arquivo = file($nomeArquivo);
	$fp = fopen($nomeArquivo.".csv", "w+");

	if($arquivo == false) die('O arquivo não existe.');
	if($fp == false) die('O arquivo não foi criado.');

	// INICIANDO VAIÁVEIS.
	$conta = 0;
	$conta2 = 0;
	$valor = 0;
	$totalCapturado = 0;
	$totalPagar = 0;
	$capturar01 = 0;
	$capturar02 = 0;
	$capturar03 = 0;
	$capturar04 = 0;
	$capturar05 = 0;
	$capturar06 = 0;
	$capturar07 = 0;
	$capturar077 = 0;
	$capturar08 = 0;
	$capturar09 = 0;
	$capturar13 = 0;
	
	$servicosContratados3 = 0;
	$iniciaCapturaMultaJuros = 0;

	$contadorDados1 = 0;
	$encontrouDados1 = 0;

	$descricaoLigacao1 = '';
	$todosdescricaoServicos = array();
	$todosdescricoesNaoCadastrados = array();
	$alertaDescricaoNaocadastrado = 0;
	$descricaoServicosContratados2 = '';
	$capturaServicosContratados = 0;
	$servicosContratados = 0;
	$encontrouLigacao1 = 0;
	$contadorPacote1 = 0;
	$encontrouPacote1 = 0;
	$numero_origem = '';
	$pegaValor = 0;

	$tudo = 'NUMERO'.';'
		.'DATA'.';'
		.'HORA'.';'
		.'DESCRICAO'.';'
		.'NUMERO DESTINO'.';'
		.'OPERADORA'.';'
		.'MINUTOS'.';'
		.'QUANTIDADE'.';'
		.'KB/MB/GB'.';'
		.'VALOR'.';'
		."\r\n";

	if (!fwrite($fp, $tudo)) {
		print "Erro escrevendo no arquivo ou esta sendo usado por outro programa.";
		exit;
	}

	$modoDebug = 0;
	
	$listaEspacos = array(" ","  ","   ","    ","     ","      ","       ","        ","        ","          ");

	$quantidadeTotalLinhas = count($arquivo);

	for ($for1 = 0; $for1 < $quantidadeTotalLinhas; $for1++) {

		$conta2 = $for1;
		$conta = $for1;

		// DETECTA OPERADORA - 02.558.157/0001-62
		$flagCNPJOperadora = "([0-9]{2}[.][0-9]{3}[.][0-9]{3}\/[0-9]{4}[-][0-9]{2})";
		if(preg_match($flagCNPJOperadora,$arquivo[$for1])) {// AND $for1 < 20) {
			preg_match_all($flagCNPJOperadora,$arquivo[$for1],$CNPJ_capturado);
			// echo '<br/>CNPJ Operadora.: '.$CNPJ_capturado[0][0];
		}

		//                        >>>>>>>>>>>>[ ATENÇÃO ]<<<<<<<<<<<<<<<<
		//
		// O USUÁRIO DEVE FORNECER O VALOR TOTAL DA FATURA POR SEGURANÇA, E NÃO CAPTURAR DO ARQUIVO.
		//

		// =====[ CAPTURA O NÚMERO QUE UTILIZOU O SERVIÇO ]=====
		$flagNumeroUtilizouServico =  "((VEJA)[\s]{0,15}(O)[\s]{0,15}(USO)[\s]{0,15}(DETALHADO)[\s]{0,15}(DO)[\s]{0,15}(VIVO)[\s]{0,20}[0-9]{1,3}[-]*(([0-9]{5}))[-]*([0-9]{4})[\s]+)";
		if(preg_match($flagNumeroUtilizouServico, $arquivo[$for1])) {

			$flagPegaNumeroUtilizouServico = "([\s]+[0-9]{2,3}[-]*[0-9]{5}[-]*[0-9]{4})";

			if(preg_match($flagPegaNumeroUtilizouServico, $arquivo[$for1])) {

				preg_match_all($flagPegaNumeroUtilizouServico,$arquivo[$for1],$numero_origem_capturado);
				$numero_origem = capturaApenasNumero($numero_origem_capturado[0][0]); // NUMERO DESTINO - CAPTURA SÓ NUMERO.
				$numero_origem = capturaNumeroDiscado($numero_origem); // VALIDA SE CONTÉM DE 8 OU MAIS NÚMEROS.

			}
		}

		$flagTotalAPagar = "((TOTAL)[\s]{0,15}(A)[\s]{0,15}(PAGAR)[\s]{0,25})";
		if(preg_match($flagTotalAPagar, $arquivo[$for1])) {

			$capturaServicosContratados = 1;
			$valorTotalPagar = "([\s]+(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

			$conta2 = $conta;

			if(preg_match($valorTotalPagar, $arquivo[$for1])) {

				preg_match_all($valorTotalPagar, $arquivo[$conta], $pegaTotalPagar);
				$totalFatura = str_replace(array(".",","), array("","."), $pegaTotalPagar[0][0]);

			}
			if(preg_match($valorTotalPagar, substr($arquivo[$conta2+1], 0, 1024))) {

				preg_match_all($valorTotalPagar, substr($arquivo[$conta2+1], 0, 1024), $pegaTotalPagar);
				$totalFatura = str_replace(array(".", ","), array("", "."), $pegaTotalPagar[0][0]);

			}
		}

		// =====[ CAPTURA O NÚMERO DA CONTA ]=====
		if($for1 < 100) {

			$flagNumeroDaConta =  "((^[]|^[\s]*)(Nº)[\s]{0,10}(da)[\s]{0,10}(Conta){0,10}(:)[\w\W]+)";
			if(preg_match($flagNumeroDaConta, utf8_encode($arquivo[$for1]))) {

				$flagPegaNumeroDaConta = "([\w\W]+[0-9]{6,15})";

				if(preg_match($flagPegaNumeroDaConta, utf8_encode($arquivo[$for1]))) {

					preg_match_all($flagPegaNumeroDaConta, $arquivo[$for1],$numeroDaContaCapturado);
					$numeroDaConta = capturaApenasNumero($numeroDaContaCapturado[0][0]); // NUMERO DESTINO - CAPTURA SÓ NUMERO.

				}
			}
		}

	// **********************************************************************************************************************************************************************

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

	//********************************************************************************************************************************************************************
	//*****[ PACOTE ]*****

		$formatoDataPacote = '([0-9]{2}\/[0-9]{2}\/[0-9]{2})';
		$formatoHoraPacote = '([0-9]{2}:[0-9]{2}:[0-9]{2})';

		$formatoLigacao2 = "((^[]|^[\s]*){$formatoDataPacote}[\s]+({$formatoHoraPacote}|(-))([\W\w\s]+)(([0-9]{1,3})|([0-9]{1,3}[\s]*dia))[\s]+(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

		if(preg_match($formatoLigacao2, $arquivo[$for1])) {

			$capturar03 = 1;
			$conta2 = $conta;

			if($contadorPacote1 == 0) {

					if($modoDebug == 1) {

						$descicaoPacotes1 = array(
									"AdicionalporLigaçõesRealizadas(Continuação)"	=> "Adicional por Ligações Realizadas",
									"AdicionalporLigaçõesRealizadas" 				=> "Adicional por Ligações Realizadas",
									"AdicionalporLigaçõesRecebidas(Continuação)" 	=> "Adicional por Ligações Recebidas",
									"AdicionalporLigaçõesRecebidas" 				=> "Adicional por Ligações Recebidas",

									"Doações(Ex.:0500)"								=> "Doações (Ex.:0500)",

									"FotoTorpedoMMS(Continuação)"					=> "Serviços (Ex.: SMS e Loja de Serviços Vivo)",
									"FotoTorpedoMMS"								=> "Serviços (Ex.: SMS e Loja de Serviços Vivo)",

									"TorpedoSMS(Continuação)"						=> "Torpedo SMS",
									"TorpedoSMS"									=> "Torpedo SMS",
									"TorpedoSMS-DiáriaVivoTravel(Continuação)"		=> "Torpedo SMS - Diária Vivo Travel",
									"TorpedoSMS-DiáriaVivoTravel"					=> "Torpedo SMS - Diária Vivo Travel",

									"TorpedoSMSparaOutrosServiços(Continuação)"		=> "Serviços (Ex.: SMS e Loja de Serviços Vivo)",
									"TorpedoSMSparaOutrosServiços"					=> "Serviços (Ex.: SMS e Loja de Serviços Vivo)",

									"DiáriasVivoTravel"								=> "Diárias Vivo Travel",
									"DiáriasVivoTravel(Continuação)"				=> "Diárias Vivo Travel",
									"Dados-DiáriaVivoTravel"						=> "Dados - Diária Vivo Travel",
									"Dados-DiáriaVivoTravel(Continuação)"			=> "Dados - Diária Vivo Travel"
									);

					}else {

						$descicaoPacotes1 = array(
									"AdicionalporLigaçõesRealizadas(Continuação)"	=> "Adicional por Ligações Realizadas",
									"AdicionalporLigaçõesRealizadas" 				=> "Adicional por Ligações Realizadas",
									"AdicionalporLigaçõesRecebidas(Continuação)" 	=> "Adicional por Ligações Recebidas",
									"AdicionalporLigaçõesRecebidas" 				=> "Adicional por Ligações Recebidas",

									"Doações(Ex.:0500)"								=> "Doações (Ex.:0500)",

									"FotoTorpedoMMS(Continuação)"					=> "Foto Torpedo MMS",
									"FotoTorpedoMMS"								=> "Foto Torpedo MMS",

									"TorpedoSMS(Continuação)"						=> "Torpedo SMS",
									"TorpedoSMS"									=> "Torpedo SMS",
									"TorpedoSMS-DiáriaVivoTravel(Continuação)"		=> "Torpedo SMS - Diária Vivo Travel",
									"TorpedoSMS-DiáriaVivoTravel"					=> "Torpedo SMS - Diária Vivo Travel",

									"TorpedoSMSparaOutrosServiços(Continuação)"		=> "Torpedo SMS para Outros Serviços",
									"TorpedoSMSparaOutrosServiços"					=> "Torpedo SMS para Outros Serviços",

									"DiáriasVivoTravel"								=> "Diárias Vivo Travel",
									"DiáriasVivoTravel(Continuação)"				=> "Diárias Vivo Travel",
									"Dados-DiáriaVivoTravel"						=> "Dados - Diária Vivo Travel",
									"Dados-DiáriaVivoTravel(Continuação)"			=> "Dados - Diária Vivo Travel"
									);
					}

					$linhaEspacosRemovidosPacote1 = '';
					$linhaEspacosRemovidosPacote1 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2], 0, 183))));

					$copia_descicaoPacote1 = $descicaoPacotes1;

					foreach($copia_descicaoPacote1 as $novaDescPacotes1) {

						if(key($copia_descicaoPacote1) == $linhaEspacosRemovidosPacote1) {
							$descricaoPacote1 = $novaDescPacotes1;
							$encontrouPacote1 = 1;
						}
						next($copia_descicaoPacote1);
					}

					// PARA DESCIÇÕES NÃO CADASTRADAS.
					if ($encontrouPacote1 == 0 AND $contadorPacote1 == 0) {

						$descPacote1 = utf8_encode(substr($arquivo[$conta2-2], 0, 183)).' - '.$for1;
						$todosdescricoesNaoCadastrados["{$descPacote1}"] = 1;
						$alertaDescricaoNaocadastrado = 1;
					}
			}
			$contadorPacote1 += 1;
		}
		else {
			$encontrouPacote1 = 0;
			$contadorPacote1 = 0;
		}
		if($capturar03 == 1) {

				$pacote1[1] = $numero_origem; // NUMERO
				$pacote1[2] = capturaData2('DD/MM/AA', utf8_encode(substr($arquivo[$for1], 0, 50))); // DATA DO PACOTE.
				$pacote1[3] = capturaHora2('00:00:00', utf8_encode(substr($arquivo[$for1], 10, 60))); // HORA DO PACOTE.
				$pacote1[4] = trim($descricaoPacote1); //DESCRIÇÂO

				$pacote1[5] = capturaApenasNumero(substr($arquivo[$for1], 40, 15)); // NUMERO DESTINO - CAPTURA SÓ NUMERO.
				if(capturaNumeroDiscado($pacote1[5])) {
					$pacote1[5] = capturaNumeroDiscado($pacote1[5]); // NUMERO DESTINO - VALIDA SE CONTÉM DE 8 OU MAIS NÚMEROS.
				}else {
					$pacote1[5] = ''; // NUMERO DESTINO
				}

				$pacote1[6] = ''; // OPERADORA
				$pacote1[7] = ''; // MINUTOS
				$pacote1[8] = capturaApenasNumero(trim(substr($arquivo[$for1], 115, 20))); // QUANTIDADE
				$pacote1[9] = ''; // MEGA
				$pacote1[10] = capturaValor2(substr($arquivo[$for1], -13));

				$valor = str_replace(array(".", ","), array("", "."), $pacote1[10]);
				$totalCapturado += $valor;

				if(empty($todosdescricaoServicos["{$pacote1[4]}"])) {
					$str = trim($pacote1[4]);
					$todosdescricaoServicos["{$str}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$pacote1[4]}"])) {
					$str = trim($pacote1[4]);
					$todosdescricaoServicos["{$str}"] += $valor;
				}else {
					$str = trim($pacote1[4]);
					$todosdescricaoServicos["{$str}"] += $valor;
				}

				// ==============================================================
				if($modoDebug == 1) {
					$str = trim($pacote1[4]);
					if(empty($debugPacote1["{$str}"])) {

						$debugPacote1["{$str}"] = 0;
					}

					if(empty($debugPacote1["{$str}"])) {

						$debugPacote1["{$str}"] += $valor;
					}else {

						$debugPacote1["{$str}"] += $valor;
					}
				}
				// ==============================================================

				$linhaPacote1 = 
					 $pacote1[1].';'
					.$pacote1[2].';'
					.$pacote1[3].';'
					.iconv("UTF-8", "Windows-1252",$pacote1[4]).';'
					.$pacote1[5].';'
					.$pacote1[6].';'
					.$pacote1[7].';'
					.$pacote1[8].';'
					.$pacote1[9].';'
					.$pacote1[10]
					.";\r\n";

				if (!fwrite($fp, $linhaPacote1)) { 
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}
				$linhaPacote1 = '';
		}
		$capturar03 = 0;

	//********************************************************************************************************************************************************************	
	//*****[ CONSUMO DE DADOS ]*****

		$formatoDataDados = "([0-9]{2}\/[0-9]{2}\/[0-9]{2})";
		$formatoHoraDados = "([0-9]{2}:[0-9]{2}:[0-9]{2})";

		$formatoDados1 = "({$formatoDataDados}([\s]+){$formatoHoraDados}([\W\w\s]+)((([0-9]{0,})[.])*([0-9]{1,})[\s]*MB([\s]*)[0-9]{1,}[\s]*KB)([\W\w]*)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

		if(preg_match($formatoDados1, utf8_encode($arquivo[$for1]))) {

			$capturar05 = 1;
			$encontrouDados = 0;

			if ($contadorDados1 ==  0) {

				$descicaoDados1 =    array(
										"Internet-TarifaçãoemMB/KB(Continuação)"	=> "Internet - Tarifação MB/KB",
										"Internet-TarifaçãoemMB/KB"					=> "Internet - Tarifação MB/KB",
										"InternetRollover-TarifaçãoemGB/MB"			=> "Internet rollover - tarifação em GB/MB",
										"Internet-VivoWap-TarifaçãoemMB/KB"			=> "Internet - Vivo Wap - Tarifação MB/KB"
										);

				$linhaEspacosRemovidosDados1 = '';
				$linhaEspacosRemovidosDados1 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2], 0, 183))));
				$copiaDescicaoDados1 = $descicaoDados1;

				foreach($copiaDescicaoDados1 as $novaDescDados1) {

					if(key($copiaDescicaoDados1) == $linhaEspacosRemovidosDados1) {
						$descricaoDados1 = $novaDescDados1;
						$encontrouDados1 = 1;
					}
					next($copiaDescicaoDados1);
				}
				if($encontrouDados1 == 0 AND $contadorDados1 ==  0) {
					$descDados1 = utf8_encode(substr($arquivo[$conta2-1], 0, 55)).'-';
					$todosdescricoesNaoCadastrados["{$descDados1}"] = 1;
					$alertaDescricaoNaocadastrado = 1;
				}
			}
			$contadorDados1 += 1;
		}
		else {
			$contadorDados1 = 0;
			$encontrouDados1 = 0;
		}

		if($capturar05 == 1) {

			if(strlen($descricaoDados1) > 2 ) {

				$dados1[1] = $numero_origem; // NUMERO
				$dados1[2] = capturaData2('DD/MM/AA', utf8_encode(substr($arquivo[$for1], 0, 50)));  // DATA DOS DADOS.
				$dados1[3] = capturaHora2('00:00:00', utf8_encode(substr($arquivo[$for1], 10, 60))); // HORA DOS DADOS.
				$dados1[4] = trim($descricaoDados1); //DESCRIÇÂO

				$dados1[5] = capturaApenasNumero(substr($arquivo[$for1], 40, 15)); // NUMERO DESTINO - CAPTURA SÓ NUMERO.
				if(capturaNumeroDiscado($dados1[5])) {
					$dados1[5] = capturaNumeroDiscado($dados1[5]); // NUMERO DESTINO - VALIDA SE CONTÉM DE 8 OU MAIS NÚMEROS.
				}else {
					$dados1[5] = ''; // NUMERO DESTINO
				}

				$dados1[6] = ''; // OPERADORA
				$dados1[7] = ''; // MINUTOS
				$dados1[8] = ''; // QUANTIDADE

				$dados1[9] = capturaMBKB (substr($arquivo[$for1], 105, 30)); // CONSUMO DADOS
				$dados1[9] = number_format($dados1[9], 1, ',', '.'); 	   // CONSUMO DADOS
				$dados1[10] = trim(substr($arquivo[$for1], -13)); // VALOR - POR CAUSA DOS MB QUE POUSEM VIRGULA o VALOR SE TORNA O 2 VALOR A CAPTURAR

				$valor = str_replace(array(".", ","), array("", "."), $dados1[10]);
				$totalCapturado += $valor;

				if(empty($todosdescricaoServicos["{$dados1[4]}"])) {
					$str = trim($dados1[4]);
					$todosdescricaoServicos["{$str}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$dados1[4]}"])) {
					$str = trim($dados1[4]);
					$todosdescricaoServicos["{$str}"] += $valor;
				}else {
					$str = trim($dados1[4]);
					$todosdescricaoServicos["{$str}"] += $valor;
				}

				// ==============================================================
				if($modoDebug == 1) {
					$str = trim($dados1[4]);
					if(empty($debugConsumoDados["{$str}"])) {

						$debugConsumoDados["{$str}"] = 0;
					}

					if(empty($debugConsumoDados["{$str}"])) {

						$debugConsumoDados["{$str}"] += $valor;
					}else {

						$debugConsumoDados["{$str}"] += $valor;
					}
				}
				// ==============================================================

				$linhaDados1 = 
					 $dados1[1].';'
					.$dados1[2].';'
					.$dados1[3].';'
					.iconv("UTF-8", "Windows-1252",$dados1[4]).';'
					.$dados1[5].';'
					.$dados1[6].';'
					.$dados1[7].';'
					.$dados1[8].';'
					.$dados1[9].';'
					.$dados1[10]
					.";\r\n";

				if (!fwrite($fp, $linhaDados1)) { 
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}
				$linhaDados1 = '';
			}
		}
		$capturar05 = 0;

	// **********************************************************************************************************************************************************************

		//-------------    >>>>>>>>>>>>       SERVIÇOS CONTRATADOS       <<<<<<<<<<      --------------------

		// CAPTURA POR BLOCO.

		if($capturaServicosContratados > 0) {
			$conta2 = $conta;

			$formatoServicosContratados2 = "((^[]|^[\s]*)(Descrição)[\s]{0,60}(Período)[\s]{0,20}(Incluso)[\s]{0,10}(Plano\/Pacote)[\w\W]+)";

			$flagServicosContratados2 = utf8_encode(substr($arquivo[$conta2-1], 0, 200));
			if(preg_match($formatoServicosContratados2, $flagServicosContratados2)) {

				$servicosContratados3 = 1;
			}
		}

		$listaEncerraServicosContratados3 = array("Subtotal", "Número");
		str_replace($listaEncerraServicosContratados3, "", utf8_encode(substr($arquivo[$conta], 0, 200)), $bloqueiaServicosContratados3);
		if($bloqueiaServicosContratados3 > 0) {
			$servicosContratados3 = 0;
		}

		if($servicosContratados3 > 0) {

			$servicosCadastrados2 = array(

								"GESTÃODECUSTOLIGHT"				=> "GESTÃO DE CUSTO LIGHT",
								"GOREADBUSINESSB2" 					=> "GO READ BUSINESS B2",

								"VIVOPROTEGE20GB"					=> "VIVO PROTEGE 20GB",
								"VIVOPROTEGE100GB"					=> "VIVO PROTEGE 100GB",
								"VIVOPROTEGE1T"						=> "VIVO PROTEGE 1T",
								);

			// $servicosCadastrados2 = array( // MODO DEBUG

								// "GESTÃODECUSTOLIGHT"				=> "GESTÃO DE CUSTO LIGHT", // "Assinatura - Serviços Contratados",
								// "VIVOPROTEGE20GB"				=> "VIVO PROTEGE 20GB", 	// "Assinatura - Serviços Contratados",
								// "VIVOPROTEGE100GB"				=> "VIVO PROTEGE 100GB", 	// "Assinatura - Serviços Contratados",
								// "GOREADBUSINESSB2" 				=> "GO READ BUSINESS B2", 	// "Assinatura - Serviços Contratados",
								// "VIVOPROTEGE1T"					=> "VIVO PROTEGE 1T" 		// "Assinatura - Serviços Contratados"
								// );

			$encontrouServ3 = 0;
			$servicosEspacosRemovidos3  = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$for1], 0, 45))));
			$copiaServicosCadastrados3 = $servicosCadastrados2;
			foreach($copiaServicosCadastrados3 as $novaDescServicos3) {

				if(key($copiaServicosCadastrados3) == $servicosEspacosRemovidos3) {

					$descricaoServicosContratados3 = $novaDescServicos3;
					$encontrouServ3 = 1;
					$capturar077 = 1;

				}
				next($copiaServicosCadastrados3);
			}

			if($encontrouServ3 == 0) {
				$descricoesServicosNaoCadastrados = utf8_encode(substr($arquivo[$conta], 0, 45));
				$todosdescricoesNaoCadastrados["{$descricoesServicosNaoCadastrados}"] = 1;
				$alertaDescricaoNaocadastrado = 1;
				$encontrouServ3 = 0;
			}

		}

		if($capturar077 == 1) {

			if($descricaoServicosContratados3 == 'NAO CAPTURAR') {

			}else {
				
				if($numero_origem == '') {
					// MANTÉM O NÚMERO DE ORIGEM.
					$numero_origem = $numeroDaConta;
				}else {
					// UTILIZA O CÓDIGO DA CONTA COMO NÚMERO DE ORIGEM.
				}

				$pacote2[1] = $numero_origem; // NUMERO
				$pacote2[2] = ''; // DATA
				$pacote2[3] = ''; // HORA
				$pacote2[4] = $descricaoServicosContratados3; //DESCRIÇÂO
				$pacote2[5] = ''; // NUMERO DESTINO
				$pacote2[6] = ''; // OPERADORA
				$pacote2[7] = ''; // MINUTOS
				$pacote2[8] = ''; // QUANTIDADE
				$pacote2[9] = ''; // KB
				$pacote2[10] = capturaValor2(trim(substr($arquivo[$conta], -13))); // VALOR

				$valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
				$totalCapturado += $valor;

				if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
					$str = trim($descricaoLigacao1);
					$todosdescricaoServicos["{$pacote2[4]}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
					$str = trim($pacote2[4]);
					$todosdescricaoServicos["{$str}"] += $valor;
				}else {
					$str = trim($pacote2[4]);
					$todosdescricaoServicos["{$str}"] += $valor;
				}

				// ==============================================================
				if($modoDebug == 1) {
					$str = trim($pacote2[4]);
					if(empty($debugServicosContratados["{$str}"])) {

						$debugServicosContratados["{$str}"] = 0;
					}

					if(empty($debugServicosContratados["{$str}"])) {

						$debugServicosContratados["{$str}"] += $valor;
					}else {

						$debugServicosContratados["{$str}"] += $valor;
					}
				}
				// ==============================================================

				$linhaPacote2 = 
					$pacote2[1].';'
					.$pacote2[2].';'
					.$pacote2[3].';'
					.iconv("UTF-8", "Windows-1252",$pacote2[4]).';'
					.$pacote2[5].';'
					.$pacote2[6].';'
					.$pacote2[7].';'
					.$pacote2[8].';'
					.$pacote2[9].';'
					.$pacote2[10]
					."\r\n";

				if (!fwrite($fp, $linhaPacote2)) {
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}
			}
		}
		$capturar077 = 0;  // ENCERRA APÓS CAPTURA.

	// **********************************************************************************************************************************************************************
	// **********************************************************************************************************************************************************************

		//-------------    >>>>>>>>>>>>       SERVIÇOS CONTRATADOS       <<<<<<<<<<      --------------------
		$servicosCadastrados = array(
								"APLICATIVOSESSENCIAIS"				=> "APLICATIVOS ESSENCIAIS",
								"ASSINATURASEMFRANQUIA"				=> "ASSINATURA SEM FRANQUIA",
								"ANTIIDENTIFICADORDECHAMADAS"		=> "ANTIIDENTIFICADOR DE CHAMADAS",

								"CONTADETMENSAL"					=> "CONTA DET MENSAL",
								"CONTASEMIDETALHADA"				=> "CONTA SEMI DETALHADA",

								"COMBOVOZSVCNACIONAL300"			=> "COMBO VOZ SVC NACIONAL 300",
								"COMBOVOZSVCNACIONAL600"			=> "COMBO VOZ SVC NACIONAL 600",
								"COMBOVOZSVCNACIONAL3000"			=> "COMBO VOZ SVC NACIONAL 3000",

								"DESVIODECHAMADAS"					=> "DESVIO DE CHAMADAS",

								"FRANQDOBROINTERNET24MPJ"			=> "FRANQ DOBRO INTERNET 24M PJ",
								"FRANQUIAINTERNET"					=> "FRANQUIA INTERNET",
								"FRANQUIATORPEDO"					=> "FRANQUIA TORPEDO",
								"FRANQUIAINTERNETCOMPARTILHADA"		=> "FRANQUIA INTERNET COMPARTILHADA",
								"FRANQUIAINTERNETDOUBLEPLAY"		=> "FRANQUIA INTERNET DOUBLE PLAY",

								"INTERNETMOVEL300MBEMP4G"			=> "INTERNET MOVEL 300MB EMP 4G",
								"INTERNETMOVEL600MBEMP4G"			=> "INTERNET MOVEL 600MB EMP 4G",
								"INTERNETMOVEL600MBEMP3G"			=> "INTERNET MOVEL 600MB EMP 3G",

								"INTERNETMOVEL3GBEMPRESA"			=> "INTERNET MOVEL 3GB EMPRESA",
								"INTERNETMOVEL3GBGOV4G"				=> "INTERNET MOVEL 3GB GOV 4G",
								"INTERNETMOVEL3GBEMP4G"				=> "INTERNET MOVEL 3GB EMP 4G",
								"INTERNETMOVEL20GBEMP4G"			=> "INTERNET MOVEL 20GB EMP 4G",
								"INTERNETMOVEL10GBEMP4G"			=> "INTERNET MOVEL 10GB EMP 4G",
								"INTERNETMOVEL10GBGOV4G"			=> "INTERNET MOVEL 10GB GOV 4G",
								"INTERNETMOVEL5GBEMP4G"				=> "INTERNET MOVEL 5GB EMP 4G",
								"INTERNETMOVEL120MBEMP3G"			=> "INTERNET MOVEL 120MB EMP 3G",

								"INTERNETBOX5GBEMP4G"				=> "INTERNET BOX 5GB EMP 4G",
								"INTERNETBOX20GBEMP4G"				=> "INTERNET BOX 20GB EMP 4G",
								"INTERNETBOX40GBEMP4G"				=> "INTERNET BOX 40GB EMP 4G",
								"INTERNETBOX80GBEMP4G"				=> "INTERNET BOX 80GB EMP 4G",
								"INTERNETMOVEL120MBEMP4G"			=> "INTERNET MOVEL 120MB EMP 4G",
								"INTERNETMOVEL5GBGOV4G"				=> "INTERNET MOVEL 5GB GOV 4G",
								"INTERNETMOVEL5GBEMP4G"				=> "INTERNET MOVEL 5GB EMP 4G",

								"INTRAGRUPOLOCAL2.000MINUTOS"		=> "INTRAGRUPO LOCAL 2.000 MINUTOS",
								"INTRAGRUPOLOCAL3.000MINUTOS"		=> "INTRAGRUPO LOCAL 3.000MINUTOS",
								"INTRAGRUPOZEROUNIVC1RAIZ"			=> "INTRAGRUPO ZERO UNI VC1 RAIZ",
								"INTRAGRUPOZEROUNINACIONAL"			=> "INTRAGRUPO ZERO UNI NACIONAL",

								"PACOTELD1"							=> "PACOTE LD 1",
								"PACOTELD01"						=> "PACOTE LD 01",
								"PACOTE2000SMSPJ"					=> "PACOTE 2000 SMS PJ",
								"PACOTEGESTAOCOMPLETO"				=> "PACOTE GESTAO COMPLETO",
								"PACOTEVIVOTRAVELVOZ50MIN"			=> "PACOTE VIVO TRAVEL VOZ 50 MIN",
								"PAC2CAIXAPOSTALILIM"				=> "PAC2 CAIXA POSTAL ILIM",
								"PAC10MINFLEXIND"					=> "PAC 10 MIN FLEX IND",
								"PAC50MINFLEXCOMP"					=> "PAC 50 MIN FLEX COMP",
								"PAC100MINFLEXCOMP"					=> "PAC 100 MIN FLEX COMP",
								"PAC100MINFLEXIND"					=> "PAC 100 MIN FLEX IND",
								"PAC200MINFLEXCOMP"					=> "PAC 200 MIN FLEX COMP",
								"PAC200MINFLEXIND"					=> "PAC 200 MIN FLEX IND",
								"PAC500MINFLEXCOMP"					=> "PAC 500 MIN FLEX COMP",
								"PACCORPLDROAMINGFLEX"				=> "PAC CORP LD ROAMING FLEX",
								"PACROAMFORAAREAFLEX"				=> "PAC ROAM FORA AREA FLEX",
								"PLANOINTERNETBOXPJ"				=> "PLANO INTERNET BOX PJ",
								"PLANOBASEINTERNETPJ"				=> "PLANO BASE INTERNET PJ",

								"SMARTEMPRESAS2GB"					=> "SMART EMPRESAS 2GB",
								"SMARTEMPRESAS5GB"					=> "SMART EMPRESAS 5GB",
								"SMARTEMPRESAS0.5GB"				=> "SMART EMPRESAS 0.5GB",
								"SMARTEMPRESAS0.5GBD"				=> "SMART EMPRESAS 0.5GB D",
								"SMARTEMPRESAS7GB"					=> "SMART EMPRESAS 7GB",
								"SMARTEMPRESAS25GBD"				=> "SMART EMPRESAS 25GB D",
								"SMARTEMPRESAS25GB"					=> "SMART EMPRESAS 25GB",
								"SMARTEMPRESAS10GB"					=> "SMART EMPRESAS 10GB",
								"SMARTEMPRESAS10GBD"				=> "SMART EMPRESAS 10GB D",
								"SMARTEMPRESAS50GB"					=> "SMART EMPRESAS 50GB",
								"SMARTEMPRESAS5GBD"					=> "SMART EMPRESAS 5GB D",

								"SERVICOGESTAO"						=> "SERVIÇO GESTÃO",
								"SERVICOGESTAOVOZ"					=> "SERVIÇO GESTÃO VOZ",
								"SERVIÇOSMSFLEX"					=> "SERVIÇO  SMS FLEX",

								"SMSCOMPARTILHADO1000PJ"			=> "SMS COMPARTILHADO 1000 PJ",
								"SMSCOMPARTILHADOPJDEP"				=> "SMS COMPARTILHADO PJ DEP",

								"SVCNACIONAL600MINUTOS"				=> "SVC NACIONAL 600 MINUTOS",
								"SUSPENSAOAPEDIDO"					=> "SUSPENSÃO A PEDIDO",

								"TROCASERIALISENTA"					=> "TROCA SERIAL ISENTA",
								"TUGO"								=> "TU GO",

								"VANTAGEMINTRAGRUPOREGIONAL"		=> "VANTAGEM INTRAGRUPO REGIONAL",
								"VANTAGEMINTRAGRUPONACIONAL"		=> "VANTAGEM INTRAGRUPO NACIONAL",

								"VIVOINTERNETIPHONETIPO8"			=> "VIVO INTERNET IPHONE TIPO 8",
								"VIVONEWS"							=> "VIVO NEWS",
								"VIVOPROTEGE5GB"					=> "VIVO PROTEGE 5GB",
								"VIVOPROTEGE1T"						=> "VIVO PROTEGE 1T",

								"VIVOAVISAEMPRESAS"					=> "VIVO AVISA EMPRESAS",
								"VIVOAVISAEMPRGRATIS"				=> "VIVO AVISA EMPR GRATIS",
								"VIVOAVISAGRATIS"					=> "VIVO AVISA GRATIS/ANUAL",
								"VIVOCHAMADAEMESPERA"				=> "VIVO CHAMADA EM ESPERA",
								"VIVOAVISA_PROMOCIONAL"				=> "VIVO AVISA PROMOCIONAL",
								"VIVOAVISAANUAL"					=> "VIVO AVISA GRATIS/ANUAL",
								"VIVOAVISA"							=> "VIVO AVISA GRATIS/ANUAL",
								"VIVOEMPRESASFLEXASSIN"				=> "VIVO EMPRESAS FLEX ASSIN",
								"VIVOTRAVELMENSALMUNDO"				=> "VIVO TRAVEL MENSAL MUNDO"
								);

		// APENAS SERÁ PROCESSADO UM POR VEZ APÓS DETECTAR O FLAG ABAIXO.
		// CASO TENHA OUTROS SERVIÇOS SEQUENCIADOS A BAIXO SERÁ CAPTURA CASO TENHA ATÉ O LIMITE.
		if($capturaServicosContratados > 0) {
			$conta2 = $conta;

			$formatoServicosContratados = "((^[]|^[\s]*)(Período)[\s]{0,50}(Incluso)[\s]{0,20}(Plano\/Pacote)[\s]{0,20}(Utilizado)[\s]{0,20}(Minutos\/Unidades)[\w\W]+)";

			$flagServicosContratados = utf8_encode(substr($arquivo[$conta2-1], 0, 200));
			if(preg_match($formatoServicosContratados, $flagServicosContratados)) {

				$servicosContratados = 1;
			}

		}
		if($servicosContratados > 0) {

			$encontrouServ1 = 0;
			$servicosEspacosRemovidos  = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 45))));
			$servicosEspacosRemovidos1 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2+1], 0, 45))));
			$copiaServicosCadastrados = $servicosCadastrados;
			foreach($copiaServicosCadastrados as $novaDescServicos) {

				if(key($copiaServicosCadastrados) == $servicosEspacosRemovidos) {

					$descricaoServicosContratados1 = $novaDescServicos;
					$encontrouServ1 = 1;
					$capturar07 = 1;

				}
				// if(key($copiaServicosCadastrados) == $servicosEspacosRemovidos1) {

					// $descricaoServicosContratados2 = $novaDescServicos;

				// }
				next($copiaServicosCadastrados);
			}

			if($encontrouServ1 == 0) {
				$descricoesServicosNaoCadastrados = utf8_encode(substr($arquivo[$conta], 0, 45)).' - '.$for1;
				$todosdescricoesNaoCadastrados["{$descricoesServicosNaoCadastrados}"] = 1;
				$alertaDescricaoNaocadastrado = 1;
				$encontrouServ1 = 0;
			}
		}
		$servicosContratados = 0;
		if($capturar07 == 1) {

			if($descricaoServicosContratados1 == 'NAO CAPTURAR') {

			}else {

				if($numero_origem == '') {
					$numero_origem = $numeroDaConta;
				}else {
					// UTILIZA O CÓDIGO DA CONTA COMO NÚMERO DE ORIGEM.
				}

				$pacote2[1] = $numero_origem; // NUMERO
				$pacote2[2] = ''; // DATA
				$pacote2[3] = ''; // HORA
				$pacote2[4] = $descricaoServicosContratados1; //DESCRIÇÂO
				$pacote2[5] = ''; // NUMERO DESTINO
				$pacote2[6] = ''; // OPERADORA
				$pacote2[7] = ''; // MINUTOS
				$pacote2[8] = ''; // QUANTIDADE
				$pacote2[9] = ''; // KB
				$pacote2[10] = capturaValor2(trim(substr($arquivo[$conta], -13))); // VALOR

				$valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
				$totalCapturado += $valor;

				if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
					$str = trim($descricaoLigacao1);
					$todosdescricaoServicos["{$pacote2[4]}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
					$str = trim($pacote2[4]);
					$todosdescricaoServicos["{$str}"] += $valor;
				}else {
					$str = trim($pacote2[4]);
					$todosdescricaoServicos["{$str}"] += $valor;
				}

				// ==============================================================
				if($modoDebug == 1) {
					$str = trim($pacote2[4]);
					if(empty($debugServicosContratados["{$str}"])) {

						$debugServicosContratados["{$str}"] = 0;
					}

					if(empty($debugServicosContratados["{$str}"])) {

						$debugServicosContratados["{$str}"] += $valor;
					}else {

						$debugServicosContratados["{$str}"] += $valor;
					}
				}
				// ==============================================================

				$linhaPacote2 = 
					$pacote2[1].';'
					.$pacote2[2].';'
					.$pacote2[3].';'
					.iconv("UTF-8", "Windows-1252",$pacote2[4]).';'
					.$pacote2[5].';'
					.$pacote2[6].';'
					.$pacote2[7].';'
					.$pacote2[8].';'
					.$pacote2[9].';'
					.$pacote2[10]
					."\r\n";

				if (!fwrite($fp, $linhaPacote2)) {
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}

				//==========================[ CAPTURA PARA UMA CARACTERISTICA ESPECIAL ]===========================
				//=================================================================================================

				// if(strlen(trim($descricaoServicosContratados2)) > 10 AND capturaValor2(substr($arquivo[$conta2+1], -13)) <> NULL) {
					
				// // if(strlen(trim($descricaoServicosContratados2)) > 10) {
					// $pacote2[1] = $numero_origem; // NUMERO
					// $pacote2[2] = ''; // DATA
					// $pacote2[3] = ''; // HORA
					// $pacote2[4] = $descricaoServicosContratados2; //DESCRIÇÂO
					// $pacote2[5] = ''; // NUMERO DESTINO
					// $pacote2[6] = ''; // OPERADORA
					// $pacote2[7] = ''; // MINUTOS
					// $pacote2[8] = ''; // QUANTIDADE
					// $pacote2[9] = ''; // KB
					// $pacote2[10] = capturaValor2(trim(substr($arquivo[$conta2+1], -13))); // VALOR

					// $valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
					// $totalCapturado += $valor;

					// if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
						// $str = trim($descricaoLigacao1);
						// $todosdescricaoServicos["{$pacote2[4]}"] = 0;
					// }

					// if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
						// $str = trim($pacote2[4]);
						// $todosdescricaoServicos["{$str}"] += $valor;
					// }else {
						// $str = trim($pacote2[4]);
						// $todosdescricaoServicos["{$str}"] += $valor;
					// }

					// // ==============================================================
					// if($modoDebug == 1) {
						// $str = trim($pacote2[4]);
						// if(empty($debugServicosContratados["{$str}"])) {

							// $debugServicosContratados["{$str}"] = 0;
						// }

						// if(empty($debugServicosContratados["{$str}"])) {

							// $debugServicosContratados["{$str}"] += $valor;
						// }else {

							// $debugServicosContratados["{$str}"] += $valor;
						// }
					// }
					// // ==============================================================

					// $linhaPacote2 = 
						// $pacote2[1].';'
						// .$pacote2[2].';'
						// .$pacote2[3].';'
						// .iconv("UTF-8", "Windows-1252",$pacote2[4]).';'
						// .$pacote2[5].';'
						// .$pacote2[6].';'
						// .$pacote2[7].';'
						// .$pacote2[8].';'
						// .$pacote2[9].';'
						// .$pacote2[10]
						// ."\r\n";

					// if (!fwrite($fp, $linhaPacote2)) {
						// print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
						// exit;
					// }
					// $descricaoServicosContratados2 = '';
				// }
				
				
				
				
				
				

				
				
				
				
				
				
				
				//=================================================================================================
				//=================================================================================================

				//**************************************************************************************
				//***** CAPTURA ESPECIAL PARA VALORES QUE PERTENCEM AO SERVIÇO A CIMA SEM DESCIÇÃO *****
				//**************************************************************************************

				$conta = $conta2;

				// ***** OBSERVAÇÕES SOBRE A FORMA DE CAPTURA ABAIXO *****

				// UTILIZANDO UM FOR O MESMO TERÁ QUE CONCLUIR O LOOP PERCORRENDO UM BLOCO DE LINHAS E
				// PODENDO ATINGIR CONDIÇÃO MAIS AFRENTE DE CAPTURA E QUE NÃO PERTENCE AO SERVIÇO.
				// UTILIZANDO OS "IFs" O MESMO PARA A CAPTURA QUANDO NÃO ENCONTRA CARACTERÍSTICAS DE UMA SEQUENCIA AO SERVIÇO.

				str_replace("/", "", utf8_encode(substr($arquivo[$conta2+1], 0, 55)), $qtdeBarras1);
				if($qtdeBarras1 > 3 and capturaValor2(trim(substr($arquivo[$conta2+1], -13))) <> NULL) {
					$pacote2[1] = $numero_origem; // NUMERO
					$pacote2[2] = ''; // DATA
					$pacote2[3] = ''; // HORA
					$pacote2[4] = $descricaoServicosContratados1; //DESCRIÇÂO
					$pacote2[5] = ''; // NUMERO DESTINO
					$pacote2[6] = ''; // OPERADORA
					$pacote2[7] = ''; // MINUTOS
					$pacote2[8] = ''; // QUANTIDADE
					$pacote2[9] = ''; // KB
					$pacote2[10] = capturaValor2(trim(substr($arquivo[$conta2+1], -13)));

					$valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
					$totalCapturado += $valor;

					if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
						$str = trim($descricaoLigacao1);
						$todosdescricaoServicos["{$pacote2[4]}"] = 0;
					}

					if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
						$str = trim($pacote2[4]);
						$todosdescricaoServicos["{$str}"] += $valor;
					}else {
						$str = trim($pacote2[4]);
						$todosdescricaoServicos["{$str}"] += $valor;
					}

					// ==============================================================
					if($modoDebug == 1) {
						$str = trim($pacote2[4]);
						if(empty($debugServicosContratados["{$str}"])) {

							$debugServicosContratados["{$str}"] = 0;
						}

						if(empty($debugServicosContratados["{$str}"])) {

							$debugServicosContratados["{$str}"] += $valor;
						}else {

							$debugServicosContratados["{$str}"] += $valor;
						}
					}
					// ==============================================================

					$linhaPacote2 = 
						$pacote2[1].';'
						.$pacote2[2].';'
						.$pacote2[3].';'
						.iconv("UTF-8", "Windows-1252", $pacote2[4]).';'
						.$pacote2[5].';'
						.$pacote2[6].';'
						.$pacote2[7].';'
						.$pacote2[8].';'
						.$pacote2[9].';'
						.$pacote2[10]
						."\r\n";

					if (!fwrite($fp, $linhaPacote2)) {
						print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
						exit;
					}
					//***************
					str_replace("/", "", utf8_encode(substr($arquivo[$conta2+2], 0, 55)), $qtdeBarras2);
					if($qtdeBarras2 > 3 and capturaValor2(trim(substr($arquivo[$conta2+2], -13))) <> NULL) {
						$pacote2[1] = $numero_origem; // NUMERO
						$pacote2[2] = ''; // DATA
						$pacote2[3] = ''; // HORA
						$pacote2[4] = $descricaoServicosContratados1; //DESCRIÇÂO
						$pacote2[5] = ''; // NUMERO DESTINO
						$pacote2[6] = ''; // OPERADORA
						$pacote2[7] = ''; // MINUTOS
						$pacote2[8] = ''; // QUANTIDADE
						$pacote2[9] = ''; // KB
						$pacote2[10] = capturaValor2(trim(substr($arquivo[$conta2+2], -13)));

						$valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
						$totalCapturado += $valor;

						if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
							$str = trim($descricaoLigacao1);
							$todosdescricaoServicos["{$pacote2[4]}"] = 0;
						}

						if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
							$str = trim($pacote2[4]);
							$todosdescricaoServicos["{$str}"] += $valor;
						}else {
							$str = trim($pacote2[4]);
							$todosdescricaoServicos["{$str}"] += $valor;
						}

						// ==============================================================
						if($modoDebug == 1) {
							$str = trim($pacote2[4]);
							if(empty($debugServicosContratados["{$str}"])) {

								$debugServicosContratados["{$str}"] = 0;
							}

							if(empty($debugServicosContratados["{$str}"])) {

								$debugServicosContratados["{$str}"] += $valor;
							}else {

								$debugServicosContratados["{$str}"] += $valor;
							}
						}
						// ==============================================================

						$linhaPacote2 = 
							$pacote2[1].';'
							.$pacote2[2].';'
							.$pacote2[3].';'
							.iconv("UTF-8", "Windows-1252", $pacote2[4]).';'
							.$pacote2[5].';'
							.$pacote2[6].';'
							.$pacote2[7].';'
							.$pacote2[8].';'
							.$pacote2[9].';'
							.$pacote2[10]
							."\r\n";

						if (!fwrite($fp, $linhaPacote2)) {
							print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
							exit;
						}
						//***************
						str_replace("/", "", utf8_encode(substr($arquivo[$conta2+3], 0, 55)), $qtdeBarras3);
						if($qtdeBarras3 > 3 and capturaValor2(trim(substr($arquivo[$conta2+3], -13))) <> NULL) {
							$pacote2[1] = $numero_origem; // NUMERO
							$pacote2[2] = ''; // DATA
							$pacote2[3] = ''; // HORA
							$pacote2[4] = $descricaoServicosContratados1; //DESCRIÇÂO
							$pacote2[5] = ''; // NUMERO DESTINO
							$pacote2[6] = ''; // OPERADORA
							$pacote2[7] = ''; // MINUTOS
							$pacote2[8] = ''; // QUANTIDADE
							$pacote2[9] = ''; // KB
							$pacote2[10] = capturaValor2(trim(substr($arquivo[$conta2+3], -13)));

							$valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
							$totalCapturado += $valor;

							if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
								$str = trim($descricaoLigacao1);
								$todosdescricaoServicos["{$pacote2[4]}"] = 0;
							}

							if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
								$str = trim($pacote2[4]);
								$todosdescricaoServicos["{$str}"] += $valor;
							}else {
								$str = trim($pacote2[4]);
								$todosdescricaoServicos["{$str}"] += $valor;
							}

							// ==============================================================
							if($modoDebug == 1) {
								$str = trim($pacote2[4]);
								if(empty($debugServicosContratados["{$str}"])) {

									$debugServicosContratados["{$str}"] = 0;
								}

								if(empty($debugServicosContratados["{$str}"])) {

									$debugServicosContratados["{$str}"] += $valor;
								}else {

									$debugServicosContratados["{$str}"] += $valor;
								}
							}
							// ==============================================================

							$linhaPacote2 = 
								$pacote2[1].';'
								.$pacote2[2].';'
								.$pacote2[3].';'
								.iconv("UTF-8", "Windows-1252", $pacote2[4]).';'
								.$pacote2[5].';'
								.$pacote2[6].';'
								.$pacote2[7].';'
								.$pacote2[8].';'
								.$pacote2[9].';'
								.$pacote2[10]
								."\r\n";

							if (!fwrite($fp, $linhaPacote2)) {
								print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
								exit;
							}
							//***************
							str_replace("/", "", utf8_encode(substr($arquivo[$conta2+4], 0, 55)), $qtdeBarras4);
							if($qtdeBarras4 > 3 and capturaValor2(trim(substr($arquivo[$conta2+4], -13))) <> NULL) {
								$pacote2[1] = $numero_origem; // NUMERO
								$pacote2[2] = ''; // DATA
								$pacote2[3] = ''; // HORA
								$pacote2[4] = $descricaoServicosContratados1; //DESCRIÇÂO
								$pacote2[5] = ''; // NUMERO DESTINO
								$pacote2[6] = ''; // OPERADORA
								$pacote2[7] = ''; // MINUTOS
								$pacote2[8] = ''; // QUANTIDADE
								$pacote2[9] = ''; // KB
								$pacote2[10] = capturaValor2(trim(substr($arquivo[$conta2+4], -13)));

								$valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
								$totalCapturado += $valor;

								if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
									$str = trim($descricaoLigacao1);
									$todosdescricaoServicos["{$pacote2[4]}"] = 0;
								}

								if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
									$str = trim($pacote2[4]);
									$todosdescricaoServicos["{$str}"] += $valor;
								}else {
									$str = trim($pacote2[4]);
									$todosdescricaoServicos["{$str}"] += $valor;
								}

								// ==============================================================
								if($modoDebug == 1) {
									$str = trim($pacote2[4]);
									if(empty($debugServicosContratados["{$str}"])) {

										$debugServicosContratados["{$str}"] = 0;
									}

									if(empty($debugServicosContratados["{$str}"])) {

										$debugServicosContratados["{$str}"] += $valor;
									}else {

										$debugServicosContratados["{$str}"] += $valor;
									}
								}
								// ==============================================================

								$linhaPacote2 = 
									$pacote2[1].';'
									.$pacote2[2].';'
									.$pacote2[3].';'
									.iconv("UTF-8", "Windows-1252", $pacote2[4]).';'
									.$pacote2[5].';'
									.$pacote2[6].';'
									.$pacote2[7].';'
									.$pacote2[8].';'
									.$pacote2[9].';'
									.$pacote2[10]
									."\r\n";

								if (!fwrite($fp, $linhaPacote2)) {
									print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
									exit;
								}
								//***************
								str_replace("/", "", utf8_encode(substr($arquivo[$conta2+5], 0, 55)), $qtdeBarras5);
								if($qtdeBarras5 > 3 and capturaValor2(trim(substr($arquivo[$conta2+5], -13))) <> NULL) {
									$pacote2[1] = $numero_origem; // NUMERO
									$pacote2[2] = ''; // DATA
									$pacote2[3] = ''; // HORA
									$pacote2[4] = $descricaoServicosContratados1; //DESCRIÇÂO
									$pacote2[5] = ''; // NUMERO DESTINO
									$pacote2[6] = ''; // OPERADORA
									$pacote2[7] = ''; // MINUTOS
									$pacote2[8] = ''; // QUANTIDADE
									$pacote2[9] = ''; // KB
									$pacote2[10] = capturaValor2(trim(substr($arquivo[$conta2+5], -13)));

									$valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
									$totalCapturado += $valor;

									if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
										$str = trim($descricaoLigacao1);
										$todosdescricaoServicos["{$pacote2[4]}"] = 0;
									}

									if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
										$str = trim($pacote2[4]);
										$todosdescricaoServicos["{$str}"] += $valor;
									}else {
										$str = trim($pacote2[4]);
										$todosdescricaoServicos["{$str}"] += $valor;
									}

									// ==============================================================
									if($modoDebug == 1) {
										$str = trim($pacote2[4]);
										if(empty($debugServicosContratados["{$str}"])) {

											$debugServicosContratados["{$str}"] = 0;
										}

										if(empty($debugServicosContratados["{$str}"])) {

											$debugServicosContratados["{$str}"] += $valor;
										}else {

											$debugServicosContratados["{$str}"] += $valor;
										}
									}
									// ==============================================================

									$linhaPacote2 = 
										$pacote2[1].';'
										.$pacote2[2].';'
										.$pacote2[3].';'
										.iconv("UTF-8", "Windows-1252", $pacote2[4]).';'
										.$pacote2[5].';'
										.$pacote2[6].';'
										.$pacote2[7].';'
										.$pacote2[8].';'
										.$pacote2[9].';'
										.$pacote2[10]
										."\r\n";

									if (!fwrite($fp, $linhaPacote2)) {
										print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
										exit;
									}
									//***************
									str_replace("/", "", utf8_encode(substr($arquivo[$conta2+6], 0, 55)), $qtdeBarras6);
									if($qtdeBarras6 > 3 and capturaValor2(trim(substr($arquivo[$conta2+6], -13))) <> NULL) {
										$pacote2[1] = $numero_origem; // NUMERO
										$pacote2[2] = ''; // DATA
										$pacote2[3] = ''; // HORA
										$pacote2[4] = $descricaoServicosContratados1; //DESCRIÇÂO
										$pacote2[5] = ''; // NUMERO DESTINO
										$pacote2[6] = ''; // OPERADORA
										$pacote2[7] = ''; // MINUTOS
										$pacote2[8] = ''; // QUANTIDADE
										$pacote2[9] = ''; // KB
										$pacote2[10] = capturaValor2(trim(substr($arquivo[$conta2+6], -13)));

										$valor = str_replace(array(".", ","), array("", "."), $pacote2[10]);
										$totalCapturado += $valor;

										if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
											$str = trim($descricaoLigacao1);
											$todosdescricaoServicos["{$pacote2[4]}"] = 0;
										}

										if(empty($todosdescricaoServicos["{$pacote2[4]}"])) {
											$str = trim($pacote2[4]);
											$todosdescricaoServicos["{$str}"] += $valor;
										}else {
											$str = trim($pacote2[4]);
											$todosdescricaoServicos["{$str}"] += $valor;
										}

										// ==============================================================
										if($modoDebug == 1) {
											$str = trim($pacote2[4]);
											if(empty($debugServicosContratados["{$str}"])) {

												$debugServicosContratados["{$str}"] = 0;
											}

											if(empty($debugServicosContratados["{$str}"])) {

												$debugServicosContratados["{$str}"] += $valor;
											}else {

												$debugServicosContratados["{$str}"] += $valor;
											}
										}
										// ==============================================================

										$linhaPacote2 = 
											$pacote2[1].';'
											.$pacote2[2].';'
											.$pacote2[3].';'
											.iconv("UTF-8", "Windows-1252", $pacote2[4]).';'
											.$pacote2[5].';'
											.$pacote2[6].';'
											.$pacote2[7].';'
											.$pacote2[8].';'
											.$pacote2[9].';'
											.$pacote2[10]
											."\r\n";

										if (!fwrite($fp, $linhaPacote2)) {
											print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
											exit;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$capturar07 = 0;  // ENCERRA APÓS CAPTURA.

	//**********************************************************************************************************************************************************************

	//*****[ DESCONTOS ]*****

		if($for1 > 10) {
			str_replace("DescontodoNúmeroPeríodoValorR$", "", str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-1], 0, 200))), $flagCapturaDados);
			if($flagCapturaDados > 0) {
				$capturar09 = 1;
			}
			str_replace(array ("NúmerodaConta:", "Subtotal"), "", str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 200))), $flagEncerraCapturaDados);
			if($flagEncerraCapturaDados > 0) {
				$capturar09 = 0;
			}
		
		}

		if($capturar09 == 1) {

			// =====[ CAPTURA O NÚMERO QUE UTILIZOU O SERVIÇO NA DESCRIÇÃO ]=====
			$flagPegaNumeroUtilizouServico = "([0-9]{2,3}[-]*[0-9]{5}[-]*[0-9]{4})";

			if(preg_match($flagPegaNumeroUtilizouServico, utf8_encode(substr($arquivo[$conta2], 0, 70)))) {

				preg_match_all($flagPegaNumeroUtilizouServico, utf8_encode(substr($arquivo[$conta2], 0, 70)), $numero_origem_capturado);
				$numero_origem = capturaApenasNumero($numero_origem_capturado[0][0]); // NUMERO DESTINO - CAPTURA SÓ NUMERO.
				$numero_origem = capturaNumeroDiscado($numero_origem); // VALIDA SE CONTÉM DE 8 OU MAIS NÚMEROS.

			}

			if($numero_origem == '') {
				$numero_origem = $numeroDaConta;
			}

			$descontos1[1] = $numero_origem; // NUMERO
			$descontos1[2] = ''; // DATA
			$descontos1[3] = ''; // HORA

			$descontos1[4] = trim(utf8_encode(substr($arquivo[$conta], 0, 70))); //DESCRIÇÂO
			// $descontos1[4] = 'DESCONTO E PROMOCAO'; //DESCRIÇÂO

			$descontos1[5] = ''; // NUMERO DESTINO
			$descontos1[6] = ''; // OPERADORA
			$descontos1[7] = ''; // MINUTOS
			$descontos1[8] = ''; // QUANTIDADE
			$descontos1[9] = ''; // MEGA
			$descontos1[10] = capturaValor2(trim(substr($arquivo[$conta], -13))); // VALOR

			$valor = str_replace(array(".", ","), array("", "."), $descontos1[10]);
			$totalCapturado += $valor;

			if(empty($todosdescricaoServicos["{$descontos1[4]}"])) {
				$str = trim($descontos1[4]);
				$todosdescricaoServicos["{$descontos1[4]}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$descontos1[4]}"])) {
				$str = trim($descontos1[4]);
				$todosdescricaoServicos["{$str}"] += $valor;
			}else {
				$str = trim($descontos1[4]);
				$todosdescricaoServicos["{$str}"] += $valor;
			}

	// ================ [ AGRUPAMENTO DOS DESCONTOS] =================

			if($modoDebug == 1) {

				$str = trim($descontos1[4]);
				if(empty($debugDescontos1["{$str}"])) {

					$debugDescontos1["{$str}"] = 0;
				}

				if(empty($debugDescontos1["{$str}"])) {

					$debugDescontos1["{$str}"] += $valor;
				}else {

					$debugDescontos1["{$str}"] += $valor;
				}
			}
	// ===============================================================

			$linhaDescontos1 = 
				$descontos1[1].';'
				.$descontos1[2].';'
				.$descontos1[3].';'
				.iconv("UTF-8", "Windows-1252", $descontos1[4]).';'
				.$descontos1[5].';'
				.$descontos1[6].';'
				.$descontos1[7].';'
				.$descontos1[8].';'
				.$descontos1[9].';'
				.$descontos1[10]
				."\r\n";

			if (!fwrite($fp, $linhaDescontos1)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
			$linhaDescontos1 = '';
		}

	//*****************************************************************************************************************************
	//*****[ CANCELAMENTO ]*****

		$itensCancelamentoEncontrado = 0;
		$encontrarItensCancelamento =    array(
								"PARCELAMENTOS",
								"CANCELAMENTO"
								);
								
		if($for1 > 10) {
			str_replace($encontrarItensCancelamento, "", substr($arquivo[$conta2-2], 0, 183), $itensCancelamentoEncontrado);
		}

		if($itensCancelamentoEncontrado > 0) {
			$capturar11 = 1;
		}
		// ----------[ ENCERRA CAPTURA DOS DESCONTOS ]----------
		$encerraCapturaCancelamento = 0;
		$encontrarItensEncerraCancelamento =    array(
								"Subtotal",
								"Conta:"
								);
		str_replace($encontrarItensEncerraCancelamento, "", utf8_encode(substr($arquivo[$conta2], 0, 183)), $encerraCapturaCancelamento);
		if($encerraCapturaCancelamento > 0) {
			$capturar11 = 0;
		}

		if($capturar11 == 1) {

			// =====[ CAPTURA O NÚMERO QUE UTILIZOU O SERVIÇO NA DESCRIÇÃO ]=====
			$flagPegaNumeroUtilizouServico = "([0-9]{2,3}[-]*[0-9]{5}[-]*[0-9]{4})";

			if(preg_match($flagPegaNumeroUtilizouServico, utf8_encode(substr($arquivo[$conta2], 0, 70)))) {

				preg_match_all($flagPegaNumeroUtilizouServico, utf8_encode(substr($arquivo[$conta2], 0, 70)), $numero_origem_capturado);
				$numero_origem = capturaApenasNumero($numero_origem_capturado[0][0]); // NUMERO DESTINO - CAPTURA SÓ NUMERO.
				$numero_origem = capturaNumeroDiscado($numero_origem); // VALIDA SE CONTÉM DE 8 OU MAIS NÚMEROS.

			}

			if($numero_origem == '') {
				$numero_origem = $numeroDaConta;
			}

			$parcelamentos1[1] = $numero_origem; // NUMERO
			$parcelamentos1[2] = ''; // DATA
			$parcelamentos1[3] = ''; // HORA
			$parcelamentos1[4] = trim(utf8_encode(substr($arquivo[$conta2], 0, 70))); //DESCRIÇÂO
			$parcelamentos1[5] = ''; // NUMERO DESTINO
			$parcelamentos1[6] = ''; // OPERADORA
			$parcelamentos1[7] = ''; // MINUTOS
			$parcelamentos1[8] = ''; // QUANTIDADE
			$parcelamentos1[9] = ''; // MEGA
			$parcelamentos1[10] = capturaValor2(trim(substr($arquivo[$conta], -13))); // VALOR

			$valor = str_replace(array(".", ","), array("", "."), $parcelamentos1[10]);
			$totalCapturado += $valor;

			if(empty($todosdescricaoServicos["{$parcelamentos1[4]}"])) {
				$str = trim($parcelamentos1[4]);
				$todosdescricaoServicos["{$parcelamentos1[4]}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$parcelamentos1[4]}"])) {
				$str = trim($parcelamentos1[4]);
				$todosdescricaoServicos["{$str}"] += $valor;
			}else {
				$str = trim($parcelamentos1[4]);
				$todosdescricaoServicos["{$str}"] += $valor;
			}

	// ======== [ CAPTURA DO PARCELAMENTO SEPARADO PARA ANALISE ] ========

			if($modoDebug == 1) {
				$str = trim($parcelamentos1[4]);
				if(empty($debugParcelamentos["{$str}"])) {

					$debugParcelamentos["{$str}"] = 0;
				}

				if(empty($debugParcelamentos["{$str}"])) {

					$debugParcelamentos["{$str}"] += $valor;
				}else {

					$debugParcelamentos["{$str}"] += $valor;
				}
			}
	// ===================================================================

			$linhaParcelamentos1 = 
				$parcelamentos1[1].';'
				.$parcelamentos1[2].';'
				.$parcelamentos1[3].';'
				.iconv("UTF-8", "Windows-1252", $parcelamentos1[4]).';'
				.$parcelamentos1[5].';'
				.$parcelamentos1[6].';'
				.$parcelamentos1[7].';'
				.$parcelamentos1[8].';'
				.$parcelamentos1[9].';'
				.$parcelamentos1[10]
				."\r\n";

			if (!fwrite($fp, $linhaParcelamentos1)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}

			$numero_origem = '';
		}

	//********************************************************************************************************************************************
	//***** [ MULTAS, JUROS, ENCARGOS FINANCEIROS ] *****
		$formatoMultasJuros = "(([\W\w]*)Descrição([\W\w]*)Referência([\W\w]*)ValorR([\W\w]*))";
		
		if($for1 > 10) {
			$flagMultaJuros = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-1], 0, 200)));
		
			if(preg_match($formatoMultasJuros, $flagMultaJuros)) {

				$iniciaCapturaMultaJuros = 1;

			}
		}
		if($iniciaCapturaMultaJuros == 1) {

			$capturar13 = 1;
			if(utf8_encode(substr($arquivo[$conta2], 0, 8)) == 'Subtotal') {

				$valorSubtotalMultaJuros = str_replace(array(".", ","), array("", "."), capturaValor2(trim(substr($arquivo[$conta], -13))));
				$subtotalMultaJuros = $valorSubtotalMultaJuros;

				if($subtotalMultaJuros == $somaMultaJuros) {
					$capturar13 = 0;
				}
				$iniciaCapturaMultaJuros = 0;

			}else {

				$valorMultaJuros = str_replace(array(".", ","), array("", "."), capturaValor2(trim(substr($arquivo[$conta], -13))));
				$somaMultaJuros += $valorMultaJuros;

			}
		}

		if($capturar13 == 1) {

			// =====[ CAPTURA O NÚMERO QUE UTILIZOU O SERVIÇO NA DESCRIÇÃO ]=====
			$flagPegaNumeroUtilizouServico = "([0-9]{2,3}[-]*[0-9]{5}[-]*[0-9]{4})";

			if(preg_match($flagPegaNumeroUtilizouServico, utf8_encode(substr($arquivo[$conta2], 0, 70)))) {

				preg_match_all($flagPegaNumeroUtilizouServico, utf8_encode(substr($arquivo[$conta2], 0, 70)), $numero_origem_capturado);
				$numero_origem = capturaApenasNumero($numero_origem_capturado[0][0]); // NUMERO DESTINO - CAPTURA SÓ NUMERO.
				$numero_origem = capturaNumeroDiscado($numero_origem); // VALIDA SE CONTÉM DE 8 OU MAIS NÚMEROS.

			}

			if($numero_origem == '') {
				$numero_origem = $numeroDaConta;
			}

			$multaJuros1[1] = $numero_origem; // NUMERO
			$multaJuros1[2] = ''; // DATA
			$multaJuros1[3] = ''; // HORA
			$multaJuros1[4] = 'MULTAS E JUROS'; //DESCRIÇÂO
			$multaJuros1[5] = ''; // NUMERO DESTINO
			$multaJuros1[6] = ''; // OPERADORA
			$multaJuros1[7] = ''; // MINUTOS
			$multaJuros1[8] = ''; // QUANTIDADE
			$multaJuros1[9] = ''; // MEGA
			$multaJuros1[10] = capturaValor2(trim(substr($arquivo[$conta], -13)));

			$valor = str_replace(array(".", ","), array("", "."), $multaJuros1[10]);
			$totalCapturado += $valor;

			if(empty($todosdescricaoServicos["{$multaJuros1[4]}"])) {
				$str = trim($multaJuros1[4]);
				$todosdescricaoServicos["{$multaJuros1[4]}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$multaJuros1[4]}"])) {
				$str = trim($multaJuros1[4]);
				$todosdescricaoServicos["{$str}"] += $valor;
			}else {
				$str = trim($multaJuros1[4]);
				$todosdescricaoServicos["{$str}"] += $valor;
			}

	// ======== [ CAPTURA DO PARCELAMENTO SEPARADO PARA ANALISE ] ========

			if($modoDebug == 1) {
				$str = trim($multaJuros1[4]);
				if(empty($debugMultaJuros["{$str}"])) {

					$debugMultaJuros["{$str}"] = 0;
				}

				if(empty($debugMultaJuros["{$str}"])) {

					$debugMultaJuros["{$str}"] += $valor;
				}else {

					$debugMultaJuros["{$str}"] += $valor;
				}
			}
	// ===================================================================

			$linhaMultaJuros1 = 
				$multaJuros1[1].';'
				.$multaJuros1[2].';'
				.$multaJuros1[3].';'
				.iconv("UTF-8", "Windows-1252", $multaJuros1[4]).';'
				.$multaJuros1[5].';'
				.$multaJuros1[6].';'
				.$multaJuros1[7].';'
				.$multaJuros1[8].';'
				.$multaJuros1[9].';'
				.$multaJuros1[10]
				."\r\n";

			if (!fwrite($fp, $linhaMultaJuros1)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}

			$numero_origem = '';

		}
		$capturar13 = 0;

	//**********************************************************************************************************************************************************************

		$conta += 1;

	}//FECHA WHILE

	// Fecha o arquivo
	fclose($fp);

	return array($totalCapturado, $totalFatura);

} // FIM FUNCTION

?>

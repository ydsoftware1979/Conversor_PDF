<?php

include_once("funcoesParaFaturas2.php");

set_time_limit(1440);

function converteFaturaVivoFixo(string $arquivoConvertidoTXT) {

	$nomeArquivo = $arquivoConvertidoTXT;

	$arquivo = file($nomeArquivo);
	$fp = fopen($nomeArquivo.".csv", "w+");

	if($arquivo == false) die('O arquivo não existeeeeeeeeee.');
	if($fp == false) die('O arquivo não foi criado.');

	$conta = 0;
	$conta2 = 0;

	$inicio = 0;
	$capturar01 = 0;
	$capturar02 = 0;
	$capturar03 = 0;
	$capturar04 = 0;
	$capturar05 = 0;
	$capturar06 = 0;
	$capturar07 = 0;
	
	$capturar12 = 0;
	$capturar13 = 0;

	$todosdescricaoDemonstrativoDespesas = array();
	$todosdescricaoServicos = array();
	$descricaoServicosCabecalho = array();
	$demonstrativoDespesas = array();
	$descricaoConsumoOutrasOperadoras = array();

	$capturaDemonstrativoDespesas2 = 0;
	$iniciaCapturaPlanos = 0;
	$itensColetaDetalhamento = 0;
	$totalFatura = 0;
	$totalCapturado = 0;
	$contadorLigacao1 = 0;
	$encontrouOutrosDescontos = '';

	$descricaoOutrosDescontos1 = '';

	$verivicaValores = '';
	$descricaoServ = array();
	
	
	$pegaOutras = 0;
	$pegaTelefonica = 0;
	$soEsse1 = 0;
	$soEsse2 = 0;

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

	$numero_origem = '';
	$codigoCliente = '';
	$iniciaCaptura = 0;
	$contou = 0;
	$passaUmTotal = 0;
	$passaUm = true;
	$encontrouCodigoCliente = 0;
	$capturaCodigoCliente = 0;
	$listaEspacos = array(" ","  ","   ","    ","     ","      ","       ","        ","        ","          ");

	$capturaDemonstrativoDespesas = 0;
	$para = 0;

	$quantidadeTotalLinhas = count($arquivo);

	for ($for1 = 0; $for1 < $quantidadeTotalLinhas; $for1++) {

		$conta2 = $for1;
		$conta = $for1;

		// ***************************[ NOME DA ORIGEM - [ CÓDIGO DO CLIENTE ] ]*************************
		if($encontrouCodigoCliente == 0) {
			$encontrarCodigoCliente = array("Código  do cliente");

			$linhaCodigoCliente = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 180)));
			str_replace(str_replace($listaEspacos, "", $encontrarCodigoCliente), "", $linhaCodigoCliente, $capturaCodigoCliente);

			if($capturaCodigoCliente == 1) {
				for($for2=20 ; $for2 < 45 ; $for2++) {
					if(utf8_encode(substr($arquivo[$conta], $for2, 1)) == 'D') {
						$para = 1;
					}

					if($para == 0) {
						$codigoCliente .= str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta],$for2, 1)));
					}
				}
				$encontrouCodigoCliente = 1;
			}
		}
		//***********************************************************************************************

//=======================================================================================================================================================================

		$flagDemonstrativoDespesas1 = "((Seu)([\s]+)(Demonstrativo)([\s]+)(de)([\s]+)(Despesas)([\s]+))";
		if(preg_match($flagDemonstrativoDespesas1, utf8_encode($arquivo[$for1]))) {
			$capturaDemonstrativoDespesas = 1;
		}
		$flagDemonstrativoDespesas2 = "((PRESTADORA)([\s]+)(TELEFONCIA)([\w\W]+))";
		if(preg_match($flagDemonstrativoDespesas2, utf8_encode($arquivo[$for1]))) {
			$capturaDemonstrativoDespesas = 1;
		}
		$flagDemonstrativoDespesas2 = "((TOTAL)[\s]+(A)[\s]+(PAGAR)[\s]+)";
		if(preg_match($flagDemonstrativoDespesas2, $arquivo[$for1])) {
			$capturaDemonstrativoDespesas = 0;
		}
		//-----------------------------------------------------------------------------------------------------------

// >>>>>>>>>>>>>>>   TERÁ QUE ARMAZENAR EM MEMORIA, COMPARAR NO DETALHAMENTO E UTILIZAR O QUE NÃO ESTÁ NO RESUMIDO   <<<<<<<<<<<<<<<<<<<<<

			if($capturaDemonstrativoDespesas == 1) {

				$listaDemonstrativoDespesas =    array(
									"LigaçõesLocais"											=> "Ligações Locais",
									"LigaçõesLocaisExcedentes"									=> "Ligações Locais Excedentes",
									"LigaçõesNacionaisdeLongaDistância"							=> "Ligações Nacionais de Longa Distância",
									"LigaçõesLocaisparaCelular(VC1)"							=> "Ligações Locais para Celular (VC1)",
									"LigaçõesNacionaisdeLongaDistânciaparaCelular(VC2/VC3)"		=> "Ligações Nacionais de Longa Distância para Celular (VC2/VC3)"
									);

				$linhaEspacosRemovidosDemonstrativoDespesas = '';
				$linhaEspacosRemovidosDemonstrativoDespesas = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$for1],0,71)));
				$copia_DemonstrativoDespesas = $listaDemonstrativoDespesas;

				foreach($copia_DemonstrativoDespesas as $novaDemonstrativoDespesas) {

					if(key($copia_DemonstrativoDespesas) == $linhaEspacosRemovidosDemonstrativoDespesas) {

						$descricaoOutrosDescontos = $novaDemonstrativoDespesas;
						$capturar12 = 1;

					}
					next($copia_DemonstrativoDespesas);
			}

			if($capturar12 == 1) {

				if(strlen($numero_origem) > 0) {
				$arrayCampos12[1] = str_replace("-","",trim($numero_origem));
			}else {
				$arrayCampos12[1] = str_replace("-","",trim($codigoCliente));
				$arrayCampos12[5] = $arrayCampos12[1];
			}

			$arrayCampos12[2] = ''; // DATA
			$arrayCampos12[3] = ''; // HORA
			$arrayCampos12[4] = $descricaoOutrosDescontos; //DESCRIÇÂO
			// $arrayCampos11[5] = ''; // NUMERO DESTINO
			$arrayCampos12[6] = ''; // OPERADORA
			$arrayCampos12[7] = ''; // MINUTOS
			$arrayCampos12[8] = ''; // QUANTIDADE
			$arrayCampos12[9] = ''; // MEGA
			$arrayCampos12[10] = capturaValor2(substr($arquivo[$conta], -13)); // VALOR

			$tudo12 = 
				 ';'	// OPERADORA
				.$codigoCliente.';'		// NOME DA ORIGEM
				.$arrayCampos12[1].';'	// NUMERO TELEFONE
				.$arrayCampos12[1].';'	// RAMAL ASSOCIADO
				.$arrayCampos12[2].';'	// DATA LIGACAO
				.$arrayCampos12[3].';'	// HORA LIGACAO
				.$arrayCampos12[5].';'	// TELEFONE CHAMADO
				.$arrayCampos12[1].';'	// TRONCO
				//.$arrayCampos11[4].';'	// DESCRICAO
				.iconv("UTF-8", "Windows-1252",$arrayCampos12[4]).';'
				.$arrayCampos12[7].';'	// DURACAO
				.number_format($arrayCampos12[10], 2, ',', '.').';'	// TARIFA
				.';'	// DEPTO.
				.';'	// CONTA DE FATURA
				.';'	// MES_REF
				."\r\n";

			$str = $descricaoOutrosDescontos;
			if(empty($descricaoServicosCabecalho["{$str}"])) {
				$descricaoServicosCabecalho["{$str}"] = '';
				$descricaoServicosCabecalho["{$str}"] = $tudo12;
			}

			if(empty($descricaoServicosCabecalho["{$str}"])) {
				$descricaoServicosCabecalho["{$str}"] = $tudo12;
			}else {
				$descricaoServicosCabecalho["{$str}"] = $tudo12;
			}
			}
			$capturar12 = 0;
		}
		
// ==========================================================================================================================
// =============================== [ CAPTURA DOS SERVIÇOS  CONSUMIDOS POR OUTRAS OPERADORAS ] ===============================
// ==========================================================================================================================

	$flagDemonstrativoDespesas3 = "(MinutosUtilizados\b)";
	if(preg_match($flagDemonstrativoDespesas3, str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$for1],0,70))))) {
		$capturaDemonstrativoDespesas2 = 1;
	}
	$flagDemonstrativoDespesas3 = "(TOTALGERALAPAGAR)";
	if(preg_match($flagDemonstrativoDespesas3, str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$for1],0,70))))) {
		$capturaDemonstrativoDespesas2 = 0;
	}
	
	//-----------------------------------------------------------------------------------------------------------

	if($capturaDemonstrativoDespesas2 == 1) {

		$listaConsumoOutrasOperadoras =    array(
								"LigaçõesLocais\b"											=> "Ligações Locais",
								"LigaçõesLocaisExcedentes"									=> "Ligações Locais Excedentes",
								"LigaçõesNacionaisdeLongaDistância"							=> "Ligações Nacionais de Longa Distância",
								"LigaçõesLocaisparaCelular\(VC1\)"							=> "Ligações Locais para Celular (VC1)",
								"LigaçõesNacionaisdeLongaDistânciaparaCelular\(VC2\/VC3\)"	=> "Ligações Nacionais de Longa Distância para Celular (VC2/VC3)"
								);

		$linhaEspacosRemovidosConsumoOutrasOperadoras = '';
		$linhaEspacosRemovidosConsumoOutrasOperadoras = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$for1],0,70)));
		$copia_ConsumoOutrasOperadoras = $listaConsumoOutrasOperadoras;

		foreach($copia_ConsumoOutrasOperadoras as $novaConsumoOutrasOperadoras) {

			$formatoDesc = "(".key($copia_ConsumoOutrasOperadoras).")";
			if(preg_match($formatoDesc, $linhaEspacosRemovidosConsumoOutrasOperadoras)) {

				// echo '<br/> => '.str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$for1],0,70))).' - '.$for1;

				$descricaoOutrasOperadoras = $novaConsumoOutrasOperadoras;
				$capturar13 = 1;

			}
			next($copia_ConsumoOutrasOperadoras);
		}
		if($capturar13 == 1) {

			if(strlen($numero_origem) > 0) {
				$arrayCampos13[1] = str_replace("-","",trim($numero_origem));
			}else {
				$arrayCampos13[1] = str_replace("-","",trim($codigoCliente));
				$arrayCampos13[5] = $arrayCampos13[1]; // NUMERO DESTINO
			}

			$arrayCampos13[2] = ''; // DATA
			$arrayCampos13[3] = ''; // HORA
			$arrayCampos13[4] = $descricaoOutrasOperadoras; //DESCRIÇÂO

			$arrayCampos13[6] = ''; // OPERADORA
			$arrayCampos13[7] = ''; // MINUTOS
			$arrayCampos13[8] = ''; // QUANTIDADE
			$arrayCampos13[9] = ''; // MEGA
			$arrayCampos13[10] = capturaValor2(substr($arquivo[$conta], -13)); // VALOR

			$tudo13 = 
				 ';'	// OPERADORA
				.$codigoCliente.';'		// NOME DA ORIGEM
				.$arrayCampos13[1].';'	// NUMERO TELEFONE
				.$arrayCampos13[1].';'	// RAMAL ASSOCIADO
				.$arrayCampos13[2].';'	// DATA LIGACAO
				.$arrayCampos13[3].';'	// HORA LIGACAO
				.$arrayCampos13[5].';'	// TELEFONE CHAMADO
				.$arrayCampos13[1].';'	// TRONCO
				//.$arrayCampos11[4].';'	// DESCRICAO
				.iconv("UTF-8", "Windows-1252",$arrayCampos13[4]).';'
				.$arrayCampos13[7].';'	// DURACAO
				.number_format($arrayCampos13[10], 2, ',', '.').';'	// TARIFA
				.';'	// DEPTO.
				.';'	// CONTA DE FATURA
				.';'	// MES_REF
				."\r\n";

			$str = $descricaoOutrasOperadoras;
			if(empty($descricaoConsumoOutrasOperadoras["{$str}"])) {
				$descricaoConsumoOutrasOperadoras["{$str}"] = '';
				$descricaoConsumoOutrasOperadoras["{$str}"] = $tudo13;
			}

			if(empty($descricaoConsumoOutrasOperadoras["{$str}"])) {
				$descricaoConsumoOutrasOperadoras["{$str}"] = $tudo13;
			}else {
				$descricaoConsumoOutrasOperadoras["{$str}"] = $tudo13;
			}

		}
		$capturar13 = 0;
	}
		
		
		
		
		
		
		
		

//=======================================================================================================================================================================

		$linhaInicioCaptura = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 180)));
		str_replace("TOTALGERALAPAGAR", "", $linhaInicioCaptura, $flagIniciarCaptura);

		if($flagIniciarCaptura == 1) {
			$iniciaCaptura = 1;
		}

// ATENÇÃO .: A CAPTURA ESTA SENDO INICIADA A PARTIR DE UMA DETERMINADA PARTE DO ARQUIVO, PORQUE INICIANDO NO COMEÇO DO ARQUIVO OCORRE DUPLICIDADE.
		if($iniciaCaptura == 1) {


//=========================================================================================================================================================
			// O SISTEMA DE COMPARAÇÃO DO RESUMIDO NO CABECALHO COMO O DETALHAMENTO DEVERÁ UTILIZAR O SISTEMA DE DETECÇÃO ABAIXO.

			if(trim(str_replace(array(" ", "  ", "   ", "    "), "", utf8_encode(substr($arquivo[$conta], 0, 180)))) == 'PrestadoraTelefonica') {
				// echo '<br/> => '.$esse = trim(str_replace(array(" ", "  ", "   ", "    "), "", utf8_encode(substr($arquivo[$conta], 0, 180))));
				// echo '<br/>LIGAÇÕES DE OUTRAS OPERADORAS';
				$pegaTelefonica = 1;
			}

			if($pegaTelefonica == 1) {

				$formatoLigacao1 = "({$formatoDataLigacao}[\s]*{$formatoHoraLigacao}[\s]+{$formatoDuracaoLigacao}([\W\w]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";
				if(preg_match($formatoLigacao1, substr($arquivo[$for1],0,183))) {

					$soEsse1 += 1;
					if($soEsse1 == 1) {
						// echo '<br/>DETALHAMENTO VIVO => '. utf8_encode(substr($arquivo[$conta2-2], 0, 180)).' - '.$for1;
					}
				}else {
					$soEsse1 = 0;
				}
			}

			//---------------------

			if(trim(str_replace(array(" ", "  ", "   ", "    "), "", utf8_encode(substr($arquivo[$conta], 0, 180)))) == 'PrestadoraTelemar') {
				// echo '<br/> => '.$esse = trim(str_replace(array(" ", "  ", "   ", "    "), "", utf8_encode(substr($arquivo[$conta], 0, 180))));
				// echo '<br/>LIGAÇÕES DE OUTRAS OPERADORAS';
				$pegaOutras = 1;
			}

			if($pegaOutras == 1) {

				$formatoLigacao1 = "({$formatoDataLigacao}[\s]*{$formatoHoraLigacao}[\s]+{$formatoDuracaoLigacao}([\W\w]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";
				if(preg_match($formatoLigacao1, substr($arquivo[$for1],0,183))) {

					$soEsse2 += 1;
					if($soEsse2 == 1) {
						// echo '<br/>OUTRAS OPERADORAS => '. utf8_encode(substr($arquivo[$conta2-2], 0, 180)).' - '.$for1;
					}
				}else {
					$soEsse2 = 0;
				}
			}
//=========================================================================================================================================================





			if(utf8_encode(trim(substr($arquivo[$conta], 0, 8))) == 'Ponto   ' AND $conta < 16) {
				// echo 'ATENCAO: ESSA CONTA E PONTO DE COMUNICACAO - NAO SUPORTADO POR ESSE CONVERSOR';
				return 'ATENCAO: ESSA CONTA E PONTO DE COMUNICACAO - NAO SUPORTADO POR ESSE CONVERSOR';
				exit();
			}

	// ----- ESSA CAPTURA NÃO SERÁ UTILIZADO E O VALOR DA FATURA DEVERÁ SER INFORMADO PELO USUÁRIO -----
	
			// echo 'HAHAHAHAHAHA';
			// exit();
	
			$totalGeralPagar = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 180))));
			str_replace("TOTALGERALAPAGAR", "", $totalGeralPagar, $flagTotalPagar);
			if($flagTotalPagar > 0) {

				$capturaServicosContratados = 1;
				
				$conta2 = $conta;
				
				$valorTotalPagar = "(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2})";

				if(preg_match($valorTotalPagar, $arquivo[$conta], $pegaTotalPagar)) {
					preg_match_all($valorTotalPagar, $arquivo[$conta], $pegaTotalPagar);
					echo '<br/> ===> '.$totalFatura = str_replace(array(".", ","), array("", "."), $pegaTotalPagar[0][0]);
					exit();
				}else {
					if(preg_match($valorTotalPagar, substr($arquivo[$conta2+1], 0, 1024))) {
						preg_match_all($valorTotalPagar, substr($arquivo[$conta2+1], 0, 1024), $pegaTotalPagar);
						$totalFatura = str_replace(array(".", ","), array("", "."), $pegaTotalPagar[0][0]);
					}
				}
			}

	//=======================================================================================================================================================================

			// **********************[ SEGUNDO "MARIA" É PARA PEGAR TUDO, ATÉ AS LETRAS ]************************
			$formatoNumeroUtilizouServico = "((POLIMPORT)[\s]+(COMERCIO)[\s]+(E)[\s]+(EXPORTACAO)[\s]+(LTDA))";

			if(preg_match($formatoNumeroUtilizouServico, substr($arquivo[$for1], 0, 183))) {
				$numero_origem = capturaNumeroDiscado(substr($arquivo[$conta], 105, 50));
			}

			// *******************************[ CAPTURA O NÚMERO DA FATURA ]*************************************
			if(utf8_encode(trim(substr($arquivo[$conta], 0, 18))) == 'Número   da fatura' OR utf8_encode(trim(substr($arquivo[$conta], 0, 17))) == 'Número  da fatura') {
				$numero_fatura = trim(capturaApenasNumero (substr($arquivo[$conta], 25, 20)));
			}

	//=======================================================================================================================================================================
		// ---------------------------------------
		// ---------- [ TIPO LIGAÇÕES ] ----------
		// ---------------------------------------
		$formatoDataLigacao = "[0-9]{2}[\/][0-9]{2}[\/][0-9]{4}";
		$formatoHoraLigacao = "[0-9]{2}[:][0-9]{2}[:][0-9]{2}";
		$formatoDuracaoLigacao = "([0-9]{2}[:][0-9]{2}[:][0-9]{2})";

		$formatoLigacao1 = "({$formatoDataLigacao}[\s]*{$formatoHoraLigacao}[\s]+{$formatoDuracaoLigacao}([\W\w]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

		if(preg_match($formatoLigacao1, substr($arquivo[$for1],0,183))) {

			$capturar01 = 1;

			if ($contadorLigacao1 ==  0) {

				$descicaoLigacoes1 = array(
								"LigaçõesLocais"										=> "Ligações Locais",
								"LigaçõesLocaisparaCelular\(VC1\)"						=> "Ligações Locais para Celular (VC1)",
								"LigaçõesNacionaisdeLongaDistância"						=> "Ligações Nacionais de Longa Distância",
								"LigaçõesNacionaisdeLongaDistânciaparaCelular\(VC2/VC3\)" => "Ligações Nacionais de Longa Distância para Celular (VC2/VC3)",
								"UsodeRecursoMóvelLocal"								=> "Uso de Recurso Móvel Local",
								"UsodeRecursoLongaDistânciaDDD"							=> "Uso de Recurso Longa Distância DDD",
								"UsodeRecursoMóveldeLongaDistância"						=> "Uso de Recurso Móvel de Longa Distância",
								"LigaçõesNacionaisdeLongaDistância"						=> "Ligações Nacionais de Longa Distância",
								"LIGACAOCELULARAREA"									=> "NAO CAPTURAR",
								"DataHoraDuração"										=> "NAO CAPTURAR",
								);
				$linhaEspacosRemovidos1 = '';
				$linhaEspacosRemovidos1T = '';
				$linhaEspacosRemovidos1 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2],0,183))));
				$linhaEspacosRemovidos1T = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-3],0,183))));

				$copia_descicaoLigacoes1 = $descicaoLigacoes1;
				foreach($copia_descicaoLigacoes1 as $novaDescLigacoes) {
					
					$formatoDesc = "(".key($copia_descicaoLigacoes1).")";
					if(preg_match($formatoDesc, $linhaEspacosRemovidos1) OR preg_match($formatoDesc, $linhaEspacosRemovidos1T)) {

						if($novaDescLigacoes == 'NAO CAPTURAR') {
							$encontrouLigacao1 = 1;
						}else {
							$descricaoLigacao1 = $novaDescLigacoes;
							$encontrouLigacao1 = 1;
						}
					}
					next($copia_descicaoLigacoes1);
				}
				$capturaDescricao = 0;
			}

			// PARA DESCIÇÕES NÃO CADASTRADAS.
			if ($encontrouLigacao1 == 0 AND $contadorLigacao1 == 0) {
				$descricaoLigacao1 = 'SERVIÇO NÃO CADASTRADO';
				$descLigacao1 = utf8_encode(substr($arquivo[$conta2-2],0,183));
				$todosdescricoesNaoCadastrados["{$descLigacao1}"] = 1;
				$alertaDescricaoNaocadastrado = 1;
			}
			$contadorLigacao1 += 1;
		}else {
			$contadorLigacao1 = 0;
			$encontrouLigacao1 = 0;
		}

		if($capturar01 == 1) {

			if(strlen($numero_origem) > 4) {
				$arrayCampos[1] = str_replace("-","",$numero_origem);
			}else {
				$arrayCampos[1] = str_replace("-","",$codigoCliente);
			}

			$arrayCampos[2] = capturaData2('DD/MM/AAAA', $arquivo[$for1]); // COLETA DE DATA.

			if(capturaHora2('00:00:00', substr($arquivo[$for1],13,19))) {
				$arrayCampos[3] = capturaHora2('00:00:00', substr($arquivo[$for1],13,19));   // COLETA DE HORA
			}else {
				$arrayCampos[3] = capturaHora2('00:00:00', substr($arquivo[$for1],120,10));
			}

			$arrayCampos[4] = $descricaoLigacao1; //DESCRIÇÂO
			$arrayCampos[5] = trim(capturaNumeroDiscado(substr($arquivo[$for1],63,25))); // NUMERO DESTINO
			$arrayCampos[6] = ''; // OPERADORA
			$arrayCampos[7] = capturaDuracao2('00:00:00', substr($arquivo[$for1],26, 13)); // COLETA DE DURAÇÃO
			$arrayCampos[7] = decimo ('00:00:00', $arrayCampos[7]);
			$arrayCampos[8] = ''; // QUANTIDADE
			$arrayCampos[9] = ''; // MEGA
			$arrayCampos[10] = capturaValor2 (substr($arquivo[$for1],-13)); // VALOR

			$valor = $arrayCampos[10];
			$totalCapturado += $valor;

			$str = iconv("UTF-8", "Windows-1252",$arrayCampos[4]);
			if(empty($todosdescricaoServicos["{$str}"])) {
				$todosdescricaoServicos["{$str}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$str}"])) {
				$todosdescricaoServicos["{$str}"] += $valor;
			}else {
				$todosdescricaoServicos["{$str}"] += $valor;
			}

			$tudo = 
				 ';'	// OPERADORA
				.$codigoCliente.';'	// NOME DA ORIGEM
				.$arrayCampos[1].';'	// NUMERO TELEFONE
				.$arrayCampos[1].';'	// RAMAL ASSOCIADO
				.$arrayCampos[2].';'	// DATA LIGACAO
				.$arrayCampos[3].';'	// HORA LIGACAO
				.$arrayCampos[5].';'	// TELEFONE CHAMADO
				.$arrayCampos[1].';'	// TRONCO
				// .$arrayCampos[4].';'	// DESCRICAO
				.iconv("UTF-8", "Windows-1252",$arrayCampos[4]).';'
				.$arrayCampos[7].';'	// DURACAO
				.number_format($arrayCampos[10], 2, ',', '.').';'	// TARIFA
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

			// $formatoDataLigacao = "[0-9]{2}[\/][0-9]{2}[\/][0-9]{4}";
			// $formatoHoraLigacao = "[0-9]{2}[:][0-9]{2}[:][0-9]{2}";
			// $formatoDuracaoLigacao = "([0-9]{2}[:][0-9]{2}[:][0-9]{2})";

			// $formatoLigacao1 = "({$formatoDataLigacao}[\s]+{$formatoHoraLigacao}[\s]+{$formatoDuracaoLigacao}([\W\w]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";

			// if(preg_match($formatoLigacao1, substr($arquivo[$for1], 0, 183))) {

				// $capturar01 = 1;

				// if ($contadorLigacao1 ==  0) {

					// $descicaoLigacoes1 = array(
									// "LigaçõesLocais"										=> "Ligações Locais",
									// "LigaçõesLocaisparaCelular(VC1)"						=> "Ligações Locais para Celular (VC1)",
									// "LigaçõesNacionaisdeLongaDistância"						=> "Ligações Nacionais de Longa Distância",
									// "LigaçõesNacionaisdeLongaDistânciaparaCelular(VC2/VC3)" => "Ligações Nacionais de Longa Distância para Celular (VC2/VC3)",
									// "UsodeRecursoMóvelLocal"								=> "Uso de Recurso Móvel Local",
									// "UsodeRecursoLongaDistânciaDDD"							=> "Uso de Recurso Longa Distância DDD",
									// "UsodeRecursoMóveldeLongaDistância"						=> "Uso de Recurso Móvel de Longa Distância",
									// "LigaçõesNacionaisdeLongaDistância"						=> "Ligações Nacionais de Longa Distância",
									// "LigaçõesLocaisparaCelular(VC1)"						=> "Ligações Locais para Celular (VC1)"
									// );

					// $linhaEspacosRemovidos1 = '';
					// $linhaEspacosRemovidos1 = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-2], 0, 183))));
					// $copia_descicaoLigacoes1 = $descicaoLigacoes1;
					// foreach($copia_descicaoLigacoes1 as $novaDescLigacoes) {
						// if(key($copia_descicaoLigacoes1) == $linhaEspacosRemovidos1) {
							// $descricaoLigacao1 = $novaDescLigacoes;
							// $encontrouLigacao1 = 1;
						// }
						// next($copia_descicaoLigacoes1);
					// }
				// }

				// PARA DESCIÇÕES NÃO CADASTRADAS.
				// if ($encontrouLigacao1 == 0 AND $contadorLigacao1 == 0) {
					// $descricaoLigacao1 = 'SERVIÇO NÃO CADASTRADO';
					// $descLigacao1 = utf8_encode(substr($arquivo[$conta2-2], 0, 183));
					// $todosdescricoesNaoCadastrados["{$descLigacao1}"] = 1;
					// $alertaDescricaoNaocadastrado = 1;
				// }
				// $contadorLigacao1 += 1;
			// }else {
				// $contadorLigacao1 = 0;
				// $encontrouLigacao1 = 0;
				// $descricaoLigacao1 = '';
			// }

			// if($capturar01 == 1) {

				// if(strlen($numero_origem) > 4) {
					// $arrayCampos[1] = str_replace("-", "", $numero_origem);
				// }else {
					// $arrayCampos[1] = str_replace("-", "", $codigoCliente);
				// }

				// $arrayCampos[2] = capturaData2('DD/MM/AAAA', $arquivo[$for1]); // COLETA DE DATA.

				// if(capturaHora2('00:00:00', substr($arquivo[$for1], 13, 19))) {
					// $arrayCampos[3] = capturaHora2('00:00:00', substr($arquivo[$for1], 13, 19));   // COLETA DE HORA
				// }else {
					// $arrayCampos[3] = capturaHora2('00:00:00', substr($arquivo[$for1], 120, 10));
				// }

				// $arrayCampos[4] = $descricaoLigacao1; //DESCRIÇÂO
				// $arrayCampos[5] = capturaNumeroDiscado(substr($arquivo[$for1], 63, 25)); // NUMERO DESTINO
				// $arrayCampos[6] = ''; // OPERADORA
				// $arrayCampos[7] = capturaDuracao2('00:00:00', substr($arquivo[$for1], 26, 13)); // COLETA DE DURAÇÃO
				// $arrayCampos[7] = decimo ('00:00:00', $arrayCampos[7]);
				// $arrayCampos[8] = ''; // QUANTIDADE
				// $arrayCampos[9] = ''; // MEGA
				// $arrayCampos[10] = capturaValor2 (substr($arquivo[$for1], -13)); // VALOR

				// $valor = str_replace(array(".", ","), array("", "."), $arrayCampos[10]);
				// $totalCapturado += $valor;

				// $str = iconv("UTF-8", "Windows-1252", $arrayCampos[4]);
				// if(empty($todosdescricaoServicos["{$str}"])) {
					// $todosdescricaoServicos["{$str}"] = 0;
				// }

				// if(empty($todosdescricaoServicos["{$str}"])) {
					// $todosdescricaoServicos["{$str}"] += $valor;
				// }else {
					// $todosdescricaoServicos["{$str}"] += $valor;
				// }

				// $tudo = 
					 // ';'	// OPERADORA
					// .$codigoCliente.';'	// NOME DA ORIGEM
					// .$arrayCampos[1].';'	// NUMERO TELEFONE
					// .$arrayCampos[1].';'	// RAMAL ASSOCIADO
					// .$arrayCampos[2].';'	// DATA LIGACAO
					// .$arrayCampos[3].';'	// HORA LIGACAO
					// .$arrayCampos[5].';'	// TELEFONE CHAMADO
					// .$arrayCampos[1].';'	// TRONCO
					// .$arrayCampos[4].';'	// DESCRICAO
					// .iconv("UTF-8", "Windows-1252", $arrayCampos[4]).';'
					// .$arrayCampos[7].';'	// DURACAO
					// .$arrayCampos[10].';'	// TARIFA
					// .';'	// DEPTO.
					// .';'	// CONTA DE FATURA
					// .';'	// MES_REF
					// ."\r\n";

				// if (!fwrite($fp, $tudo)) { 
					// print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					// return 'erro';
					// exit;
				// }
				// $tudo = '';
			// }
			// $capturar01 = 0;

	//=======================================================================================================================================================================
		// -----------------------------------------------------------
		// ---------- [ TIPO DADOS POR DADOS - ESTA EM MB ] ----------
		// -----------------------------------------------------------

			$itensDadosEncontrado = 0;
			$encontrarItensDados =    array(
									"Data          Hora                                                           Tipo",
									"xxxxxxxx"
									);
			str_replace($encontrarItensDados,"",substr($arquivo[$conta2-1], 0, 183), $itensDadosEncontrado);

			if($itensDadosEncontrado > 0) {
				$capturar05 = 1;
				// ----------[ APENAS PARA CAPTURA DA DESCRIÇÃO ]----------
				$itensDescricaoDadosEncontrado = 0;
				$encontrarItensDescricaoDados =    array(
										"xxx"
										);
				str_replace($encontrarItensDescricaoDados, "", utf8_encode(substr($arquivo[$conta2-2],0,183)), $itensDescricaoDadosEncontrado);
				if($itensDescricaoDadosEncontrado > 0) {
					$descricaoDados = substr($arquivo[$conta2-2], 0, 120); // CAPTURA A DESCRÇÃO DO SERVIÇO.

				}else {
					$descricaoDados = 'DESCRICAO NAO CADASTRADA -> '.$descricao_ligacao = substr($arquivo[$conta2-2], 0, 120);
					//$descricao_ligacao = 'DESCRICAO NAO CADASTRADA';
				}
			}

			// ----------[ ENCERRA CAPTURA DOS DADOS ]----------
			$encerraCapturaDados = 0;
			$encontrarItensEncerraDados =    array(
									"Subtotal",
									"Conta:"
									);
			str_replace($encontrarItensEncerraDados, "", utf8_encode(substr($arquivo[$conta2], 0, 183)), $encerraCapturaDados);
			if($encerraCapturaDados > 0) {
				$capturar05 = 0;
			}
			// ----------------------------------------------------

			if($capturar05 == 1) {

				if(strlen(trim($numero_origem)) > 0) {
					$arrayCampos5[1] = str_replace("-", "", $numero_origem); // NUMERO
				}else {
					$arrayCampos5[1] = str_replace("-", "", $numero_fatura);
				}

				$arrayCampos5[1] = $numero_origem; // NUMERO
				$arrayCampos5[2] = trim(substr($arquivo[$conta], 0, 10));
				$arrayCampos5[3] = trim(substr($arquivo[$conta], 11, 10));
				$arrayCampos5[4] = trim($descricaoDados); //DESCRIÇÂO
				$arrayCampos5[5] = ''; // NUMERO DESTINO
				$arrayCampos5[6] = ''; // OPERADORA
				$arrayCampos5[7] = ''; // MINUTOS
				$arrayCampos5[8] = ''; // QUANTIDADE
				$arrayCampos5[9] = trim(substr($arquivo[$conta], 113, 18)); // MEGA
				$arrayCampos5[10] = capturaValor2 (substr($arquivo[$conta], -13)); // VALOR

				$valor = str_replace(array(".", ","), array("", "."), $arrayCampos5[10]);
				$totalCapturado += $valor;

				$str = iconv("UTF-8", "Windows-1252", $arrayCampos5[4]);
				if(empty($todosdescricaoServicos["{$str}"])) {
					$todosdescricaoServicos["{$str}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$str}"])) {
					$todosdescricaoServicos["{$str}"] += $valor;
				}else {
					$todosdescricaoServicos["{$str}"] += $valor;
				}

				$tudo5 = 
				 ';'	// OPERADORA
				.$codigoCliente.';'		// NOME DA ORIGEM
				.$arrayCampos5[1].';'	// NUMERO TELEFONE
				.$arrayCampos5[1].';'	// RAMAL ASSOCIADO
				.$arrayCampos5[2].';'	// DATA LIGACAO
				.$arrayCampos5[3].';'	// HORA LIGACAO
				.$arrayCampos5[5].';'	// TELEFONE CHAMADO
				.$arrayCampos5[1].';'	// TRONCO
				// .$arrayCampos5[4].';'	// DESCRICAO
				.iconv("UTF-8", "Windows-1252", $arrayCampos5[4]).';'
				.$arrayCampos5[7].';'	// DURACAO
				.number_format($arrayCampos5[10], 2, ',', '.').';'	// TARIFA
				.';'	// DEPTO.
				.';'	// CONTA DE FATURA
				.';'	// MES_REF
				."\r\n";

				if (!fwrite($fp, $tudo5)) {
					// print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					return 'erro';
					exit;
				}
				$tudo5 = '';

			}

	//=======================================================================================================================================================================
		// ---------------------------------------------------------------
		// ---------- [ TIPO SERVIÇOS E MENSALIDADES E PLANOS ] ----------
		// ---------------------------------------------------------------
		$servicosCadastrados = array(
								"PagamentoemDuplicidade"					=> "Pagamento em duplicidade",
								"AssinaturaMensal"							=> "Assinatura Mensal",
		
								"FixoeMóvelIlimitadoNacionalEmpresas-FranquiaMensalGT11FSP"		=> "Fixo e Móvel Ilimitado Nacional Empresas - Franquia Mensal GT11 FSP",
								"FixoeMóvelIlimitadoNacionalEmpresas-FranquiaMensalGT"			=> "Fixo e Móvel Ilimitado Nacional Empresas - Franquia Mensal GT",
								"FixoeMóvelIlimitadoLocalEmpresas-FranquiaMensalGT1"			=> "Fixo e Móvel Ilimitado Local Empresas - Franquia Mensal GT1",
								"FixoeMóvelIlimitadoLocalEmpresas-FranquiaMensalGT11"			=> "Fixo e Móvel Ilimitado Local Empresas - Franquia Mensal GT11",
								"FixoeMóvelIlimitadoLocalEmpresas-FranquiaMensalGT11FSP"		=> "Fixo e Móvel Ilimitado Local Empresas - Franquia Mensal GT11 FSP",

								"FixoIlimitadoLocalEmpresas-FranquiaMensalGT11FSP" 				=> "Fixo Ilimitado Local Empresas - Franquia Mensal GT11 FSP",

								"AssinaturaMensalsemMinutos-EconomixFlex800" 					=> "Assinatura Mensal sem Minutos - Economix Flex 800",
								"AssinaturaMensalsemMinutos-EconomixFlex1500"					=> "Assinatura Mensal sem Minutos - Economix Flex 1500",
								"AssinaturaMensalsemMinutos-Max"								=> "Assinatura Mensal sem Minutos - Max",
								"AssinaturaMensalsemMinutos-PlanoMAISMINUTOS250"				=> "Assinatura Mensal sem Minutos - Plano MAIS MINUTOS 250",
								"AssinaturaMensalsemMinutos-PlanoAlternativo"					=> "Assinatura Mensal sem Minutos - Plano Alternativo",

								"ApontadorEssencial"						=> "Apontador Essencial",
								".\B(ApontadorBusiness)"					=> "Apontador Business",

								"AssinaturaMensalsemMinutos-Max"			=> "Assinatura Mensal sem Minutos - Max",

								"Max1000-FranquiaMensal"					=> "Max 1000 - Franquia Mensal",
								"Max5000-FranquiaMensal"					=> "Max 5000 - Franquia Mensal",

								"Acesso200MB"								=> "Acesso 200MB",
								"ServiçoInternetCorporativa200MB"			=> "Serviço Internet Corporativa 200MB",

								"8MegaEmpresasGT12UFSP"						=> "8 Mega  Empresas GT12 UFSP",
								"10MegaEmpresasGT11FSP"						=> "10 Mega Empresas GT11 FSP",
								"15MegaEmpresasGT11FSP"						=> "15 Mega Empresas GT11 FSP",
								"15MegaEmpresasGT12UFSP"					=> "15 Mega Empresas GT12 UFSP",
								"25MegaEmpresasGT11FSP"						=> "25 Mega Empresas GT11 FSP",
								"25MegaEmpresasGT12UFSP"					=> "25 Mega Empresas GT12 UFSP",
								
								"50MegaEmpresasGT11FSP"						=> "50 Mega Empresas GT11 FSP",
								
								"50MegaEmpresasGT12UFSP"					=> "50 Mega Empresas GT12 UFSP",
								"100MegaEmpresasGT11FSP"					=> "100 Mega Empresas GT11 FSP",
								"100MegaEmpresasGT12UFSP"					=> "100 Mega Empresas GT12 UFSP",

								"LinkdedadosTurbonetPower3Mega"				=> "Link de dados Turbonet Power 3 Mega",
								"LinkdedadosTurbonetPower5Mega"				=> "Link de dados Turbonet Power 5 Mega",
								"LinkdeDadosTurbonetPower5MbpsMaxGVT"		=> "Link de Dados Turbonet Power 5 Mbps Max GVT",
								"LinkdedadosTurbonetPower15MegaMais"		=> "Link de dados Turbonet Power 15 Mega Mais",
								"LinkdeDadosTurbonetPower10MbpsMaxGVT"  	=> "Link de Dados Turbonet Power 10 Mbps Max GVT",
								"LinkdedadosTurbonetPower35Mega"			=> "Link de dados Turbonet Power 35 Mega",
								"LinkdeDadosTurbonetPower35MbpsMaxGVT"  	=> "Link de Dados Turbonet Power 35 Mbps Max GVT",
								"LinkdedadosTurbonetMEGAMAXX1Mbps"      	=> "Link de dados Turbonet MEGA MAXX 1 Mbps",
								"LinkdedadosTurbonetMEGAMAXX5Mbps"			=> "Link de dados Turbonet MEGA MAXX 5 Mbps",

								"DisponibilizaçãodeEnd.IPFixoTurbonetPower"			=> "Disponibilização    de  End. IP  Fixo  Turbonet   Power",
								"DisponibilizaçãodeEnd.IPFixoT.MEGAMAXX1Mbps"		=> "Disponibilização    de  End. IP  Fixo  T. MEGA  MAXX  1 Mbps",

								"LocaçãodeInfra-estruturaTurbonetPower3Mega"		=> "Locação de Infra-estrutura Turbonet Power 3 Mega",
								"LocaçãodeInfra-estruturaTurbonetPower5Mega"		=> "Locação de Infra-estrutura Turbonet Power 5 Mega",
								"LocaçãodeInfra-estruturaTurbonetPower35Mega"		=> "Locação de Infra-estrutura Turbonet Power 35 Mega ", // esse
								"LocaçãodeInfra-estruturaTurbonetPower15MegaMais"	=> "Locação de Infra-estrutura Turbonet Power 15 Mega Mais",

								"LocaçãodeInfra-estrTurbonetPower5MbpsMaxGVT"		=> "Locação de Infra-estr Turbonet Power 5 Mbps Max GVT",
								"LocaçãodeInfra-estrTurbonetPower10MbpsMaxGVT"		=> "Locação de Infra-estr Turbonet Power 10 Mbps Max GVT",
								"LocaçãodeInfra-estrTurbonetPower35MbpsMaxGVT"		=> "Locação de Infra-estr Turbonet Power 35 Mbps Max GVT",

								"LocaçãodeInfra-estruturaTurbonetMEGAMAXX1Mbps"		=> "Locação de Infra-estrutura Turbonet MEGA MAXX 1 Mbps",
								"LocaçãodeInfra-estruturaTurbonetMEGAMAXX5Mbps"		=> "Locação de Infra-estrutura Turbonet MEGA MAXX 5 Mbps",

								"ProtectInvasãoPromoPlus"							=> "Protect Invasão Promo Plus",
								"ProtectTotal"                                  	=> "Protect Total",

								"PlanoEconomixFlex1500-Crédito1.500minutos-LocalFixo"	=> "Plano Economix Flex 1500 - Crédito 1.500 minutos - Local Fixo",

								"IlimitadoBrasilEmpresasEspecial-MensalidadePrincipalGT"			=> "Ilimitado Brasil Empresas Especial - Mensalidade Principal GT",
								"IlimitadoBrasilEmpresas-MensalidadePrincipalGT11FSP"              	=> "Ilimitado Brasil Empresas - Mensalidade Principal GT11 FSP",
								"IlimitadoBrasilEmpresas-MensalidadeAdicionalGT11FSP"              	=> "Ilimitado Brasil Empresas - Mensalidade Adicional GT11 FSP",
								"IlimitadoBrasilEmpresas-MensalidadePrincipalGT11"                 	=> "Ilimitado Brasil Empresas - Mensalidade Principal GT11",
								"IlimitadoBrasilEmpresasEspecial-MensalidadePrincipalGT11FSP"      	=> "Ilimitado Brasil Empresas Especial - Mensalidade Principal GT11 FSP",
								

								"VivoFixoIlimitadoEmpresasBRASIL"									=> "Vivo Fixo Ilimitado Empresas BRASIL",

								"VivoCloudBackupPremium"											=> "Vivo Cloud Backup Premium",
								"VivoCloudBackupBusiness"                                     		=> "Vivo Cloud Backup Business",
								"VivoFibra15MbpsAvulsoGT11FSP"										=> "Vivo Fibra 15 Mbps Avulso GT11 FSP",
								"VivoFibra25MbpsAvulsoGT11FSP"                       				=> "Vivo Fibra 25 Mbps Avulso GT11 FSP",
								"VivoInternet10MbpsAvulsoGT11FSP"                     				=> "Vivo Internet 10 Mbps Avulso GT11 FSP",

								"POPVivoProtege"													=> "POP Vivo Protege",
								"ProtegeEmpresas300GB"												=> "Protege Empresas 300GB",

								"PlanoEconomixFlex800-Crédito800minutos-LocalFixo"					=> "Plano Economix Flex 800 - Crédito 800 minutos - Local Fixo",
								"PlanoMAISMINUTOS250-Crédito250minutos-LocalFixo"					=> "Plano MAIS MINUTOS 250 - Crédito 250 minutos - Local Fixo",
								"PlanoGVT1.000-Credito1.000minutos-LocalFixo"						=> "Plano GVT 1.000 - Credito 1.000 minutos - Local Fixo",
								
								"PlanoBásico150minfixofixolocal"									=> "Plano Básico 150 min fixo fixo local",

								"TaxadeHabilitacao-Parcela"											=> "Taxa de Habilitacao - Parcela",
								"TaxadeHabilitação\b"												=> "Taxa de Habilitação",
								"TaxadeInstalaçãoInternet"											=> "Taxa de Instalação Internet",

								"LocaçãoPlataformaGer.deDados\/Internet-Estendido"					=> "Locação Plataforma Ger. de Dados/Internet - Estendido",

								"ServiçodeAutenticaçãoTurbonet"										=> "Serviço de Autenticação Turbonet",

								"ServiçoInternetPower10MegaGT1"										=> "Serviço Internet Power 10 Mega GT1",
								"ServicoPremiumTipo02200MB"											=> "Servico Premium Tipo 02 200MB",
								
								"SuspensãoTemporáriaVoz"											=> "Suspensão Temporária Voz",

								"ProtectVirus"														=> "Protect Virus",
								
								"Juros\b"						=> "Juros",
								"Multa\b"						=> "Multa"
								
								);

		$servicosEspacosRemovidos  = trim(str_replace($listaEspacos, "",utf8_encode(substr($arquivo[$conta],0,100))));
		$copiaServicosCadastrados = $servicosCadastrados;

		foreach($copiaServicosCadastrados as $novaDescServicos) {

			$formatoDesc = "(".key($copiaServicosCadastrados).")";
			if(preg_match($formatoDesc, $servicosEspacosRemovidos)) {

				$descricaoServicosContratados1 = $novaDescServicos;
				$encontrouServ1 = 1;
				$capturar06 = 1;

			}
			next($copiaServicosCadastrados);
		}

		if($capturar06 == 1) {

			if(strlen(trim($numero_origem)) > 4) {
				$arrayCampos6[1] = trim(str_replace("-","",$numero_origem));
				$arrayCampos6[5] = $arrayCampos6[1]; // NUMERO DESTINO
			}else {
				$arrayCampos6[1] = str_replace("-","",$codigoCliente);
				$arrayCampos6[5] = $arrayCampos6[1]; // NUMERO DESTINO
			}

			// $arrayCampos6[2] = ''; // DATA
			$arrayCampos6[2] = capturaData2('DD/MM/AAAA', substr($arquivo[$conta], 100, 20)); // DATA
			$arrayCampos6[3] = ''; // HORA
			$arrayCampos6[4] = $descricaoServicosContratados1;
			//$arrayCampos6[5] = ''; // NUMERO DESTINO
			$arrayCampos6[6] = ''; // OPERADORA
			$arrayCampos6[7] = ''; // MINUTOS
			$arrayCampos6[8] = ''; // QUANTIDADE
			$arrayCampos6[9] = ''; // KB
			$arrayCampos6[10] = capturaValor2 (substr($arquivo[$conta], -13));

			$valor = $arrayCampos6[10];
			$totalCapturado += $valor;

			$str = iconv("UTF-8", "Windows-1252",$arrayCampos6[4]);
			if(empty($todosdescricaoServicos["{$str}"])) {
				$todosdescricaoServicos["{$str}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$str}"])) {
				$todosdescricaoServicos["{$str}"] += $valor;
			}else {
				$todosdescricaoServicos["{$str}"] += $valor;
			}

			$tudo6 = 
				 ';'	// OPERADORA
				.$codigoCliente.';'		// NOME DA ORIGEM
				.$arrayCampos6[1].';'	// NUMERO TELEFONE
				.$arrayCampos6[1].';'	// RAMAL ASSOCIADO
				.$arrayCampos6[2].';'	// DATA LIGACAO
				.$arrayCampos6[3].';'	// HORA LIGACAO
				.$arrayCampos6[5].';'	// TELEFONE CHAMADO
				.$arrayCampos6[1].';'	// TRONCO
				// .$arrayCampos6[4].';'	// DESCRICAO
				.iconv("UTF-8", "Windows-1252",$arrayCampos6[4]).';'
				.$arrayCampos6[7].';'	// DURACAO
				.number_format($arrayCampos6[10], 2, ',', '.').';'	// TARIFA
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

	//=======================================================================================================================================================================
		// -----------------------------------
		// ---------- [ DESCONTOS ] ----------
		// -----------------------------------
		$encontrarItensDesconto =    array(
								"0027001032"							=> "0027001032",
								"0001001000"							=> "0001001000",
								"0030001036"							=> "0030001036",
								"0005001001"							=> "0005001001",
								"0035001053"							=> "0035001053",
								"0023001030"							=> "0023001030",
								"0021001023"							=> "0021001023",
								"0031001036"							=> "0031001036",

								"Jurosref.aomês"						=> "Juros ref. ao mês",
								"Multaref.aomês"						=> "Multa ref. ao mês",
								"JurosServiçosDigitaisTBRA"				=> "Juros Serviços Digitais TBRA",
								"DescontoVivoCloudBackup"				=> "Desconto Vivo Cloud Backup",

								"DescontoProm.Internet"					=> "Desconto Prom. Internet", // EQUIVALE AOS COMENTADOS A BAIXO.
								// "DescontoProm.InternetR$25.00"			=> "Desconto Prom. Internet R$ 25.00",
								// "DescontoProm.InternetR$29.90"			=> "Desconto Prom. Internet R$ 29,90",
								// "DescontoProm.InternetR$14.90"			=> "Desconto Prom. Internet R$ 14,90",
								// "DescontoProm.InternetR$10.00"			=> "Desconto Prom. Internet R$ 10.00",

								"DescontoProm.ApontadorBusiness"		=> "Desconto Prom. Apontador Business",
								// "DescontoProm.ApontadorBusinessR$12.09"	=> "Desconto Prom. Apontador Business R$ 12.09",
								// "DescontoProm.ApontadorBusinessR$7.00"	=> "Desconto Prom. Apontador Business R$ 7.00",
								
								"DescontoMensalidadePrincipal"			=> "Desconto Mensalidade Principal",
								// "DescontoMensalidadePrincipalR$5.00"	=> "Desconto Mensalidade Principal R$ 5.00",
								// "DescontoMensalidadePrincipalR$40.00"	=> "Desconto Mensalidade Principal R$ 40.00",
								// "DescontoMensalidadePrincipalR$17.90"	=> "Desconto Mensalidade Principal R$ 17,90",

								"DescontoMensalidadeAdicional"	=> "Desconto Mensalidade Adicional",
								// "DescontoMensalidadeAdicionalR$10.00"	=> "Desconto Mensalidade Adicional R$ 10.00",

								"DescontoMensalidadePrincipal"			=> "Desconto Mensalidade Principal",
								// "DescontoMensalidadeAdicional"			=> "Desconto Mensalidade Adicional",
								"Descontopromo.franquia"				=> "Desconto promo. franquia",

								"Descontopromo.taxadeinstalaçãodabandalarga"			=> "Desconto promo. taxa de instalação da banda larga",
								"Descontopromo.taxadehabilitaçãodalinha"				=> "Desconto promo. taxa de habilitação da linha",
								"Taxa de Instalação Internet"							=> "Taxa de Instalação Internet",

								"IsençãodeCob.porInterrupçãoPontualdoServiçoDados"		=> "Isenção de Cob. por Interrupção Pontual do Serviço Dados",
								"IsençãodeCob.porInterrupçãoPontualdoServiçoDado"		=> "Isenção de Cob. por Interrupção Pontual do Serviço Dado",
								"IsençãodeCob.porInterrupçãoPontualdoServiçoVoz"		=> "Isenção de Cob. por Interrupção Pontual do Serviço Voz",

								"MultaFidelizaçãoComboProdutoBandaLarga"				=> "Multa Fidelização Combo Produto Banda Larga",
								"MultaFidelizaçãoComboProdutoVoz"						=> "Multa Fidelização Combo Produto Voz",
								"MultaFidelizaçãoCombo"									=> "Multa Fidelização Combo",

								"Ressarcimentoporinterrupçãodoserviçodeinternet"		=> "Ressarcimento por interrupção do serviço de internet",
								"Ressarcimentoporinterrupçãodoserviçodetelefoniafixa"	=> "Ressarcimento por interrupção do serviço de telefonia fixa",
								"Ressarcimentoporinterrupçãodoserviçodetelefoniaf"		=> "Ressarcimento por interrupção do serviço de telefonia fixa",

								"Créditoreferenteafaturasanteriores\(1\)"					=> "Crédito referente a faturas anteriores",
								"Créditoreferenteafaturasanteriores"					=> "Crédito referente a faturas anteriores",
								"PagamentoemDuplicidade"								=> "Pagamento em Duplicidade",
								);

		$linhaEncontrarItensDesconto = '';
		$linhaEncontrarItensDesconto = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2],3,85)));
		$copia_encontrarItensDesconto = $encontrarItensDesconto;

		foreach($copia_encontrarItensDesconto as $novaEncontrarItensDesconto) {
			
			$formatoDesc = "\"(".key($copia_encontrarItensDesconto).")\"";
			if(preg_match($formatoDesc, $linhaEncontrarItensDesconto)) {

				$capturar07 = 1;
				$descDescontosCreditos = $novaEncontrarItensDesconto;
			}
			next($copia_encontrarItensDesconto);
		}
		
		// str_replace($encontrarItensDesconto,"",utf8_encode(substr($arquivo[$conta2],0,183)),$itensDescontoEncontrado);

		// if($itensDescontoEncontrado > 0) {
			// $capturar07 = 1;
		// }
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

			if(strlen($numero_origem) > 4) {
				$arrayCampos7[1] = trim(str_replace("-","",$numero_origem));
				$arrayCampos7[5] = $arrayCampos7[1]; // NUMERO DESTINO
			}else {
				$arrayCampos7[1] = str_replace("-","",$codigoCliente);
				$arrayCampos7[5] = $arrayCampos7[1]; // NUMERO DESTINO
			}

			//$arrayCampos7[1] = $numero_conta; // NUMERO

			// $arrayCampos7[2] = ''; // DATA
			$arrayCampos7[2] = capturaData2('DD/MM/AAAA',substr($arquivo[$conta], 95, 20)); // DATA
			$arrayCampos7[3] = ''; // HORA
			// $arrayCampos7[4] = trim(substr($arquivo[$conta],2,85)); //DESCRIÇÂO
			$arrayCampos7[4] = $descDescontosCreditos; // DESCRIÇÂO
			// $arrayCampos7[5] = ''; // NUMERO DESTINO
			$arrayCampos7[6] = ''; // OPERADORA
			$arrayCampos7[7] = ''; // MINUTOS
			$arrayCampos7[8] = ''; // QUANTIDADE
			$arrayCampos7[9] = ''; // MEGA
			$arrayCampos7[10] = capturaValor2 (substr($arquivo[$conta],-13));

			$valor = $arrayCampos7[10];
			$totalCapturado += $valor;

			$str = iconv("UTF-8", "Windows-1252",$arrayCampos7[4]);
			if(empty($todosdescricaoServicos["{$str}"])) {
				$todosdescricaoServicos["{$str}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$str}"])) {
				$todosdescricaoServicos["{$str}"] += $valor;
			}else {
				$todosdescricaoServicos["{$str}"] += $valor;
			}

			// TRATAMENTO DE CODIFICAÇÃO NA DESCRIÇÃO
			// $codificacao = mb_detect_encoding($descDescontosCreditos, 'UFT-8', true);
			// if($codificacao) {
				// $arrayCampos7[4] = iconv("UTF-8", "Windows-1252", $arrayCampos7[4]);
			// }else {
				// $arrayCampos7[4] = utf8_encode($arrayCampos7[4]);
			// }

			$tudo7 = 
				 ';'	// OPERADORA
				.$codigoCliente.';'		// NOME DA ORIGEM
				.$arrayCampos7[1].';'	// NUMERO TELEFONE
				.$arrayCampos7[1].';'	// RAMAL ASSOCIADO
				.$arrayCampos7[2].';'	// DATA LIGACAO
				.$arrayCampos7[3].';'	// HORA LIGACAO
				.$arrayCampos7[5].';'	// TELEFONE CHAMADO
				.$arrayCampos7[1].';'	// TRONCO
				.$arrayCampos7[4].';'	// DESCRICAO
				// .iconv("UTF-8", "Windows-1252",$arrayCampos7[4]).';'
				.$arrayCampos7[7].';'	// DURACAO
				.number_format($arrayCampos7[10], 2, ',', '.').';'	// TARIFA
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

	//=======================================================================================================================================================================
	// ===========================================================================================================
	// ========= [ SISTEMA DE COMPARAÇÃO DE SERVIÇOS QUE ESTÃO NO RESUMIDO E NÃO ESTÃO NO DETALHAMENTO ] =========
	// ===========================================================================================================

		if($for1 == $quantidadeTotalLinhas-2) {

			foreach($descricaoServicosCabecalho as $desc2) {

				$pega = 0;
				
				// echo '<br/> ==> '.key($descricaoServicosCabecalho);
				
				$copiatodosdescricaoServicos = $todosdescricaoServicos;

				foreach($copiatodosdescricaoServicos as $desc1) {
					
					// echo '<br/>'.key($descricaoServicosCabecalho).' - '.utf8_encode(key($copiatodosdescricaoServicos));

					if(key($descricaoServicosCabecalho) == utf8_encode(key($copiatodosdescricaoServicos))) {
						$pega = 1;
					}
					next($copiatodosdescricaoServicos);
				}

				if($pega == 0) {



					$arrayCampos13[10] = capturaValor2(substr($desc2, -13)); // VALOR

					$valor = $arrayCampos13[10];
					$totalCapturado += $valor;

					// $str = iconv("UTF-8", "Windows-1252", key($descricaoServicosCabecalho));
					// if(empty($todosdescricaoServicos["{$str}"])) {
						// $todosdescricaoServicos["{$str}"] = 0;
					// }

					// if(empty($todosdescricaoServicos["{$str}"])) {
						// $todosdescricaoServicos["{$str}"] += $valor;
					// }else {
						// $todosdescricaoServicos["{$str}"] += $valor;
					// }

					$dD = 
						 ';'	// OPERADORA
						.';'		// NOME DA ORIGEM
						.';'	// NUMERO TELEFONE
						.';'	// RAMAL ASSOCIADO
						.';'	// DATA LIGACAO
						.';'	// HORA LIGACAO
						.';'	// TELEFONE CHAMADO
						.';'	// TRONCO
						.iconv("UTF-8", "Windows-1252", key($descricaoServicosCabecalho)).';' // DESCRICAO
						.';'	// DURACAO
						.number_format($arrayCampos13[10], 2, ',', '.').';'	// TARIFA
						.';'	// DEPTO.
						.';'	// CONTA DE FATURA
						.';'	// MES_REF
						."\r\n";
				
					if (!fwrite($fp, $dD)) {
						// print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
						return 'erro';
						exit;
					}

				}
				next($descricaoServicosCabecalho);
			}
		}
		
		if($for1 == $quantidadeTotalLinhas-2) {

			$copia_descricaoServicosCabecalho = $descricaoServicosCabecalho;

			foreach($copia_descricaoServicosCabecalho as $descDSC) {

				$pega2 = 0;
				$copia_descricaoConsumoOutrasOperadoras = $descricaoConsumoOutrasOperadoras;

				foreach($copia_descricaoConsumoOutrasOperadoras as $descCOO) {
					
					// echo '<br/> => '.key($copia_descricaoConsumoOutrasOperadoras).' - '.key($copia_descricaoServicosCabecalho);

					if(key($copia_descricaoConsumoOutrasOperadoras) == key($copia_descricaoServicosCabecalho)) {
						$pega2 = 1;
					}
					next($copia_descricaoConsumoOutrasOperadoras);
				}

				if($pega2 == 1) {

					$arrayCampos14[0] = capturaValor2(substr($descDSC, -13)); // VALOR

					$valor = $arrayCampos14[0];
					$totalCapturado += $valor;

					// $str = iconv("UTF-8", "Windows-1252", key($copia_descricaoServicosCabecalho));
					// if(empty($todosdescricaoServicos["{$str}"])) {
						// $todosdescricaoServicos["{$str}"] = 0;
					// }

					// if(empty($todosdescricaoServicos["{$str}"])) {
						// $todosdescricaoServicos["{$str}"] += $valor;
					// }else {
						// $todosdescricaoServicos["{$str}"] += $valor;
					// }

					$registroOutrasOperadoras = 
						 ';'	// OPERADORA
						.';'		// NOME DA ORIGEM
						.';'	// NUMERO TELEFONE
						.';'	// RAMAL ASSOCIADO
						.';'	// DATA LIGACAO
						.';'	// HORA LIGACAO
						.';'	// TELEFONE CHAMADO
						.';'	// TRONCO
						.iconv("UTF-8", "Windows-1252", key($copia_descricaoServicosCabecalho)).';' // DESCRICAO
						.';'	// DURACAO
						.number_format($arrayCampos14[0], 2, ',', '.').';'	// TARIFA
						.';'	// DEPTO.
						.';'	// CONTA DE FATURA
						.';'	// MES_REF
						."\r\n";

					if (!fwrite($fp, $registroOutrasOperadoras)) {
						print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
						exit;
					}
				}
				next($copia_descricaoServicosCabecalho);
			}
		}
	


		} // FIM O FLAG INICIO DE CAPTURA GERAL.

	}//FECHA WHILE

	// Fecha o arquivo
	fclose($fp);
	// echo '</p>TERMINOU';
	// exit();
	return array($totalCapturado, $totalFatura);

} // FIM FUNCTION

?>

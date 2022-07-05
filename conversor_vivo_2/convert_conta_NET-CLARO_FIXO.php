<?php

include_once("funcoesParaFaturas2.php");

set_time_limit(1440);

function converteFaturaNetClaroFixo(string $arquivoConvertidoTXT) {

	//ABRE O ARQUIVO TXT
	$arquivo = file($arquivoConvertidoTXT);
	$fp = fopen($arquivoConvertidoTXT.".csv", "w+");

	if($arquivo == false) die('O arquivo não existe.');
	if($fp == false) die('O arquivo não foi criado.');

	$conta = 0;
	$conta2 = 0;

	$totalCapturado = 0;
	$numero_origem = '';
	$numero_fatura = '';

	$capturar01 = 0;
	$capturar02 = 0;
	$capturar03 = 0;
	$capturar04 = 0;
	$capturar05 = 0;
	$capturar06 = 0;
	$capturar07 = 0;
	$capturar10 = 0;
	$capturar11 = 0;
	$capturarBLOCO2 = 0;
	$capturaDescServ3 = 0;
	$paraCapturaCabecalho = 0;
	$capturarCabBLOCO1 = 0;

	GLOBAL $codigoCliente;

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

	$contadorLigacoes = 0;
	$alertaDescricaoNaocadastrado = 0;
	$todosdescricaoServicos = array();
	$pegaTudo = 0;
	$dataVencimento = '';
	$meioDia = 12;

	$listaEspacos = array(" ","  ","   ","    ","     ","      ","       ","        ","        ","          ");

	// FORMATO PARA LIGAÇÕES
	$formatoDataLigacao = '([0-9]{2}\/[0-9]{2}\/[0-9]{4})';
	$formatoHoraLigacao = '([0-9]{2}h[0-9]{2}m[0-9]{2}s)';
	$formatoDuracaoLigacao = '([0-9]{1}h[0-9]{2}m[0-9]{2}[s])';
	
	$listaDescLigacoes = array(
									"LIGACOESDDDPARATELEFONESFIXOS"				=> "LIGACOES DDD PARA TELEFONES FIXOS",
									"LIGACOESLOCAISPARACELULARES"				=> "LIGACOES LOCAIS PARA CELULARES",
									"LIGACOESDDDPARACELULARES"					=> "LIGACOES DDD PARA CELULARES",
									"LIGACOESLOCPCELULARESCLARO"				=> "LIGACOES LOCAIS PARA CELULARES CLARO",
									"LIGACOESDDDPCELULARESCLARO"				=> "LIGACOES DDD PARA CELULARES CLARO",
									"LIGACOESLOCAISPARANETFONEECLAROFONE"		=> "LIGACOES LOCAIS PARA NET FONE E CLARO FONE",
									"LIGACOESDDDPARANETFONEECLAROFONE"			=> "LIGACOES DDD PARA NET FONE E CLARO FONE",
									"LIGACOESLOCAISPARATELEFONESFIXOS"			=> "LIGACOES LOCAIS PARA TELEFONES FIXOS",
									"DDDPARATELEFONEFIXO"						=> "DDD PARA TELEFONE FIXO",
									"DDDPARATELEFONEMOVEL"						=> "DDD PARA TELEFONE MOVEL",
									"LIGACOESLOCAISPARATELEFONESFIXOS-DURACAOEVALORDASLIGACOESREALIZADASPARAOMESMONUMEROESTAOSOMADOS"	=> "LIGACOES LOCAIS PARA TELEFONES FIXOS"
									);
	$listaDescServ3 = array(
								"ENCARGOSFINANC.CONTASATRASO"								=> "ENCARGOS FINANC. CONTAS ATRASO",
								"MENSALIDADEPACOTENOW"										=> "MENSALIDADE PACOTE NOW",
								"MENSSERVICOSWEBSITEAVULSO"									=> "MENS SERVICOS WEB SITE AVULSO",
								"MENSPROPORCIONALVIRTUABLEMPRESAS30MC/PWFID"				=> "MENS PROPORCIONAL VIRTUA BL EMPRESAS 30 MC/PWFID",
								"MENSPROPORCIONALVIRTUABDALARGANETEMPRESAS30MEGAFID"		=> "MENS PROPORCIONAL VIRTUA BDA LARGA NET EMPRESAS 30 MEGA FID",
								"MENSPROPORCIONALVIRTUABDALARGANETEMPRESAS120MEGAFD"		=> "MENS PROPORCIONAL VIRTUA BDA LARGA NET EMPRESAS 120 MEGA FD",
								"MENSPROPORCIONALVIRTUANETEMPRESASBL40MEGAFID"				=> "MENS PROPORCIONAL VIRTUA NET EMPRESAS BL 40 MEGA FID",
								"MENSPROPORCIONALTVPRINCIPALSELEÇÃONETEMPRESASCOMPACT"		=> "MENS PROPORCIONAL TV PRINCIPAL SELEÇÃO NET EMPRESAS COMPACT",
								"MENSPROPORCIONALTVPRINCIPALSELEÇÃOCONEXAODIGPMEFIDELI"		=> "MENS PROPORCIONAL TV PRINCIPAL SELEÇÃO CONEXAO DIGPME FIDELI",
								"OFERTACONJUNTANETEMPRESASBL40MEGAFID+APLICATIVOS"			=> "OFERTA CONJUNTA NET EMPRESAS BL 40 MEGA FID + APLICATIVOS",
								"MENSALIDADEVIRTUANETEMPRESASBL40MEGAFID"					=> "MENSALIDADE VIRTUA NET EMPRESAS BL 40 MEGA FID",
								"MENSPROPORCIONALVIRTUANETEMPRESASBL140MEGAFID"				=> "MENS PROPORCIONAL VIRTUA NET EMPRESAS BL 140 MEGA FID",
								"MEGA NET VIRTUA"											=> "MEGA NET VIRTUA",
								"REVISTAMONET"												=> "REVISTA MONET",
								"REVISTA"													=> "REVISTA"
								);
	
	$listaDescJurosMulta = array("JUROSPGTO EM ATRASO"										=> "JUROSPGTO EM ATRASO",
										"MULTA"														=> "MULTA",
										"DESCOFERTACONJUNTANETEMPRESASBL140MEGAFID+APLICATIVOS"		=> "DESC OFERTA CONJUNTA NET EMPRESAS BL 140 MEGA FID + APLICATIVOS",
										"DESCONTOINTERRUPÇÃODESINALVIRTUAEM" 						=> "DESCONTO INTERRUPÇÃO DE SINAL VIRTUA EM",
										"TAXAASSISTTECNICAVIRTUA" 									=> "TAXA ASSIST TECNICA VIRTUA",
										"DESCONTOCOMERCIALVIRTUA" 									=> "DESCONTO COMERCIAL VIRTUA",
										"DESCONTOMENSALIDADEVIRTUA" 								=> "DESCONTO MENSALIDADE VIRTUA"
									);
	$listaServ1 = array(
								"OFERTACONJUNTANETEMPRESASBL140MEGAFID+"					=> "OFERTA CONJUNTA NET EMPRESAS BL 140 MEGA FID + APLICATIVOS",
								"OFERTACONJUNTANETEMPRESASBL140MEGAFID+APLICATIVOS"			=> "OFERTA CONJUNTA NET EMPRESAS BL 140 MEGA FID + APLICATIVOS",
								"OFERTACONJUNTABLEMPRESAS30MC/PWFID+APLICATIVOS"			=> "OFERTA CONJUNTA BL EMPRESAS 30 MC/PWFID + APLICATIVOS",
								"OFERTACONJUNTABLNETEMPRESAS60MEGA+APLICATIVOS"				=> "OFERTA CONJUNTA BL NET EMPRESAS 60 MEGA + APLICATIVOS",
								"OFERTACONJUNTABDALARGANETEMPRESAS60MEGAFID+APLICATIV"		=> "OFERTA CONJUNTA BDA LARGA NET EMPRESAS 60 MEGA FID + APLICATIVOS",
								"OFERTACONJUNTAVIRTUA35MINDIVIDUALFIDELIDADE+APLICATIVOS"	=> "OFERTA CONJUNTA VIRTUA 35 M INDIVIDUAL FIDELIDADE + APLICATIVOS",
								"OFERTACONJUNTABANDALARGANETEMPRESAS120MEGA+APLICATIV"		=> "OFERTA CONJUNTA BANDA LARGA NET EMPRESAS 120 MEGA + APLICATIVOS",
								"OFERTACONJUNTABDALARGANETEMPRESAS30MEGAFID+APLICATIV"		=> "OFERTA CONJUNTA BDA LARGA NET EMPRESAS 30 MEGA FID + APLICATIVOS",
								"OFERTACONJUNTABDALARGANETEMPRESAS240MEGAFD+APLICATI"		=> "OFERTA CONJUNTA BDA LARGA NET EMPRESAS 240 MEGA FD + APLICATIVOS",
								"OFERTACONJUNTAVIRTUA240MCOMFONEFIDELIDADE+APLICATIVOS"		=> "OFERTA CONJUNTA VIRTUA 240 MCOM FONE FIDELIDADE + APLICATIVOS",
								"OFERTACONJUNTAVIRTUA240MCOMTVOUFONEFID+APLICATIVOS"		=> "OFERTA CONJUNTA VIRTUA 240 MCOM TV OU FONE FID + APLICATIVOS",
								"OFERTACONJUNTABLNETEMPRESAS240MFIDELIDADE+APLICATIVOS"		=> "OFERTA CONJUNTA BL NET EMPRESAS 240 M FIDELIDADE + APLICATIVOS",
								"OFERTACONJUNTACBONETEMPRESASBANDALARGA120M+APLICATI"		=> "OFERTA CONJUNTA CBO NET EMPRESAS BANDA LARGA 120 M + APLICATIVOS",

								"OFERTACONJUNTAVIRTUA120MCOMFONEFIDELIDADE+"				=> "OFERTA CONJUNTA VIRTUA 120M COM FONE FIDELIDADE + APLICATIVOS",
								"OFERTACONJUNTABLEMPRESAS60MPMECOMFONE+"					=> "OFERTA CONJUNTA BL EMPRESAS 60 MPME COM FONE +",
								"OFERTACONJUNTABLEMPRESAS60MPMECOMFONEFID+"					=> "OFERTA CONJUNTA BL EMPRESAS 60 MPME COM FONE FID +",
								"OFERTACONJUNTABDALARGANETEMPRESAS120MEGAFD+"				=> "OFERTA CONJUNTA BDA LARGA NETEMPRESAS 120 MEGA FD +",
								"OFERTACONJUNTABDALARGANETEMPRESAS120MEGAFD+APLICATI"		=> "OFERTA CONJUNTA BDA LARGA NET EMPRESAS 120 MEGA FD + APLICATIVOS",

								"OFERTACONJUNTABLNETEMPRESAS120MEGA+APLICATIVOS"			=> "OFERTA CONJUNTA BL NET EMPRESAS 120 MEGA + APLICATIVOS",
								"OFERTACONJUNTAVTA120MEGACOMTVOUFONEFID+APLICATIVOS"		=> "OFERTA CONJUNTA VTA 120 MEGA COM TV OU FONE FID + APLICATIVOS",
								"OFERTACONJUNTABLEMPRESAS60MPMECOMFONE+APLICATIVOS"			=> "OFERTA CONJUNTA BL EMPRESAS 60 MPME COM FONE + APLICATIVOS",
								"OFERTACONJUNTABLEMPRESAS60MPMECOMFONEFID+APLICATIVO"		=> "OFERTA CONJUNTA BL EMPRESAS 60 MPME COM FONE FID + APLICATIVOS",
								"OFERTACONJUNTAPROPORCIONALNETEMPRESASBL140"				=> "OFERTA CONJUNTA PROPORCIONAL NET EMPRESAS BL 140 MEGA FID + APLICATIVOS",
								"OFERTACONJUNTAPROPORCIONALNETEMPRESASBL40MEGAFID+A"		=> "OFERTA CONJUNTA PROPORCIONAL NET EMPRESAS BL 40 MEGA FID + APLICATIVOS",
								"OFERTACONJUNTABLNETEMPRESAS60MEGA+"						=> "OFERTA CONJUNTA BL NET EMPRESAS 60 MEGA +",
								"OFERTACONJUNTAVIRTUA120MCOMFONEFIDELIDADE+APLICATIVOS"		=> "OFERTA CONJUNTA VIRTUA 120 M COM FONE FIDELIDADE + APLICATIVOS",
								"OFERTACONJUNTAPROPORCIONALVIRTUA120MCOMFONEFIDELIDADE"		=> "OFERTA CONJUNTA PROPORCIONAL VIRTUA 120 M COM FONE FIDELIDADE",
								"OFERTACONJUNTABLNETEMPRESAS120MEGA"						=> "OFERTA CONJUNTA BL NET EMPRESAS 120 MEGA",

								"MENSALIDADEVIRTUABLNETEMPRESAS60MEGA"						=> "MENSALIDADE VIRTUABL NET EMPRESAS 60 MEGA",
								"MENSALIDADEVIRTUAVIRTUA240MCOMTVOUFONEFID"					=> "MENSALIDADE VIRTUA VIRTUA 240M COM TV OU FONE FID",
								"MENSALIDADEVIRTUAVTA120MEGACOMTVOUFONEFID"					=> "MENSALIDADE VIRTUA VTA 120 MEGA COM TV OU FONE FID",
								"MENSALIDADEVIRTUABLNETEMPRESAS120MEGA"						=> "MENSALIDADE VIRTUA BL NET EMPRESAS 120 MEGA",
								"MENSALIDADEVIRTUABANDALARGANETEMPRESAS120MEGA"				=> "MENSALIDADE VIRTUA BANDA LARGA NET EMPRESAS 120 MEGA",
								"MENSALIDADEVIRTUABDALARGANETEMPRESAS120MEGAFD"				=> "MENSALIDADE VIRTUA BDA LARGA NET EMPRESAS 120 MEGA FD",
								"MENSALIDADEVIRTUABDALARGANETEMPRESAS240MEGAFD"				=> "MENSALIDADE VIRTUA BDA LARGA NET EMPRESAS 240 MEGA FD",
								"MENSALIDADETVPRINCIPALSELEÇÃONETEMPRESASCOMPACTOCFFD"		=> "MENSALIDADE TV PRINCIPAL SELEÇÃO NET EMPRESAS COMPACTO CFFD",
								"MENSALIDADEVIRTUACBONETEMPRESASBANDALARGA120M"				=> "MENSALIDADE VIRTUA CBO NET EMPRESAS BANDA LARGA 120 M",
								"MENSALIDADETVPRINCIPALSELEÇÃOCBONETEMPRESASBASICOPME"		=> "MENSALIDADE TV PRINCIPAL SELEÇÃO CBO NET EMPRESAS BASICO PME",
								"MENSALIDADEVIRTUAVIRTUA240MCOMFONEFIDELIDADE"				=> "MENSALIDADE VIRTUA VIRTUA 240 M COM FONE FIDELIDADE",
								"MENSALIDADEVIRTUABDALARGANETEMPRESAS30MEGAFID"				=> "MENSALIDADE VIRTUA BDA LARGA NET EMPRESAS 30 MEGA FID",
								"MENSALIDADEVIRTUAVIRTUA35MINDIVIDUALFIDELIDADE"			=> "MENSALIDADE VIRTUA VIRTUA 35 MINDIVIDUAL FIDELIDADE",
								"MENSALIDADEVIRTUABLPME120MFIDELIDADE"						=> "MENSALIDADE VIRTUA BLPME 120 M FIDELIDADE",
								"MENSALIDADETVPRINCIPALSELEÇÃOBASICOHDPMEFIDELIDADE"		=> "MENSALIDADE TV PRINCIPAL SELEÇÃO BASICO HDPME FIDELIDADE",
								"MENSALIDADETVPRINCIPALSELEÇÃOCONEXAODIGPMEFIDELIDADE"		=> "MENSALIDADE TV PRINCIPAL SELEÇÃO CONEXAO DIG PME FIDELIDADE",
								);

	$quantidadeTotalLinhas = count($arquivo);
	for ($for1 = 0; $for1 < $quantidadeTotalLinhas; $for1++) {

		$conta2 = $conta;

		if(utf8_encode(substr($arquivo[$conta2], 0, 7)) == "Cliente") {
			$paraCapturaCabecalho = 1;
		}

		str_replace(array("Vencimento"), "", str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 200))), $iniciaCapturaDataAchou);
		if($iniciaCapturaDataAchou > 0 AND $for1 < 20) {
			$dataVencimento = capturaData2("DD/MM/AAAA", $arquivo[$conta2+1]);
		}

		str_replace(array("IdentificaçãoparaDébito"), "", str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 200))), $iniciaCapturaServicosAchou);
		if($iniciaCapturaServicosAchou > 0) {
			$pegaTudo = 1;
		}

		if(utf8_encode(substr($arquivo[$conta], 0, 9)) == 'Telefone:') {

			$numero_origem = trim(str_replace("-", "", utf8_encode(substr($arquivo[$conta2], 9, 15))));
			$numero_origem = capturaNumeroTel('00000000', $numero_origem);	// CAPTURA O NÚMERO DA LINHA QUE UTILIZOU O SERVIÇO.

		}

		$itensCapturaFatura = 0;
		$capturaCodigoFatura =    array("digo  NET","digo    "
											);

		str_replace($capturaCodigoFatura,"",utf8_encode(substr($arquivo[$conta2],0,180)),$itensCapturaFatura);

		if($itensCapturaFatura > 0) {
			$numero_fatura = trim(str_replace(array("-", "/"), "", utf8_encode(substr($arquivo[$conta2+1], 55, 22))));
			$codigoCliente = trim(str_replace(array("-", "/"), "", utf8_encode(substr($arquivo[$conta2+1], 55, 22))));
		}

		// NOME DA ORIGEM
		if(utf8_encode(trim(substr($arquivo[$conta], 0, 18))) == 'Código  do cliente' AND $passaUm == true) {
			$codigoCliente = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 20, 25)));
			$passaUm = false;
		}

// -------------------------------------------------------------------------------
// ------------------------------[ TIPO LIGAÇÕES 1 ]------------------------------
// -------------------------------------------------------------------------------

		$formatoLigacao = "((^[^SubTotal])([0-9]+)([\W\w]+){$formatoDuracaoLigacao}([\W\w]+)(([+-]?)[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}))";
		if(preg_match($formatoLigacao,$arquivo[$for1])) {

			$capturar01 = 1;

			if($contadorLigacoes == 0) {

				$itensDescricaoEncontrado = 0;
				// $listaDescLigacoes = array(
									// "LIGACOESDDDPARATELEFONESFIXOS"				=> "LIGACOES DDD PARA TELEFONES FIXOS",
									// "LIGACOESLOCAISPARACELULARES"				=> "LIGACOES LOCAIS PARA CELULARES",
									// "LIGACOESDDDPARACELULARES"					=> "LIGACOES DDD PARA CELULARES",
									// "LIGACOESLOCPCELULARESCLARO"				=> "LIGACOES LOCAIS PARA CELULARES CLARO",
									// "LIGACOESDDDPCELULARESCLARO"				=> "LIGACOES DDD PARA CELULARES CLARO",
									// "LIGACOESLOCAISPARANETFONEECLAROFONE"		=> "LIGACOES LOCAIS PARA NET FONE E CLARO FONE",
									// "LIGACOESDDDPARANETFONEECLAROFONE"			=> "LIGACOES DDD PARA NET FONE E CLARO FONE",
									// "LIGACOESLOCAISPARATELEFONESFIXOS"			=> "LIGACOES LOCAIS PARA TELEFONES FIXOS",
									// "DDDPARATELEFONEFIXO"						=> "DDD PARA TELEFONE FIXO",
									// "DDDPARATELEFONEMOVEL"						=> "DDD PARA TELEFONE MOVEL",
									// "LIGACOESLOCAISPARATELEFONESFIXOS-DURACAOEVALORDASLIGACOESREALIZADASPARAOMESMONUMEROESTAOSOMADOS"	=> "LIGACOES LOCAIS PARA TELEFONES FIXOS"
									// );

				$linhaEspacosRemovidos = '';
				$linhaEspacosRemovidos = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2-1], 0, 183))));
				$copiaDescLigacoes = $listaDescLigacoes;
				foreach($copiaDescLigacoes as $novaDescLigacoes) {

					if(key($copiaDescLigacoes) == $linhaEspacosRemovidos) {
						$descricaoLigacao = $novaDescLigacoes;
						$encontrouLig = 1;
					}
					next($copiaDescLigacoes);
				}

				if($encontrouLig == 0) {

					$desc = substr($arquivo[$conta2-1], 0, 47).' - ['.$for1.']';
					$todosdescricoesNaoCadastrados["{$desc}"] = 1;
					$alertaDescricaoNaocadastrado = 1;

				}
			}
			$contadorLigacoes += 1;
		}else {
			$contadorLigacoes = 0;
		}

		if($capturar01 == 1) {

			$arrayCampos[1] = $numero_origem; // NUMERO
			$arrayCampos[2] = capturaData2("DD/MM/AAAA", $arquivo[$conta]);
			$arrayCampos[3] = capturaDuracao2("00h00m00s", $arquivo[$conta]);

			if($arrayCampos[2] == '' AND $arrayCampos[3] == '') { // SE DATA E HORA SÃO AUSENTES A FORMA DE CAPTURA MUDA.

				$arrayCampos[2] = $dataVencimento; // DATA
				$novaHora = capturaDuracao2("0h00m00s", $arquivo[$conta]);
				$arrayCampos[3] = str_pad($meioDia , 2 , '0' , STR_PAD_LEFT).':'.substr($novaHora, 2, 2).':'.substr($novaHora, 5, 2); // HORA

				$meioDia += substr($novaHora, 2, 2);
				if($meioDia <= 23) {
					$meioDia += substr($novaHora, 2, 2);
				}else {
					$meioDia = 8;
				}

				$arrayCampos[4] = trim($descricaoLigacao); //DESCRIÇÂO
				$arrayCampos[5] = capturaNumeroTel("00000000", substr($arquivo[$conta], 0, 17)); // NUMERO DESTINO
				$arrayCampos[6] = ''; // OPERADORA
				$arrayCampos[7] = decimo('0h00m00s', $arquivo[$conta]);

			}else {

				$arrayCampos[2] = capturaData2("DD/MM/AAAA", $arquivo[$conta]);
				$arrayCampos[3] = capturaDuracao2("00h00m00s", $arquivo[$conta]);
				$arrayCampos[4] = trim($descricaoLigacao); //DESCRIÇÂO
				$arrayCampos[5] = capturaNumeroTel("00000000", substr($arquivo[$conta], 15, 17)); // NUMERO DESTINO
				$arrayCampos[6] = ''; // OPERADORA
				$arrayCampos[7] = decimo('0h00m00s', substr($arquivo[$conta], 100, 20));

			}

			$arrayCampos[8] = ''; // QUANTIDADE
			$arrayCampos[9] = ''; // MEGA

			$arrayCampos[10] = capturaValor2(substr($arquivo[$for1], -13)); // VALOR

			$valor = str_replace(array(".", ","), array("", "."), $arrayCampos[10]);
			$totalCapturado += $valor;

			if(empty($todosdescricaoServicos["{$arrayCampos[4]}"])) {
				$str = trim($arrayCampos[4]);
				$todosdescricaoServicos["{$str}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$arrayCampos[4]}"])) {
				$str = trim($arrayCampos[4]);
				$todosdescricaoServicos["{$str}"] += (float)$valor;
			}else {
				$str = trim($arrayCampos[4]);
				$todosdescricaoServicos["{$str}"] += (float)$valor;
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
			$arrayCampos[2] = '';
			$tudo = '';
		} // FINAL TIPO1
		$capturar01 = 0;

// --------------------------------------------------------------------------------------------
// ------------------------------[ TIPO SERVIÇOS E MENSALIDADES CABECALHO ]------------------------------
// --------------------------------------------------------------------------------------------

		// TERÁ QUE VERIFICAR OS SERVIÇOS NO COMEÇO E COMPARAR COM OS ABAIXO.
		// $listaDescServ3 = array(
								// "ENCARGOSFINANC.CONTASATRASO"								=> "ENCARGOS FINANC. CONTAS ATRASO",
								// "MENSALIDADEPACOTENOW"										=> "MENSALIDADE PACOTE NOW",
								// "MENSSERVICOSWEBSITEAVULSO"									=> "MENS SERVICOS WEB SITE AVULSO",
								// "MENSPROPORCIONALVIRTUABLEMPRESAS30MC/PWFID"				=> "MENS PROPORCIONAL VIRTUA BL EMPRESAS 30 MC/PWFID",
								// "MENSPROPORCIONALVIRTUABDALARGANETEMPRESAS30MEGAFID"		=> "MENS PROPORCIONAL VIRTUA BDA LARGA NET EMPRESAS 30 MEGA FID",
								// "MENSPROPORCIONALVIRTUABDALARGANETEMPRESAS120MEGAFD"		=> "MENS PROPORCIONAL VIRTUA BDA LARGA NET EMPRESAS 120 MEGA FD",
								// "MENSPROPORCIONALVIRTUANETEMPRESASBL40MEGAFID"				=> "MENS PROPORCIONAL VIRTUA NET EMPRESAS BL 40 MEGA FID",
								// "MENSPROPORCIONALTVPRINCIPALSELEÇÃONETEMPRESASCOMPACT"		=> "MENS PROPORCIONAL TV PRINCIPAL SELEÇÃO NET EMPRESAS COMPACT",
								// "MENSPROPORCIONALTVPRINCIPALSELEÇÃOCONEXAODIGPMEFIDELI"		=> "MENS PROPORCIONAL TV PRINCIPAL SELEÇÃO CONEXAO DIGPME FIDELI",
								// "OFERTACONJUNTANETEMPRESASBL40MEGAFID+APLICATIVOS"			=> "OFERTA CONJUNTA NET EMPRESAS BL 40 MEGA FID + APLICATIVOS",
								// "MENSALIDADEVIRTUANETEMPRESASBL40MEGAFID"					=> "MENSALIDADE VIRTUA NET EMPRESAS BL 40 MEGA FID",
								// "MENSPROPORCIONALVIRTUANETEMPRESASBL140MEGAFID"				=> "MENS PROPORCIONAL VIRTUA NET EMPRESAS BL 140 MEGA FID",
								// "MEGA NET VIRTUA"											=> "MEGA NET VIRTUA",
								// "REVISTAMONET"												=> "REVISTA MONET",
								// "REVISTA"													=> "REVISTA"
								// );

		if($paraCapturaCabecalho == 1) {

			$flagDescServ3 = "((^[]|^[\s]*)(Discriminação)[\s]+(do)[\s]+(Serviço))";
			if(preg_match($flagDescServ3, utf8_encode($arquivo[$for1]))) {
				$capturaDescServ3 = 1;
			}
			if($pegaTudo == 0 OR $capturaDescServ3 == 1) {
				$cesta1 = $listaDescServ3;
				$linhaDescServ3 = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 80)));

				foreach($cesta1 as $novaDescServicos) {

					str_replace(key($cesta1), "", $linhaDescServ3, $encontradoServicosMensalidades);

					if($encontradoServicosMensalidades > 0) {
						$descServ2 = $novaDescServicos;
						$capturar06 = 1;
						//$encontrouServ1 = 1;
					}
					next($cesta1);
				}
			}

			if($capturar06 == 1) {

				if(trim($numero_origem) == '') {
					$arrayCampos6[1] = $numero_fatura;
				}else {
					$arrayCampos6[1] = $numero_origem; // NUMERO
				}

				$arrayCampos6[2] = ''; // DATA
				$arrayCampos6[3] = ''; // HORA
				$arrayCampos6[4] = $descServ2; // DESCRIÇÂO
				$arrayCampos6[5] = ''; // NUMERO DESTINO
				$arrayCampos6[6] = ''; // OPERADORA
				$arrayCampos6[7] = ''; // MINUTOS
				$arrayCampos6[8] = ''; // QUANTIDADE
				$arrayCampos6[9] = ''; // KB
				$arrayCampos6[10] = capturaValor2(substr($arquivo[$conta], 0, 180));

				$valor = str_replace(array(".", ","), array("", "."), $arrayCampos6[10]);
				$totalCapturado += $valor;

				if(empty($todosdescricaoServicos["{$arrayCampos6[4]}"])) {
					$str = trim($arrayCampos6[4]);
					$todosdescricaoServicos["{$str}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$arrayCampos6[4]}"])) {
					$str = trim($arrayCampos6[4]);
					$todosdescricaoServicos["{$str}"] += (float)$valor;
				}else {
					$str = trim($arrayCampos6[4]);
					$todosdescricaoServicos["{$str}"] += (float)$valor;
				}

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
		
// ----------[ CABEÇALHO  DA CONTA ]------------
// -------------------------------------------------------------------------------------------------------------------------
// ----------[ DESCONTOS, JUROS, MULTA, TAXA, ACRÉSCIMO  - ATENÇÃO: ESSA CAPTURA É PARA APENAS DA SEGUNDA COLUNA ]----------
// -------------------------------------------------------------------------------------------------------------------------

		if($paraCapturaCabecalho == 0) {
			
			// $listaDescJurosMulta = array("JUROSPGTO EM ATRASO"										=> "JUROSPGTO EM ATRASO",
										// "MULTA"														=> "MULTA",
										// "DESCOFERTACONJUNTANETEMPRESASBL140MEGAFID+APLICATIVOS"		=> "DESC OFERTA CONJUNTA NET EMPRESAS BL 140 MEGA FID + APLICATIVOS",
										// "DESCONTOINTERRUPÇÃODESINALVIRTUAEM" 						=> "DESCONTO INTERRUPÇÃO DE SINAL VIRTUA EM",
										// "TAXAASSISTTECNICAVIRTUA" 									=> "TAXA ASSIST TECNICA VIRTUA",
										// "DESCONTOCOMERCIALVIRTUA" 									=> "DESCONTO COMERCIAL VIRTUA",
										// "DESCONTOMENSALIDADEVIRTUA" 								=> "DESCONTO MENSALIDADE VIRTUA"
									// );

			$linhaDescJurosMulta = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2], -100)));
			$copiaDescJurosMulta = $listaDescJurosMulta;

			foreach($copiaDescJurosMulta as $itemDJM) {
				str_replace(key($copiaDescJurosMulta), "", $linhaDescJurosMulta, $encDescJurosMulta);
				if($encDescJurosMulta > 0) {
					$capturarBLOCO2 = 1;
					$descricaoServicos1 = $itemDJM;
				}
				next($copiaDescJurosMulta);
			}

			if($capturarBLOCO2 == 1) {

				if(trim($numero_origem) == '') {
					$arrayCamposBLOCO2[1] = $numero_fatura;
				}else {
					$arrayCamposBLOCO2[1] = $numero_origem; // NUMERO
				}

				$arrayCamposBLOCO2[2] = ''; // DATA
				$arrayCamposBLOCO2[3] = ''; // HORA
				$arrayCamposBLOCO2[4] = $descricaoServicos1; // trim(substr($arquivo[$conta],0,70)); //DESCRIÇÂO
				$arrayCamposBLOCO2[5] = ''; // NUMERO DESTINO
				$arrayCamposBLOCO2[6] = ''; // OPERADORA
				$arrayCamposBLOCO2[7] = ''; // MINUTOS
				$arrayCamposBLOCO2[8] = ''; // QUANTIDADE
				$arrayCamposBLOCO2[9] = ''; // KB
				$arrayCamposBLOCO2[10] = capturaValor2(substr($arquivo[$conta], -13)); // COLETA DE VALOR

				$valor = str_replace(array(".", ","), array("", "."), $arrayCamposBLOCO2[10]);
				$totalCapturado += $valor;

				if(empty($todosdescricaoServicos["{$arrayCamposBLOCO2[4]}"])) {
					$str = trim($arrayCamposBLOCO2[4]);
					$todosdescricaoServicos["{$str}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$arrayCamposBLOCO2[4]}"])) {
					$str = trim($arrayCamposBLOCO2[4]);
					$todosdescricaoServicos["{$str}"] += (float)$valor;
				}else {
					$str = trim($arrayCamposBLOCO2[4]);
					$todosdescricaoServicos["{$str}"] += (float)$valor;
				}

				$tudoBLOCO2 = 
					 ';'	// OPERADORA
					.$codigoCliente.';'	// NOME DA ORIGEM
					.$arrayCamposBLOCO2[1].';'	// NUMERO TELEFONE
					.$arrayCamposBLOCO2[1].';'	// RAMAL ASSOCIADO
					.$arrayCamposBLOCO2[2].';'	// DATA LIGACAO
					.$arrayCamposBLOCO2[3].';'	// HORA LIGACAO
					.$arrayCamposBLOCO2[5].';'	// TELEFONE CHAMADO
					.$arrayCamposBLOCO2[1].';'	// TRONCO
					.$arrayCamposBLOCO2[4].';'	// DESCRICAO
					.$arrayCamposBLOCO2[7].';'	// DURACAO
					.$arrayCamposBLOCO2[10].';'	// TARIFA
					.';'	// DEPTO.
					.';'	// CONTA DE FATURA
					.';'	// MES_REF
					."\r\n";

				if (!fwrite($fp, $tudoBLOCO2)) {
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}
				$tudoBLOCO2 = '';
			}
			$capturarBLOCO2 = 0;  // ENCERRA APÓS CAPTURA.
		}

// ----------[ CABEÇALHO  DA CONTA ]------------
// ---------------------------------------------------------------------------------------
// ----------[ OFERTA, ?????  - ATENÇÃO: ESSA CAPTURA É PARA A PRIMEIRA COLUNA ]----------
// ---------------------------------------------------------------------------------------

		if($paraCapturaCabecalho == 0) {
			
			// $listaServ1 = array(
								// "OFERTACONJUNTANETEMPRESASBL140MEGAFID+"					=> "OFERTA CONJUNTA NET EMPRESAS BL 140 MEGA FID + APLICATIVOS",
								// "OFERTACONJUNTANETEMPRESASBL140MEGAFID+APLICATIVOS"			=> "OFERTA CONJUNTA NET EMPRESAS BL 140 MEGA FID + APLICATIVOS",
								// "OFERTACONJUNTABLEMPRESAS30MC/PWFID+APLICATIVOS"			=> "OFERTA CONJUNTA BL EMPRESAS 30 MC/PWFID + APLICATIVOS",
								// "OFERTACONJUNTABLNETEMPRESAS60MEGA+APLICATIVOS"				=> "OFERTA CONJUNTA BL NET EMPRESAS 60 MEGA + APLICATIVOS",
								// "OFERTACONJUNTABDALARGANETEMPRESAS60MEGAFID+APLICATIV"		=> "OFERTA CONJUNTA BDA LARGA NET EMPRESAS 60 MEGA FID + APLICATIVOS",
								// "OFERTACONJUNTAVIRTUA35MINDIVIDUALFIDELIDADE+APLICATIVOS"	=> "OFERTA CONJUNTA VIRTUA 35 M INDIVIDUAL FIDELIDADE + APLICATIVOS",
								// "OFERTACONJUNTABANDALARGANETEMPRESAS120MEGA+APLICATIV"		=> "OFERTA CONJUNTA BANDA LARGA NET EMPRESAS 120 MEGA + APLICATIVOS",
								// "OFERTACONJUNTABDALARGANETEMPRESAS30MEGAFID+APLICATIV"		=> "OFERTA CONJUNTA BDA LARGA NET EMPRESAS 30 MEGA FID + APLICATIVOS",
								// "OFERTACONJUNTABDALARGANETEMPRESAS240MEGAFD+APLICATI"		=> "OFERTA CONJUNTA BDA LARGA NET EMPRESAS 240 MEGA FD + APLICATIVOS",
								// "OFERTACONJUNTAVIRTUA240MCOMFONEFIDELIDADE+APLICATIVOS"		=> "OFERTA CONJUNTA VIRTUA 240 MCOM FONE FIDELIDADE + APLICATIVOS",
								// "OFERTACONJUNTAVIRTUA240MCOMTVOUFONEFID+APLICATIVOS"		=> "OFERTA CONJUNTA VIRTUA 240 MCOM TV OU FONE FID + APLICATIVOS",
								// "OFERTACONJUNTABLNETEMPRESAS240MFIDELIDADE+APLICATIVOS"		=> "OFERTA CONJUNTA BL NET EMPRESAS 240 M FIDELIDADE + APLICATIVOS",
								// "OFERTACONJUNTACBONETEMPRESASBANDALARGA120M+APLICATI"		=> "OFERTA CONJUNTA CBO NET EMPRESAS BANDA LARGA 120 M + APLICATIVOS",

								// "OFERTACONJUNTAVIRTUA120MCOMFONEFIDELIDADE+"				=> "OFERTA CONJUNTA VIRTUA 120M COM FONE FIDELIDADE + APLICATIVOS",
								// "OFERTACONJUNTABLEMPRESAS60MPMECOMFONE+"					=> "OFERTA CONJUNTA BL EMPRESAS 60 MPME COM FONE +",
								// "OFERTACONJUNTABLEMPRESAS60MPMECOMFONEFID+"					=> "OFERTA CONJUNTA BL EMPRESAS 60 MPME COM FONE FID +",
								// "OFERTACONJUNTABDALARGANETEMPRESAS120MEGAFD+"				=> "OFERTA CONJUNTA BDA LARGA NETEMPRESAS 120 MEGA FD +",
								// "OFERTACONJUNTABDALARGANETEMPRESAS120MEGAFD+APLICATI"		=> "OFERTA CONJUNTA BDA LARGA NET EMPRESAS 120 MEGA FD + APLICATIVOS",

								// "OFERTACONJUNTABLNETEMPRESAS120MEGA+APLICATIVOS"			=> "OFERTA CONJUNTA BL NET EMPRESAS 120 MEGA + APLICATIVOS",
								// "OFERTACONJUNTAVTA120MEGACOMTVOUFONEFID+APLICATIVOS"		=> "OFERTA CONJUNTA VTA 120 MEGA COM TV OU FONE FID + APLICATIVOS",
								// "OFERTACONJUNTABLEMPRESAS60MPMECOMFONE+APLICATIVOS"			=> "OFERTA CONJUNTA BL EMPRESAS 60 MPME COM FONE + APLICATIVOS",
								// "OFERTACONJUNTABLEMPRESAS60MPMECOMFONEFID+APLICATIVO"		=> "OFERTA CONJUNTA BL EMPRESAS 60 MPME COM FONE FID + APLICATIVOS",
								// "OFERTACONJUNTAPROPORCIONALNETEMPRESASBL140"				=> "OFERTA CONJUNTA PROPORCIONAL NET EMPRESAS BL 140 MEGA FID + APLICATIVOS",
								// "OFERTACONJUNTAPROPORCIONALNETEMPRESASBL40MEGAFID+A"		=> "OFERTA CONJUNTA PROPORCIONAL NET EMPRESAS BL 40 MEGA FID + APLICATIVOS",
								// "OFERTACONJUNTABLNETEMPRESAS60MEGA+"						=> "OFERTA CONJUNTA BL NET EMPRESAS 60 MEGA +",
								// "OFERTACONJUNTAVIRTUA120MCOMFONEFIDELIDADE+APLICATIVOS"		=> "OFERTA CONJUNTA VIRTUA 120 M COM FONE FIDELIDADE + APLICATIVOS",
								// "OFERTACONJUNTAPROPORCIONALVIRTUA120MCOMFONEFIDELIDADE"		=> "OFERTA CONJUNTA PROPORCIONAL VIRTUA 120 M COM FONE FIDELIDADE",
								// "OFERTACONJUNTABLNETEMPRESAS120MEGA"						=> "OFERTA CONJUNTA BL NET EMPRESAS 120 MEGA",

								// "MENSALIDADEVIRTUABLNETEMPRESAS60MEGA"						=> "MENSALIDADE VIRTUABL NET EMPRESAS 60 MEGA",
								// "MENSALIDADEVIRTUAVIRTUA240MCOMTVOUFONEFID"					=> "MENSALIDADE VIRTUA VIRTUA 240M COM TV OU FONE FID",
								// "MENSALIDADEVIRTUAVTA120MEGACOMTVOUFONEFID"					=> "MENSALIDADE VIRTUA VTA 120 MEGA COM TV OU FONE FID",
								// "MENSALIDADEVIRTUABLNETEMPRESAS120MEGA"						=> "MENSALIDADE VIRTUA BL NET EMPRESAS 120 MEGA",
								// "MENSALIDADEVIRTUABANDALARGANETEMPRESAS120MEGA"				=> "MENSALIDADE VIRTUA BANDA LARGA NET EMPRESAS 120 MEGA",
								// "MENSALIDADEVIRTUABDALARGANETEMPRESAS120MEGAFD"				=> "MENSALIDADE VIRTUA BDA LARGA NET EMPRESAS 120 MEGA FD",
								// "MENSALIDADEVIRTUABDALARGANETEMPRESAS240MEGAFD"				=> "MENSALIDADE VIRTUA BDA LARGA NET EMPRESAS 240 MEGA FD",
								// "MENSALIDADETVPRINCIPALSELEÇÃONETEMPRESASCOMPACTOCFFD"		=> "MENSALIDADE TV PRINCIPAL SELEÇÃO NET EMPRESAS COMPACTO CFFD",
								// "MENSALIDADEVIRTUACBONETEMPRESASBANDALARGA120M"				=> "MENSALIDADE VIRTUA CBO NET EMPRESAS BANDA LARGA 120 M",
								// "MENSALIDADETVPRINCIPALSELEÇÃOCBONETEMPRESASBASICOPME"		=> "MENSALIDADE TV PRINCIPAL SELEÇÃO CBO NET EMPRESAS BASICO PME",
								// "MENSALIDADEVIRTUAVIRTUA240MCOMFONEFIDELIDADE"				=> "MENSALIDADE VIRTUA VIRTUA 240 M COM FONE FIDELIDADE",
								// "MENSALIDADEVIRTUABDALARGANETEMPRESAS30MEGAFID"				=> "MENSALIDADE VIRTUA BDA LARGA NET EMPRESAS 30 MEGA FID",
								// "MENSALIDADEVIRTUAVIRTUA35MINDIVIDUALFIDELIDADE"			=> "MENSALIDADE VIRTUA VIRTUA 35 MINDIVIDUAL FIDELIDADE",
								// "MENSALIDADEVIRTUABLPME120MFIDELIDADE"						=> "MENSALIDADE VIRTUA BLPME 120 M FIDELIDADE",
								// "MENSALIDADETVPRINCIPALSELEÇÃOBASICOHDPMEFIDELIDADE"		=> "MENSALIDADE TV PRINCIPAL SELEÇÃO BASICO HDPME FIDELIDADE",
								// "MENSALIDADETVPRINCIPALSELEÇÃOCONEXAODIGPMEFIDELIDADE"		=> "MENSALIDADE TV PRINCIPAL SELEÇÃO CONEXAO DIG PME FIDELIDADE",
								// );

			$linhaCabServ1 = str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta2], 0, 100)));
			$copiaCabServ1 = $listaServ1;

			foreach($copiaCabServ1 as $itemCS1) {

				str_replace(key($copiaCabServ1), "", $linhaCabServ1, $encCabServ1);

				if($encCabServ1 > 0) {
					$capturarCabBLOCO1 = 1;
					$descCabServ1 = $itemCS1;
				}
				next($copiaCabServ1);
			}

			if($capturarCabBLOCO1 == 1) {

				if(trim($numero_origem) == '') {
					$arrayCamposBLOCO2[1] = $numero_fatura;
				}else {
					$arrayCamposBLOCO2[1] = $numero_origem; // NUMERO
				}

				$arrayCamposBLOCO2[2] = ''; // DATA
				$arrayCamposBLOCO2[3] = ''; // HORA
				$arrayCamposBLOCO2[4] = $descCabServ1; // DESCRIÇÂO
				$arrayCamposBLOCO2[5] = ''; // NUMERO DESTINO
				$arrayCamposBLOCO2[6] = ''; // OPERADORA
				$arrayCamposBLOCO2[7] = ''; // MINUTOS
				$arrayCamposBLOCO2[8] = ''; // QUANTIDADE
				$arrayCamposBLOCO2[9] = ''; // KB
				$arrayCamposBLOCO2[10] = capturaValor2(substr($arquivo[$conta], 60, 40)); // COLETA DE VALOR

				$valor = str_replace(array(".", ","), array("", "."), $arrayCamposBLOCO2[10]);
				$totalCapturado += $valor;

				if(empty($todosdescricaoServicos["{$arrayCamposBLOCO2[4]}"])) {
					$str = trim($arrayCamposBLOCO2[4]);
					$todosdescricaoServicos["{$str}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$arrayCamposBLOCO2[4]}"])) {
					$str = trim($arrayCamposBLOCO2[4]);
					$todosdescricaoServicos["{$str}"] += (float)$valor;
				}else {
					$str = trim($arrayCamposBLOCO2[4]);
					$todosdescricaoServicos["{$str}"] += (float)$valor;
				}

				$tudoBLOCO2 = 
					 ';'	// OPERADORA
					.$codigoCliente.';'	// NOME DA ORIGEM
					.$arrayCamposBLOCO2[1].';'	// NUMERO TELEFONE
					.$arrayCamposBLOCO2[1].';'	// RAMAL ASSOCIADO
					.$arrayCamposBLOCO2[2].';'	// DATA LIGACAO
					.$arrayCamposBLOCO2[3].';'	// HORA LIGACAO
					.$arrayCamposBLOCO2[5].';'	// TELEFONE CHAMADO
					.$arrayCamposBLOCO2[1].';'	// TRONCO
					.$arrayCamposBLOCO2[4].';'	// DESCRICAO
					.$arrayCamposBLOCO2[7].';'	// DURACAO
					.$arrayCamposBLOCO2[10].';'	// TARIFA
					.';'	// DEPTO.
					.';'	// CONTA DE FATURA
					.';'	// MES_REF
					."\r\n";

				if (!fwrite($fp, $tudoBLOCO2)) {
					print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
					exit;
				}
				$tudoBLOCO2 = '';
			}
			$capturarCabBLOCO1 = 0;  // ENCERRA APÓS CAPTURA.
		}

// -----------------------------------------------------------------------------------------------------
// --------------------[ //TIPO SERVIÇOS E MENSALIDADES - OUTRAS FORMAS DE CAPTURA ]--------------------
// -----------------------------------------------------------------------------------------------------

		$listaDescServ1 = array(
								"FONEEMPILIMBRASILTOTAL1L",
								"FONEEMPILIMBRASILTOTAL",
								"NETFONEILIMITADOLOCALPROMOCIONAL",
								"NETFONEILIMITADOBRASIL21PROMOCIONAL",
								"FONEEMPRILIMITADOBRASIL1L",
								"FONEEMPRESAILIMBR1L",
								"FONEEMPRILIMITADOLOCAL1L",
								"NETFONEEMPRESARIALILIMITADO1LCOMPORTABILIDADE",
								"NETFONEEMPRESARIALILIMITADO1LSEMPORTABILIDADE",
								"FRANQUIANAOUTILIZADA",
								"PORATRASODEPAGAMENTOEMFATURA"
								);
		str_replace($listaDescServ1, "", str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 183))), $itensDescontoEncontrado);

		if($itensDescontoEncontrado > 0 AND $pegaTudo == 1) {

			$listaDescServ2 =    array(
									"FONEEMPILIMBRASILTOTAL1L"							=> "FONE EMP ILIM BRASIL TOTAL 1L",
									"FONEEMPILIMBRASILTOTAL" 							=> "FONE EMP ILIM BRASIL TOTAL 1L",
									"NETFONEILIMITADOLOCALPROMOCIONAL"					=> "NETFONE ILIMITADO LOCAL PROMOCIONAL",
									"NETFONEILIMITADOBRASIL21PROMOCIONAL"				=> "NETFONE ILIMITADO BRASIL 21 PROMOCIONAL",
									"FONEEMPRILIMITADOBRASIL1L"							=> "FONE EMPR ILIMITADO BRASIL 1L",
									"FONEEMPRESAILIMBR1L"								=> "FONE EMPRESA ILIMBR 1L",
									"FONEEMPRILIMITADOLOCAL1L"							=> "FONE EMPR ILIMITADO LOCAL 1L",
									"NETFONEEMPRESARIALILIMITADO1LCOMPORTABILIDADE"		=> "NETFONE EMPRESARIAL ILIMITADO 1L COM PORTABILIDADE",
									"NETFONEEMPRESARIALILIMITADO1LSEMPORTABILIDADE"		=> "NETFONE EMPRESARIAL ILIMITADO 1L SEM PORTABILIDADE",
									"PORATRASODEPAGAMENTOEMFATURA"						=> "POR ATRASO DE PAGAMENTO EM FATURA",
									"FRANQUIANAOUTILIZADA"								=> "FRANQUIA NAO UTILIZADA",
									);

			$servicosEspacosRemovidos = trim(str_replace($listaEspacos, "", utf8_encode(substr($arquivo[$conta], 0, 50))));
			$cesta = $listaDescServ2;
			foreach($cesta as $novaDescServicos) {

				if(key($cesta) == $servicosEspacosRemovidos) {

					$capturar07 = 1;
					$descServ1 = $novaDescServicos;
					$encontrouServ1 = 1;

				}
				next($cesta);
			}
		}
		if($capturar07 == 1) {
			
			if(capturaData2('DD/MM/AAAA', $arquivo[$conta2+1]) <> NULL) {

				if(trim($numero_origem) == '') {
					$arrayCampos7[1] = $numero_fatura;
				}else {
					$arrayCampos7[1] = $numero_origem;
				}

				$arrayCampos7[2] = capturaData2('DD/MM/AAAA', $arquivo[$conta2+1]); // DATA
				$arrayCampos7[3] = ''; // HORA
				$arrayCampos7[4] = $descServ1; //DESCRIÇÂO
				$arrayCampos7[5] = ''; // NUMERO DESTINO
				$arrayCampos7[6] = ''; // OPERADORA
				$arrayCampos7[7] = ''; // MINUTOS
				$arrayCampos7[8] = ''; // QUANTIDADE
				$arrayCampos7[9] = ''; // MEGA
				$arrayCampos7[10] = capturaValor2($arquivo[$conta2+1]); // COLETA DE VALOR

				$valor = str_replace(array(".", ","), array("", "."), $arrayCampos7[10]);
				$totalCapturado += $valor;

				if(empty($todosdescricaoServicos["{$arrayCampos7[4]}"])) {
					$str = trim($arrayCampos7[4]);
					$todosdescricaoServicos["{$str}"] = 0;
				}

				if(empty($todosdescricaoServicos["{$arrayCampos7[4]}"])) {
					$str = trim($arrayCampos7[4]);
					$todosdescricaoServicos["{$str}"] += (float)$valor;
				}else {
					$str = trim($arrayCampos7[4]);
					$todosdescricaoServicos["{$str}"] += (float)$valor;
				}

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

				//************************* [ CAPTURA DE VALORES DOS PLANOS SEM DESCRIÇÃO ] ****************************
				if(utf8_encode(substr($arquivo[$conta2+4], 0, 9)) == 'Telefone:') {
					$arrayCampos7[2] = ''; // DATA
					$arrayCampos7[3] = ''; // HORA
					$arrayCampos7[4] = $descServ1; //DESCRIÇÂO
					$arrayCampos7[5] = ''; // NUMERO DESTINO
					$arrayCampos7[6] = ''; // OPERADORA
					$arrayCampos7[7] = ''; // MINUTOS
					$arrayCampos7[8] = ''; // QUANTIDADE
					$arrayCampos7[9] = ''; // MEGA
					$arrayCampos7[10] = capturaValor2($arquivo[$conta2+5]); // COLETA DE VALOR

					$valor = str_replace(array(".", ","), array("", "."), $arrayCampos7[10]);
					$totalCapturado += $valor;

					if(empty($todosdescricaoServicos["{$arrayCampos7[4]}"])) {
						$str = trim($arrayCampos7[4]);
						$todosdescricaoServicos["{$str}"] = 0;
					}

					if(empty($todosdescricaoServicos["{$arrayCampos7[4]}"])) {
						$str = trim($arrayCampos7[4]);
						$todosdescricaoServicos["{$str}"] += (float)$valor;
					}else {
						$str = trim($arrayCampos7[4]);
						$todosdescricaoServicos["{$str}"] += (float)$valor;
					}

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
				}
				//***************************************************************************************************
			}
		}
		$capturar07 = 0;  // ENCERRA APÓS CAPTURA.

// -------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------[ CAPTURA PARA NA LINHA 156 PRA FRENTE ]------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------

		$capturar09 = 0;

		$itensCancelamentoEncontrado = 0;
		$encontrarItensCancelamento =    array(
								"VAGO"
								//"MENS PROPORCIONALVIRTUA NET EMPRESAS BL 140MEGA FID",
								//"DESCONTOMENSALIDADE VIRTUA"
								);
		str_replace($encontrarItensCancelamento, "", utf8_encode(substr($arquivo[$conta], 0, 183)), $itensCancelamentoEncontrado);
		if($itensCancelamentoEncontrado > 0) {
			$capturar09 = 1;
		}

		// -----------------------------------------------------

		if($capturar09 == 9) {

			if(trim($numero_origem) == '') {
				$arrayCampos8[1] = $numero_fatura;
			}else {
				$arrayCampos8[1] = $numero_origem;
			}

			$arrayCampos8[2] = ''; // DATA
			$arrayCampos8[3] = ''; // HORA
			$arrayCampos8[4] = trim(substr($linha, 0, 75)); //DESCRIÇÂO
			$arrayCampos8[5] = ''; // NUMERO DESTINO
			$arrayCampos8[6] = ''; // OPERADORA
			$arrayCampos8[7] = ''; // MINUTOS
			$arrayCampos8[8] = ''; // QUANTIDADE
			$arrayCampos8[9] = ''; // MEGA
			$arrayCampos8[10] = trim(substr($linha, 155, 13)); // VALOR

			$valor = str_replace(array(".", ","), array("", "."), $arrayCampos8[10]);
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
			$capturar09 = 0;
		}

// -------------------------------------------------------------------------------------------
// -------------------------------------[ PARCELAMENTOS ]-------------------------------------
// -------------------------------------------------------------------------------------------

		$itensParcelamentoEncontrado = 0;
		$encontrarItensParcelamento =    array(
								"PARCELAMENTOS",
								"xxxxxxxx"
								);
		if($for1 > 3) {
			str_replace($encontrarItensParcelamento, "", substr($arquivo[$conta2-2], 0, 183), $itensParcelamentoEncontrado);
		}

		if($itensParcelamentoEncontrado > 0) {
			$capturar10 = 1;
		}

		// ----------[ ENCERRA CAPTURA DOS DESCONTOS ]----------
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

		if($capturar10 == 9) {

			$arrayCampos9[1] = $numero_conta; // NUMERO
			$arrayCampos9[2] = ''; // DATA
			$arrayCampos9[3] = ''; // HORA
			$arrayCampos9[4] = trim(substr($arquivo[$conta], 0, 70)); //DESCRIÇÂO
			$arrayCampos9[5] = ''; // NUMERO DESTINO
			$arrayCampos9[6] = ''; // OPERADORA
			$arrayCampos9[7] = ''; // MINUTOS
			$arrayCampos9[8] = ''; // QUANTIDADE
			$arrayCampos9[9] = ''; // MEGA
			$arrayCampos9[10] = trim(substr($arquivo[$conta], 145, 13)); // VALOR

			$valor = str_replace(array(".", ","), array("", "."), $arrayCampos9[10]);
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

// -------------------------------------------------------------------------------------------------
// -----------------------------[ OUTROS DESCONTOS - RESSARCIMENTO ]--------------------------------
// -------------------------------------------------------------------------------------------------

		$encerraOutrosDescontos = 0;
		$encontrarOutrosDescontos =    array(
								"Ressarcimento     por  interrupção  do   serviço  de  telefonia fixa",
								"Ressarcimento por interrupção  do serviçode  telefoniafixa",
								"Ressarcimento por interrupção  do serviçode  internet",
								"Isenção   de  Cob.  por  Interrupção   Pontual   do Serviço   Dados",
								"Ressarcimento     por  interrupção  do   serviço  de  internet"
								);
		str_replace($encontrarOutrosDescontos, "", utf8_encode(substr($arquivo[$conta2], 0, 183)), $encerraOutrosDescontos);
		if($encerraOutrosDescontos > 0) {
			$capturar11 = 1;
		}
		// ----------------------------------------------------

		if($capturar11 == 9) {

			if(trim($numero_origem) == '') {
				$arrayCampos10[1] = $numero_fatura;
			}else {
				$arrayCampos10[1] = $numero_origem;
			}

			$arrayCampos10[2] = ''; // DATA
			$arrayCampos10[3] = ''; // HORA
			$arrayCampos10[4] = trim(substr($arquivo[$conta], 0, 75)); //DESCRIÇÂO
			$arrayCampos10[5] = ''; // NUMERO DESTINO
			$arrayCampos10[6] = ''; // OPERADORA
			$arrayCampos10[7] = ''; // MINUTOS
			$arrayCampos10[8] = ''; // QUANTIDADE
			$arrayCampos10[9] = ''; // MEGA
			$arrayCampos10[10] = trim(substr($arquivo[$conta], 85, 20)); // VALOR

			$valor = str_replace(array(".", ","), array("", "."), $arrayCampos10[10]);
			$totalCapturado += $valor;

			$tudo10 = 
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

			if (!fwrite($fp, $tudo10)) {
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
			$capturar11 = 0;
		}

// ---------------------------------------[ CAPTURA TOTAL DA FATURA ]------------------------------------------
		if(substr($arquivo[$for1], 0, 11) == 'Valor total' or substr($arquivo[$for1], 0, 12) == 'Valor  Total') {

			$totalFatura = str_replace(array(".", ","), array("", "."), capturaValor2(substr($arquivo[$conta2+1], -13)));  // VALOR

		}
// ------------------------------------------------------------------------------------------------------------	

		$conta += 1;

	}//FECHA WHILE
	
	// Fecha o arquivo
	fclose($fp);

	return array($totalCapturado, $totalFatura);

} // FIM FUNÇÃO

?>

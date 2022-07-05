<?php

include_once("connect.php");
include_once("detectaOperadora.php");
include_once("convert_conta_VIVO_FIXO.php");
include_once("convert_conta_NET-CLARO_FIXO.php");
include_once("convert_conta_ALGAR.php");
include_once("convert_conta_NEXTEL_2019.php");
include_once("convert_conta_VIVO_FIXO-PONTO_DE_COMUNICACAO.php");
include_once("convert_conta_VIVO_MOVEL.php");
include_once("conversorPdfTxt.php");

$sql = ("SELECT * FROM `revendas` WHERE id = '".$_SESSION['idRevenda']."' LIMIT 1");
$query = $_SG['link']->query($sql);
$resultado = $query->fetchAll(PDO::FETCH_ASSOC);

foreach($resultado as $item){

	$storage = 'storage/';
	$pastaRevenda = $item['pastaRevenda'].'/';

}



// -------------------------------------------------------
// $date = new DateTime();
// $pastaData = $date->format('dmY');
// $pasta_revenda = 'convertidos/'.$pastaRevenda.'/';
// $pasta_cliente_revenda = 'convertidos/'.$pastaRevenda.'/'.$pastaData.'/';

// if(is_dir($pastaRevenda)){
	
	// if(is_dir($pasta_cliente_revenda)){
		
	// }else {
		// echo 'vai ser criada';
		// mkdir($pasta_cliente_revenda);
	// }
// }else {
	// mkdir($pastaRevenda);
	// if(is_dir($pasta_cliente_revenda)){
	
	// }else {
		// mkdir($pasta_cliente_revenda);
	// }
// }
// -------------------------------------------------------

$i=0;

if(!empty($_POST)) {
	
	if(isset($_POST['tipo_conta'])) {
		$tipoDeConta = $_POST['tipo_conta'];
		// echo '<br/> TIPO CONTA.: '.$tipoDeConta;
		// exit();
	}
}else {
	header("Location: principal.php");
	// return NULL;
}

if(!empty($_POST)) {
	if(isset($_POST['senhaPdf'])) {
		$senhaPdf = $_POST['senhaPdf'];
	}
	
}else {
	$senhaPdf = '000';
}

foreach($_FILES["arquivos"]["error"] as $key => $error) {

	// O CORRETO É TER A PASTA EXCLUSIVA PARA CADA CLIENTE.
	$destino = $storage.$pastaRevenda.$_FILES["arquivos"]["name"][$i];
	$arquivoPDF = $_FILES["arquivos"]["name"][$i];

	move_uploaded_file( $_FILES["arquivos"]["tmp_name"][$i], $destino );

	$retornoConversao = conversorPDFTXT($destino, $senhaPdf); // RETORNA 0 = SUCESSO, 1 = ERRO ou 2 = FALTA DE SENHA.

	// if($retornoConversao == 0) {
		// echo '<br/>0 => '.$retornoConversao;
	// }elseif($retornoConversao == 1) {
		// echo '<br/>1 => '.$retornoConversao;
	// }else {
		// echo '<br/>2 => '.$retornoConversao;
		
	// }
	echo '<br/>ARQUIVO.: '.$destino.' - ERRO.: '.$retornoConversao;
	
	$i++;
	
	if($retornoConversao == 0) { // 0 - SE A CONVERSÃO FOI REALIZADA COM SUCESSO O ARQUIVO TXT SERÁ PROCESSADO.

		$arquivoTXT = $destino.'.txt';

		$operadora = detectaOperadora($arquivoTXT); // DETECTA A OPERADORA E REDIRECIONA PARA O CAPTURADOR CORRETO.

		$operadoraDetectada = 'NAO DETECTADO';

// ================================================================================================================================
var_dump($operadora);
var_dump($tipoDeConta);
/*
		if($operadora == 1 AND $tipoDeConta == 1) {
			$vlrsFatura = converteFaturaVivoFixo($arquivoTXT); // VIVO - FIXO
			$operadoraDetectada = 'VIVO FIXO';
		}
		if($operadora == 11 AND $tipoDeConta == 1) {
			$vlrsFatura = converteFaturaVivoFixoPontoComunicacao2019($arquivoTXT); // VIVO FIXO - PONTO DE COMUNICAÇÃO
			$operadoraDetectada = 'VIVO FIXO';
		}
		// -----------
		if($operadora == 1 AND $tipoDeConta == 2) {
			$vlrsFatura = converteFaturaVivoMovel($arquivoTXT); // VIVO - FIXO
			$operadoraDetectada = 'VIVO MOVEL';
		}
*/
// ================================================================================================================================

		if($operadora == 2 AND $tipoDeConta == 1) {
			// $vlrsFatura = converteFaturaVivoFixo($arquivoTXT);
		}
		
		if($operadora == 1 AND $tipoDeConta == 1) {
			$vlrsFatura = converteFaturaVivoMovel($arquivoTXT);
			var_dump($vlrsFatura);
			exit();
			$operadoraDetectada = 'VIVO MOVEL';
		}
		
		if($operadora == 3 AND $tipoDeConta == 1) {
			$vlrsFatura = converteFaturaNEXTELFixo2019($arquivoTXT);
			var_dump($vlrsFatura);
			exit();
			$operadoraDetectada = 'NEXTEL';
		}
		if($operadora == 4 AND $tipoDeConta == 1) {
			$vlrsFatura = converteFaturaNetClaroFixo($arquivoTXT); // NET - CLARO FIXO
			$operadoraDetectada = 'CLARO';
		}
		if($operadora == 5 AND $tipoDeConta == 1) {
			// $vlrsFatura = converteFaturaVivoFixo($arquivoTXT);
		}
		if($operadora == 6 AND $tipoDeConta == 1) {
			$vlrsFatura = converteFaturaALGARFixo2018($arquivoTXT);
			$operadoraDetectada = 'ALGAR';
		}
		if($operadora == 7 AND $tipoDeConta == 1) {
			$vlrsFatura = converteFaturaNetClaroFixo($arquivoTXT); // NET - CLARO FIXO
		}
		if($operadora == 8 AND $tipoDeConta == 1) {
			// $vlrsFatura = converteFaturaVivoFixo($arquivoTXT);
		}		

		$idRevenda = $_SESSION['idRevenda'];
		$arquivoHASH = md5($arquivoPDF);
		echo 'VLR FATURA.: '.$vlrFatura = $vlrsFatura[1];
		echo 'VLR CAPTUR.: '.$vlrCapturado = $vlrsFatura[0];
		exit();
		echo '<br/> VLR FATURA.: '.$vlrFatura = $vlrsFatura[1];
		$vlrCapturado = $vlrsFatura[0];
		$nomeCliente = 'SEM NOME';
		$numeroFatura = '0';
		$dataVencimento = '2019-01-01';
		$dataConversao = date("Y-m-d");

		$stmt = $_SG['link']->prepare('INSERT INTO `listaconversoes` (idRevenda, tipoConversao, nomeArquivo, nomeArquivoHASH, vlrFatura, vlrConvertido, nomeCliente, numeroFatura, dataVencimento, dataConversao, operadora) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$stmt->bindParam(1, $idRevenda); 		// ID REVENDA
		$stmt->bindParam(2, $retornoConversao);	// TIPO DE RETORNO
		$stmt->bindParam(3, $arquivoPDF);		// NOME PDF
		$stmt->bindParam(4, $arquivoHASH);		// NOME PDF CRIPTADO
		$stmt->bindParam(5, $vlrFatura); 		// VALOR DA FATURA
		$stmt->bindParam(6, $vlrCapturado); 	// VALOR CAPTURADO
		$stmt->bindParam(7, $nomeCliente); 		// NOME DO CLIENTE
		$stmt->bindParam(8, $numeroFatura); 	// NUMERO DA FATURA
		$stmt->bindParam(9, $dataVencimento); 	// DATA VENCIMENTO
		$stmt->bindParam(10, $dataConversao); 	// DATA DA CONVERSAO
		$stmt->bindParam(11, $operadoraDetectada); 	// OPERADORA

		if ($stmt->execute()) {
			# Deu certo.
		}else {
		   throw new PDOException("Erro: Não foi possível executar a declaração sql");
		}

	}elseif($retornoConversao == 1) { // 1 - CASO A CONVERSÃO OCORRA ERRO POR PDF CORROMPIDO OU SCANEADO O MESMO SERÁ REGISTRADO AQUI.

		$idRevenda = $_SESSION['idRevenda'];
		$arquivoHASH = md5($arquivoPDF);
		$vlrFatura = '0';
		$vlrCapturado = '0';
		$nomeCliente = 'SEM NOME';
		$numeroFatura = '0';
		$dataVencimento = '2019-01-01';
		$dataConversao = date("Y-m-d");

		$stmt = $_SG['link']->prepare('INSERT INTO `listaconversoes` (idRevenda, tipoConversao, nomeArquivo, nomeArquivoHASH, vlrFatura, vlrConvertido, nomeCliente, numeroFatura, dataVencimento, dataConversao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$stmt->bindParam(1, $idRevenda); 		// ID REVENDA
		$stmt->bindParam(2, $retornoConversao);	// TIPO DE RETORNO
		$stmt->bindParam(3, $arquivoPDF);		// NOME PDF
		$stmt->bindParam(4, $arquivoHASH);		// NOME PDF CRIPTADO
		$stmt->bindParam(5, $vlrFatura); 		// VALOR DA FATURA
		$stmt->bindParam(6, $vlrCapturado); 	// VALOR CAPTURADO
		$stmt->bindParam(7, $nomeCliente); 		// NOME DO CLIENTE
		$stmt->bindParam(8, $numeroFatura); 	// NUMERO DA FATURA
		$stmt->bindParam(9, $dataVencimento); 	// DATA VENCIMENTO
		$stmt->bindParam(10, $dataConversao); 	// DATA DA CONVERSAO

		if ($stmt->execute()) {
			# Deu certo.
		}else {
		   throw new PDOException("Erro: Não foi possível executar a declaração sql");
		}

	}else { // 2 - CASO A CONVERSÃO OCORRA ERRO POR SENHA O MESMO SERÁ REGISTRADO COMO SENHA REQUERIDA.
	
		echo '<br/>2 => '.$retornoConversao;

		$idRevenda = $_SESSION['idRevenda'];
		$arquivoHASH = md5($arquivoPDF);
		$vlrFatura = '0,00';
		$vlrCapturado = '0,00';
		$nomeCliente = 'SEM NOME';
		$numeroFatura = '0';
		$dataVencimento = '2019-01-01';
		$dataConversao = date("Y-m-d");

		$stmt = $_SG['link']->prepare('INSERT INTO `listaconversoes` (idRevenda, tipoConversao, nomeArquivo, nomeArquivoHASH, vlrFatura, vlrConvertido, nomeCliente, numeroFatura, dataVencimento, dataConversao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$stmt->bindParam(1, $idRevenda); 		// ID REVENDA
		$stmt->bindParam(2, $retornoConversao);	// TIPO DE RETORNO
		$stmt->bindParam(3, $arquivoPDF);		// NOME PDF
		$stmt->bindParam(4, $arquivoHASH);		// NOME PDF CRIPTADO
		$stmt->bindParam(5, $vlrFatura); 		// VALOR DA FATURA
		$stmt->bindParam(6, $vlrCapturado); 	// VALOR CAPTURADO
		$stmt->bindParam(7, $nomeCliente); 		// NOME DO CLIENTE
		$stmt->bindParam(8, $numeroFatura); 	// NUMERO DA FATURA
		$stmt->bindParam(9, $dataVencimento); 	// DATA VENCIMENTO
		$stmt->bindParam(10, $dataConversao); 	// DATA DA CONVERSAO

		if ($stmt->execute()) {
			# Deu certo.
		}else {
		   throw new PDOException("Erro: Não foi possível executar a declaração sql");
		}

	}

}
exit();
header("Location: principal.php");

?>

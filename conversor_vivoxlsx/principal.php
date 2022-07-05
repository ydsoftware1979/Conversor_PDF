<?php
error_reporting(E_ALL);

// include_once("connect.php");
// include_once("valida.php");

// $sql = ("SELECT * FROM `listaconversoes` WHERE idRevenda = '".$_SESSION['idRevenda']."' AND dataConversao = '".date("Y/m/d")."'");
// $query = $_SG['link']->query($sql);
// $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Conversor 1.0</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="css/bootstrap.css" rel="stylesheet" type="text/css">
	<script src="js/bootstrap.js"></script>
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container">
		<div class="card">
			<div class="card-header">
				CONVERSOR VIVO CONTA .XLSX PARA .CSV
			</div>
			<div class="card-body">
				<h6 class="card-title">Conversão da(s) seguinte(s) conta(s):</h6>
				<p class="card-text">VIVO-FIXO.</p>
				<form method="post" action="conversorPDF.php" enctype="multipart/form-data">
					<div class="input-group">
						<div class="custom-file">
							<input name="arquivos[]" type="file" multiple class="custom-file-input" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04">
							<label class="custom-file-label" for="inputGroupFile04">APENAS ARQUIVO(s) CONTA VIVO - XLSX</label>
						</div>
						<div class="input-group-append">
							<button class="btn btn-outline-secondary" type="submit" id="inputGroupFileAddon04">Enviar</button>
						</div>
					</div>
					</p>
				</form>
			</div>
		</div>
		<div class="card">
			<div class="card-header">
				CONVERSÕES DE HOJE - <?php echo date("d/m/Y");?>
			</div>
			<div class="card-body">
<?php

// foreach($resultado as $item){
	// if($item['tipoConversao'] == 0) {
		// if(number_format($item['vlrConvertido'], 2, ',', '.') == number_format($item['vlrFatura'], 2, ',', '.')) {
			// // echo '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge badge-primary badge-pill">'.$item['nomeArquivo'].'</span><span class="badge badge-dark badge-pill">OPERADORA.: '.$item['operadora'].'</span><span class="badge badge-secondary badge-pill">VALOR CAPTURADO.: '.number_format($item['vlrConvertido'], 2, ',', '.').'</span><span class="badge badge-secondary badge-pill">VALOR DA FAURA.: '.number_format($item['vlrFatura'], 2, ',', '.').'</span><span class="badge badge-light badge-pill">CONVERTIDO</span>';
			// echo '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge badge-primary badge-pill">'.$item['nomeArquivo'].'</span><span class="badge badge-dark badge-pill">OPERADORA.: '.$item['operadora'].'</span><span class="badge badge-success badge-pill">VALOR OK</span><span class="badge badge-secondary badge-pill">VALOR DA FAURA.: '.number_format($item['vlrFatura'], 2, ',', '.').'</span><span class="badge badge-light badge-pill">CONVERTIDO</span>';
		// }else {
			// if(number_format($item['vlrFatura'], 2, ',', '.') > number_format($item['vlrConvertido'], 2, ',', '.')) {
				// echo '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge badge-primary badge-pill">'.$item['nomeArquivo'].'</span><span class="badge badge-dark badge-pill">OPERADORA.: '.$item['operadora'].'</span><span class="badge badge-danger badge-pill">ATENÇÃO - VALOR NÃO BATEU -> </span><span class="badge badge-warning badge-pill">VALOR CAPTURADO A MENOS.: '.number_format($item['vlrConvertido'], 2, ',', '.').'</span><span class="badge badge-warning badge-pill">VALOR DA FAURA.: '.number_format($item['vlrFatura'], 2, ',', '.').'</span><span class="badge badge-light badge-pill">CONVERTIDO</span>';
			// }else {
				// echo '<li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge badge-primary badge-pill">'.$item['nomeArquivo'].'</span><span class="badge badge-dark badge-pill">OPERADORA.: '.$item['operadora'].'</span><span class="badge badge-danger badge-pill">ATENÇÃO - VALOR NÃO BATEU -> </span><span class="badge badge-warning badge-pill">VALOR CAPTURADO A MAIS.: '.number_format($item['vlrConvertido'], 2, ',', '.').'</span><span class="badge badge-warning badge-pill">VALOR DA FAURA.: '.number_format($item['vlrFatura'], 2, ',', '.').'</span><span class="badge badge-light badge-pill">CONVERTIDO</span>';
			// }
		// }
	// }elseif($item['tipoConversao'] == 1) {
		// echo '<li class="list-group-item d-flex justify-content-between align-items-center">'.$item['nomeArquivo'].'<span class="badge badge-danger badge-pill">ERRO AO CONVERTER - PDF SCANEADO OU ARQUIVO CORROMPIDO</span>';
	// }else {
		// echo '<li class="list-group-item d-flex justify-content-between align-items-center">'.$item['nomeArquivo'].'<span class="badge badge-danger badge-pill">PDF REQUER SENHA OU SENHA INVÁLIDA</span>';
	// }
// }

?>
			</div>
		</div>
	</div>
</body>
</html>

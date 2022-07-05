<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Conversor Conta Vivo</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/bootstrap.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF">
<font size="2" face="Courier">

<?php

require 'vendor/autoload.php';
require_once 'vendor/phpoffice/phpspreadsheet/src/Bootstrap.php';
include_once("funcoesParaFaturas2.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
$reader->setReadDataOnly(TRUE);

$arquivoXlsx = '9999835487186_oi.xls.xlsx'; // NOME COMPLETO COM EXTENSÃO.

$nomeArquivo = $arquivoXlsx.'-convertido';

$fp = fopen($nomeArquivo.".csv", "w+");

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

// $spreadsheet = $reader->load($arquivoXlsx);
// $worksheet = $spreadsheet->getActiveSheet();

$contador = 0;
$exibeHtml = 1;
$valor = 0.00;
$pegaValorTotal = 0;
$totalFatura = 0.00;
$totalPagar = 0.00;

$achouServicos = 0;
$capturaServicos  = 0;
$capturandoServicos = 0;

$celula = array();
$arrayCampos1 = array();
$arrayCampos2 = array();

echo '<table border="1" width=95% align="center">';
echo '<th bgcolor="#DCDCDC" style="text-align: center;">OPERADORA</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">NOME DA ORIGEM</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">NUMERO TELEFONE</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">RAMAL ASSOCIADO</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">DATA LIGACAO</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">HORARIO LIGACAO</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">TELEFONE CHAMADO</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">TRONCO</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">DESCRICAO</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">DURACAO/UNID</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">TARIFA</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">DEPTO</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">CONTA FATURA</th>
	<th bgcolor="#DCDCDC" style="text-align: center;">MES_REF</th>';

$ORIGEM 	= 0;
$DATAHORA 	= 1;
$DURACAO 	= 2;
$DESTINO 	= 3;
$DESCRICAO 	= 6;
$VALOR 		= 9;

$PHPSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($arquivoXlsx);
foreach ($PHPSpreadsheet->getWorksheetIterator() as $worksheet) {
    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set

		foreach ($cellIterator as $cell) {
            if (!is_null($cell)) {

				if($contador == 4) {
					if($cell->getFormattedValue() == 'Valor da Fatura') { // AO ENCONTRAR CERTA INFORMAÇÃO
						$pegaValorTotal = 1;
					}
				}
				if($contador == 5 AND $pegaValorTotal == 1) { // NUMERO TELEFONE
					$totalPagar = $cell->getValue();
					$pegaValorTotal = 0;
				}

				if($contador == $ORIGEM) { // NUMERO TELEFONE
					//$cell->getStyle()->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY2);
					$celula[$contador] = $cell->getFormattedValue();
				}
				if($contador == $DATAHORA) { // DATA e HORA DA LIGACAO
					$cell->getStyle()->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY2);
					$celula[$contador] = $cell->getFormattedValue();
				}
				if($contador == $DURACAO) { // DURACAO
					//$cell->getStyle()->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY2);
					$celula[$contador] = $cell->getFormattedValue();
				}
				if($contador == $DESTINO) { // TELEFONE CHAMADO
					//$cell->getStyle()->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY2);
					$celula[$contador] = $cell->getFormattedValue();
				}
				if($contador == $DESCRICAO) { // DESCICAO
					//$cell->getStyle()->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY2);
					$celula[$contador] = $cell->getFormattedValue();
				}
				if($contador == $VALOR) { // VALOR
					$celula[$contador] = $cell->getValue();
					// $celula[$contador] = $cell->getFormattedValue();
				}

				// if($contador == 8) { // TARIFA - APENAS PARA SERVICOS.
					// $celula[$contador] = $cell->getValue();
				// }

				if($contador == 5) { // VALOR SERVIÇOS
					$celula[$contador] = $cell->getValue();
				}

				// ABAIXO EXEMPLO
                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
					//$cell->getStyle()->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY2);
                    //echo $cell->getStyle()->getNumberFormat()->getFormatCode();
                    //echo '<br>';
                    //echo $cell->getValue();
                    //echo '<br>';
                    //echo $cell->getCalculatedValue();
                    //echo '<br>';
                    //echo substr($cell->getFormattedValue(),0,10).' - '.substr($cell->getFormattedValue(),11,8);
                    //echo '<br>';
                }
            }
			$contador += 1;
        }

		$contador = 0;
		// echo '<br/> ==>'.capturaData2('DD/MM/AAAA',$celula[$DATAHORA]);
		// echo '<br/> ==>'.capturaHora2('00:00:00',$celula[$DATAHORA]);
		// echo '<br/> ==>'.capturaValor2((string)$celula[$VALOR]);
		// echo '<br/> ==>'.$celula[$VALOR];
		// echo '<br/> ==>'.$celula[$DURACAO];
		//--------------[ PARTE DE CAPTURA ]--------------

		if(capturaData2('DD/MM/AAAA',(string)$celula[$DATAHORA]) AND capturaHora2('00:00:00',(string)$celula[$DATAHORA]) AND capturaHora2('0:00:00',$celula[$DURACAO])) {

			$codigoCliente		= $celula[$ORIGEM]; // $celula[4];	// NOME DA ORIGEM
			$arrayCampos1[1]	= $celula[$ORIGEM];	// NUMERO TELEFONE
			$arrayCampos1[2]	= $celula[$ORIGEM];	// RAMAL ASSOCIADO
			$arrayCampos1[3]	= capturaData2('DD/MM/AAAA',$celula[$DATAHORA]);	// DATA LIGACAO
			$arrayCampos1[4]	= capturaHora2('00:00:00',$celula[$DATAHORA]);	// HORA LIGACAO
			$arrayCampos1[5]	= $celula[$DESTINO];	// TELEFONE CHAMADO
			$arrayCampos1[6]	= $celula[$ORIGEM];	// TRONCO
			$arrayCampos1[7]	= $celula[$DESCRICAO];	// DESCRICAO
			$arrayCampos1[8] 	= decimo('00:00:00', $celula[$DURACAO]);	// DURACAO
			$arrayCampos1[9]	= $celula[$VALOR]; // VALOR

			$totalFatura += $arrayCampos1[9];

			if(empty($todosdescricaoServicos["{$arrayCampos1[7]}"])) {
				$str = trim($arrayCampos1[7]);
				$todosdescricaoServicos["{$str}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$arrayCampos1[7]}"])) {
				$str = trim($arrayCampos1[7]);
				$todosdescricaoServicos["{$str}"] += (float)$valor;
			}else {
				$str = trim($arrayCampos1[7]);
				$todosdescricaoServicos["{$str}"] += (float)$valor;
			}

			if($exibeHtml == 1) {
				echo '<tr>';
				echo '<td align="center"></td>'
				.'<td align="center">'.$codigoCliente   /* NOME DA ORIGEM. */		.'</td>'
				.'<td align="center">'.$arrayCampos1[1] /* NUMERO TELEFONE */      	.'</td>'
				.'<td align="center">'.$arrayCampos1[2] /* RAMAL ASSOCIADO */       .'</td>'
				.'<td align="center">'.$arrayCampos1[3] /* DATA LIGAÇÃO */          .'</td>'
				.'<td align="center">'.$arrayCampos1[4] /* HORARIO LIGAÇÃO */       .'</td>'
				.'<td align="center">'.$arrayCampos1[5] /* TELEFONE CHAMADO */      .'</td>'
				.'<td align="center">'.$arrayCampos1[6] /* TRONCO */                .'</td>'
				.'<td align="center">'.$arrayCampos1[7] /* DRESCRIÇÃO */            			.'</td>'
				.'<td align="center">'.$arrayCampos1[8].' - '.$celula[$DURACAO] /* DURAÇÃO/UNID.*/          .'</td>'
				.'<td align="right">' .number_format($arrayCampos1[9], 2, ',', '.') /* TARIFA*/					.'</td>'
				// .'<td align="right">' .number_format(str_replace(",",".",$arrayCampos1[9]), 2, ',', '.') /* TARIFA*/					.'</td>'
				.'<td align="center"></td>'/* */
				.'<td align="center"></td>'
				.'<td align="center"></td>';
				echo '<tr/>';
			}

			$tudo1 = ';'					// OPERADORA
					.$codigoCliente.';'		// NOME DA ORIGEM
					.$arrayCampos1[1].';'	// NUMERO TELEFONE
					.$arrayCampos1[2].';'	// RAMAL ASSOCIADO
					.$arrayCampos1[3].';'	// DATA LIGACAO
					.$arrayCampos1[4].';'	// HORA LIGACAO
					.$arrayCampos1[5].';'	// TELEFONE CHAMADO
					.$arrayCampos1[6].';'	// TRONCO
					.iconv("UTF-8", "Windows-1252",$arrayCampos1[7]).';'	// DESCRICAO
					.$arrayCampos1[8].';'	// DURACAO/UNID.
					//.number_format($arrayCampos1[9], 2, ',', '.').';'	// TARIFA
					.number_format(str_replace(",",".",$arrayCampos1[9]), 2, ',', '.').';'	// TARIFA
					.';'					// DEPTO.
					.';'					// CONTA DE FATURA
					.';'					// MES_REF
					."\r\n";

			if (!fwrite($fp, $tudo1)) { 
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
		}

		// APENAS SERVIÇOS
		if(trim($celula[0]) == 'Serviços Mensais') {
			$achouServicos = 1;
		}
		if($achouServicos == 1) {
			if(trim($celula[0]) == 'Tipo' AND trim($celula[5]) == 'Valor') {
				$capturaServicos = 1;
				$achouServicos = 0;
			}
		}
		if(trim($celula[0]) == 'Total') {
			$capturaServicos = 0;
			$capturandoServicos = 0;
		}

		if($capturaServicos == 1) {
			$capturandoServicos += 1;
		}

		if($capturandoServicos > 1) {

			// if(strlen(trim($celula[0])) > 0) {
				// $codigoCliente = $celula[0];
			// }
			// $codigoCliente = '';

			$arrayCampos2[1] = $codigoCliente; // NUMERO TELEFONE
			$arrayCampos2[2] = $codigoCliente; // RAMAL ASSOCIADO
			$arrayCampos2[6] = $codigoCliente; // TRONCO

			//$codigoCliente		= $celula[0];	// NOME DA ORIGEM
			//$arrayCampos2[1]	= $celula[1];	// NUMERO TELEFONE
			//$arrayCampos2[2]	= $celula[1];	// RAMAL ASSOCIADO
			$arrayCampos2[3]	= $celula[$DESTINO];	// DATA LIGACAO
			$arrayCampos2[4]	= '';	// HORA LIGACAO
			$arrayCampos2[5]	= '';	// TELEFONE CHAMADO
			//$arrayCampos2[6]	= $celula[1];	// TRONCO
			$arrayCampos2[7]	= $celula[2];	// DESCRICAO
			$arrayCampos2[8]	= '';	// DURACAO
			$arrayCampos2[9]	= (float)$celula[5];	// TARIFA
			// $arrayCampos2[9]	= str_replace(array(".", ","), array("", ","), $arrayCampos2[9]);
	
			$valor_ = $arrayCampos2[9];
			$totalFatura += $arrayCampos2[9];
			
			if(empty($todosdescricaoServicos["{$arrayCampos2[7]}"])) {
				$str = trim($arrayCampos2[7]);
				$todosdescricaoServicos["{$str}"] = 0;
			}

			if(empty($todosdescricaoServicos["{$arrayCampos2[7]}"])) {
				$str = trim($arrayCampos2[7]);
				$todosdescricaoServicos["{$str}"] += (float)$valor;
			}else {
				$str = trim($arrayCampos2[7]);
				$todosdescricaoServicos["{$str}"] += (float)$valor;
			}

			if(1 == $exibeHtml) {
				echo '<tr>';
				echo '<td align="center"></td>' // OPERADORA
				.'<td align="center">'.$codigoCliente.'</td>' // NOME DA ORIGEM
				.'<td align="center">'.$arrayCampos2[1].'</td>' // NUMERO TELEFONE
				.'<td align="center">'.$arrayCampos2[2].'</td>' // RAMAL ASSOCIADO
				.'<td align="center">'.$arrayCampos2[3].'</td>' // DATA LIGACAO
				.'<td align="center">'.$arrayCampos2[4].'</td>' // HORARIO LIGACAO
				.'<td align="center">'.$arrayCampos2[5].'</td>' // TELEFONE CHAMADO
				.'<td align="center">'.$arrayCampos2[6].'</td>' // TRONCO
				.'<td align="center">'.$arrayCampos2[7].'</td>' // DESCRICAO
				.'<td align="center">'.$arrayCampos2[8].'</td>' // DURACAO
				.'<td align="right">'.number_format($arrayCampos2[9], 2, ',', '.').'</td>' // TARIFA
				.'<td align="center"></td>' // DEPARTAMENTO
				.'<td align="center"></td>' // CONTA FATURA
				.'<td align="center"></td>'; // MES_REF
				echo '<tr/>';
			}
								
			$tudo2 = 
					';'	// OPERADORA
					.$codigoCliente.';'	// NOME DA ORIGEM
					.$arrayCampos2[1].';'	// NUMERO TELEFONE
					.$arrayCampos2[2].';'	// RAMAL ASSOCIADO
					.$arrayCampos2[3].';'	// DATA LIGACAO
					.$arrayCampos2[4].';'	// HORA LIGACAO
					.$arrayCampos2[5].';'	// TELEFONE CHAMADO
					.$arrayCampos2[6].';'	// TRONCO
					.iconv("UTF-8", "Windows-1252",$arrayCampos2[7]).';'	// DESCRICAO
					.$arrayCampos2[8].';'	// DURACAO/UNID.
					.number_format(str_replace(",",".",$arrayCampos2[9]), 2, ',', '.').';'	// TRIFA
					.';'	// DEPTO.
					.';'	// CONTA DE FATURA
					.';'	// MES_REF
					."\r\n";
			if (!fwrite($fp, $tudo2)) { 
				print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
				exit;
			}
		}
		//------------------------------------------------
		// echo '<br>--------------';
    }
}

// $diferenca2 = (float)$totalPagar - (float)$totalFatura;
	
	// $valor_ = str_replace(",",".",$diferenca2);
	// $totalFatura += $valor_;
	
	// if(empty($todosdescricaoServicos["VALOR NAO ENCONTRADO NO EXCEL"])) {
		// $todosdescricaoServicos["VALOR NAO ENCONTRADO NO EXCEL"] = 0;
	// }

	// if(empty($todosdescricaoServicos["VALOR NAO ENCONTRADO NO EXCEL"])) {
		// $todosdescricaoServicos["VALOR NAO ENCONTRADO NO EXCEL"] += (float)$valor;
	// }else {
		// $todosdescricaoServicos["VALOR NAO ENCONTRADO NO EXCEL"] += (float)$valor;
	// }

	// if(1 == $exibeHtml) {
		// echo '<tr>';
		// echo '<td align="center"></td>'
		// .'<td align="center">'.$codigoCliente.'</td>'
		// .'<td align="center">'.$arrayCampos2[1].'</td>'
		// .'<td align="center">'.$arrayCampos2[1].'</td>'
		// .'<td align="center"></td>'
		// .'<td align="center"></td>'
		// .'<td align="center"></td>'
		// .'<td align="center">'.$arrayCampos2[6].'</td>'
		// .'<td align="center"><b>VALOR NAO ENCONTRADO NO EXCEL<b/></td>'
		// .'<td align="center"></td>'
		// .'<td align="right"><b>'.number_format($diferenca2, 2, ',', '.').'<b/></td>'
		// .'<td align="right"></td>'
		// .'<td align="right"></td>'
		// .'<td align="right"></td>';
		// echo '<tr/>';
	// }
	
	// $registroDiferenca = '';
	// $registroDiferenca = 
					// ';'	// OPERADORA
					// .$codigoCliente.';'	// NOME DA ORIGEM
					// .$arrayCampos2[1].';'	// NUMERO TELEFONE
					// .$arrayCampos2[1].';'	// RAMAL ASSOCIADO
					// .';'	// DATA LIGACAO
					// .';'	// HORA LIGACAO
					// .';'	// TELEFONE CHAMADO
					// .$arrayCampos2[6].';'	// TRONCO
					// .'VALOR NAO ENCONTRADO NO EXCEL;'	// DESCRICAO
					// .';'	// DURACAO/UNID.
					// .number_format($diferenca2, 2, ',', '.').';'	// VALOR
					// .';'	// DEPTO.
					// .';'	// CONTA DE FATURA
					// .';'	// MES_REF
					// ."\r\n";
	
	// if (!fwrite($fp, $registroDiferenca)) { 
		// print "Erro escrevendo no arquivo ($arquivo) ou esta sendo usado por outro programa.";
		// exit;
	// }
	//***************************************************************************************************		

echo '</table>';
	// foreach($descricaoServicos as $desc) {
		// echo '<tr>';
		// echo '<td align="center"></td>'
		// .'<td align="center"></td>'
		// .'<td align="center"></td>'
		// .'<td align="left">'.key($descricaoServicos).'</td>'
		// .'<td align="center"></td>'
		// .'<td align="center"></td>'
		// .'<td align="center"></td>'
		// .'<td align="center"></td>'
		// .'<td align="center"></td>'
		// .'<td align="right">'.number_format($desc, 2, ',', '.').'</td>';
		// echo '<tr/>';
		// next($descricaoServicos);
	// }
	

	echo '<br/> TOTAL .: '.$totalFatura;
	
	$diferenca = (float)$totalPagar - (float)$totalFatura;

	if($totalFatura == $totalPagar) {

		echo '<table border="2" width=90% align="center">';
		echo '<tr>';
		echo '<td bgcolor="#98FB98" colspan=="11" align="right">';
		echo 'CAPTURADO : <strong>'.number_format($totalFatura, 2, ',', '.').'</strong>';
		echo '</td>';
		echo '</tr>';
		echo '<table/>';
	}else {
		echo '<table border="2" width=90% align="center">';
		echo '<tr>';
		echo '<td bgcolor="#FF6347" colspan=="11" align="right">';
		echo 'ATENÇÃO - NÃO BATEU : <strong>'.number_format($totalFatura, 2, ',', '.').'</strong>';
		echo '</td>';
		echo '</tr>';
		echo '<table/>';
	}

	echo '<table border="2" width=90% align="center">';
	echo '<tr>';
	echo '<td colspan=="11" align="right">&nbsp';
	echo 'VALOR DA CONTA : <strong>'.number_format($totalPagar, 2, ',', '.').'</strong>';
	echo '</td>';
	echo '</tr>';
	echo '<table/>';

	if(number_format($diferenca, 2, ',', '.') == '0,00') {
		echo '<table border="2" width=90% align="center">';
		echo '<tr>';
		echo '<td colspan=="11" align="right">&nbsp';
		echo '<strong>OK.</strong>';
		echo '</td>';
		echo '</tr>';
		echo '<table/>';
	}else {
		echo '<table border="2" width=90% align="center">';
		echo '<tr>';
		echo '<td colspan=="11" align="right">&nbsp';
		echo 'DIFERENÇA : <strong>'.number_format($diferenca, 2, ',', '.').'</strong>';
		echo '</td>';
		echo '</tr>';
		echo '<table/>';
	}

	echo '<table border="0" width=90% align="center">';
	echo '<tr>';
	echo '<td colspan=="11" align="right">&nbsp';
	echo '</td>';
	echo '</tr>';
	echo '<table/>';

	echo '<table/>';

echo '
</font>
</body>
</html>';

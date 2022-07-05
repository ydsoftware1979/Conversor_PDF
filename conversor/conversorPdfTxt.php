<?php

function conversorPDFTXT (string $destinoArquivo, string $senhaPDF = NULL) {

	$sistemaOperacional = PHP_OS;

	if($sistemaOperacional == 'WINNT') {

		if(file_exists($destinoArquivo)) {

			exec('java -jar conversor.jar "'.$destinoArquivo.'" "'.$senhaPDF.'" "'.$destinoArquivo.'.txt"', $e); 					// PARA USO EM WINDOWS.

		}else {
			echo '</p>Arquivo nao encontrado';
		}

		if(empty($e[0])) {
			// SE ESTIVER FALTANDO O ARQUIVO CONVERTIDO, HOUVE ERRO AO CONVERTER E O MESMO RECEBERÁ O RETONO 1 DE ERRO AO CONVETER.

			// A SENHA PARA O PDF DA VIVO COM SENHA E "004"

			$arquivosTXT = glob("$destinoArquivo{*.txt}", GLOB_BRACE);
			$arquivosPDF = glob($destinoArquivo, GLOB_BRACE);

			foreach($arquivosPDF as $pastasPDF) {

				$ok = 0;
				foreach($arquivosTXT as $pastasTXT) {

					if($pastasPDF.'.txt' == $pastasTXT) {

						if(filesize($pastasPDF.'.txt') > 11) {
							$ok = 1;
						}
					}
				}
				if($ok == 1) {
					return 0; // SUCESSO.
				}else {
					return 1; // ERRO.
				}
			}

		}else {
			return 2; // FALTA DE SENHA.
		}

	}elseif($sistemaOperacional == 'Linux') {
		if(file_exists($destino)) {
			// exec('java -jar -Xmx4096M conversor.jar "'.$destino.'" "004" "'.$destino.'.TXT"',$e,$retorno); 		// PARA USO EM WINDOWS.
			// exec('java -jar conversor.jar "'.$destino.'" "062" "'.$destino.'.txt"',$e,$retorno); 				// PARA USO EM WINDOWS.
			// exec('java -jar conversor.jar "'.$destino.'" "'.$senhaPdf.'" "'.$destino.'.txt"',$e, $retorno); 		// PARA USO EM WINDOWS.
			// exec('java -jar conversor.jar "'.$destino.'" "'.$senhaPdf.'" "'.$destino.'.txt"', $resultado); 		// PARA USO EM WINDOWS.

			exec('java -jar conversor.jar "'.$destino.'" "'.$senhaPdf.'" "'.$destino.'.txt"', $e); 					// PARA USO EM WINDOWS.

		}else {
			echo '</p>Arquivo nao encontrado';
		}

		// 0 = SUCESSO, 
		// 1 = FALTA DE SENHA NO PDF, 
		// 3 = ERRO AO CONVERTER RECEBE 0, MAS FALTANDO DO TXT DA CONVERSÃO JÁ INDICA ERRO.

		if(empty($e[0])) {
			$tipoConversão = 0; // SUCESSO OU ERRO.
		}else {
			$tipoConversão = 2; // FALTA DE SENHA.
		}
	}else {
		return NULL;
	}
}

?>
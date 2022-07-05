<?php

session_start();
session_destroy();

include_once("connect.php"); // Inclui o arquivo com o sistema de segurança

$sql = ("SELECT * FROM `revendas`");
$query = $_SG['link']->query($sql);
$resultado = $query->fetchAll(PDO::FETCH_ASSOC);

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
	<style>
		.center {
		  margin: auto;
		  width: 60%;
		  padding: 10px;
		}
	</style>
</head>
<body>
	<center>
	<div class="container">
		<div class="form-group row">
			<div class="col-sm-6 center">
				<div class="card">
					<div class="card-header">
						ESCOLHA A REVENDA
					</div>
					<div class="card-body">
						<form method="post" action="valida.php" enctype="multipart/form-data">
							<div class="form-group">
								<select class="form-control" id="exampleFormControlSelect1" name="dadosRevenda">
									<option>ESCOLHA AQUI A REVENDA</option>
									<?php
										foreach($resultado as $item){
											echo '<option value="'.$item['id'].'">'.$item['nomeRevenda'].'</option>';
										}
									?>
								</select>
							</div>
							<input class="btn btn-primary" type="submit" value="Entrar">
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	</center>
</body>
</html>

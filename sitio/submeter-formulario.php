<?php 
	$nombre = $_POST['nombre'];
	$email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $website = $_POST['website'];
	$asunto = $_POST['asunto'];
	$mensaje = "Nombre: ".$nombre."<br> Email: $email<br>Telefono: $telefone<br>Website: $website<br>Mensaje:".$_POST['mensaje'];


	if(mail('sebastian.marcano@gmail.com', $asunto, $mensaje)){
		echo "Correo enviado";
	}
?>
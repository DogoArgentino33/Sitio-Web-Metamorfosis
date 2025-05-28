<?php
#HAY UN ERROR CON EL PUERTO DE SQL, POR LO TANTO SIEMPRE USAR PDO Y ENTRAR VIA http://localhost/[carpeta]/[archivo.php] 
#Cambiar el puerto a otro para que el error no salga
$dbhost="localhost";
$dbusuario="root";
$dbpassword= "rojas.u33"; #1234
$db="pruebametamorfosis";
$conexion = new mysqli ($dbhost,$dbusuario,$dbpassword,$db);

#http://localhost/Sitio-Web-Metamorfosis/index.php

if (!$conexion)
{
    die("Connection failed: ". mysqli_connect_error());
}

date_default_timezone_set('America/Argentina/Catamarca');

?>
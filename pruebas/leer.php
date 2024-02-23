
<?


$path=".";



$directorio=dir($path);




/*
echo "Directorio ".$path.":<br><br>";
*/


while ($archivo = $directorio->read())
{
	echo "<a href=\"$archivo\"> $archivo</a>";
	echo "<br>";
}


$directorio->close();


?>


<html>
<head>
<title>Minha pagina</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="refresh" content="10">
</head>

<body style="background-color: #000000;">

<div align="">
<br><br>
<img src="http://www.realpoker.com.br/uploads/2016_04/images/resized/logoreal[370x141].png"><br>
<br><br><br><br>
	

	<div style="color: #fff; font-size: 52px; font-family: Tahoma;" align="left"> 

	Aproxime a ficha do leitor...

	<br><br><br>

	<?php  

	if ($_POST['name'] == "0001108369") {
	echo "Registro: <b>8369 </b><br><br>";
	echo "Valor da Ficha: <b>25</b><br>";
	echo "Cliente: <b>BSOP</b> <br>";
	echo "Fabricação: <b>22/02/2017</b><br>";
	} 

	if ($_POST['name'] == "0001081087") {
	echo "Registro: <b>1087 </b><br><br>";
	echo "Valor da Ficha: <b>25</b><br>";
	echo "Cliente: <b>BSOP </b> <br>";
	echo "Fabricação: <b>06/06/2016</b><br>";

	} 

	if ($_POST['name'] == "0001123307") {
	echo "Registro: <b>3307 </b><br><br>";
	echo "Valor da Ficha: <b>25</b><br>";
	echo "Cliente: <b>BSOP </b> <br>";
	echo "Fabricação: <b>11/11/2011</b><br>";

	} 



	?>

	</div>

</div>
<div>

	<form action="rfid.php" method="post">
	 <p> <input style="width: 1px; background-color: #000;" type="text" name="name" autofocus/></p>
	 <p><input style="width: 1px; background-color: #000; border-color: #000;"  type="submit"  /></p>
	</form>

	</div>

</body>
</html>
<!doctype html>
<html>
<head>
<title>Sun lab RNAseq Pipeline</title>
<meta name="description" content="graphics user interface for the RNAseq pipeline to communicate with the super computer">
<meta name="keywords" content="RNAseq">
</head>
<body>
<h2>Welcome to the Sun Lab RNA sequencing pipeline</h2>
<h3>Enter TSCC login credentials to begin</h3>
<form action="menu.php" method="post">
Username: <input type="text" name="username" value=""><br>
Password: <input type="password" name="password" value=""><br>
<select name='step' id='stepS' hidden='hidden'>
	<option value='fileselect' selected>File Select</option>
	<option value='attributes'>Define Attributes</option>
	<option value='parameters'>Parameters</option>
	<option value='RNAseq'>RNAseq</option>
</select>
	
<input type="submit">
</body>
<html>
<?php
include 'vendor/autoload.php';
use phpseclib3\Net\SSH2;

#use 0 and 1 rather than true and false which are evaluating to strings
$password = "";
$username = "";
$proName = "";

if(isset($_POST["proName"]))
{
	$proName = $_POST["proName"];
}
if(isset($_POST["username"]) && isset($_POST["password"])){
	$username = $_POST["username"];
	$password = $_POST["password"];
}
echo "<title>Sun Lab RNAseq</title>";
	echo "<div>Your task name: ",$proName,"</div>";
	echo "<h6>Status report from TSCC:</h6><p>";
	#log in to server and conduct RNAseq
	ini_set('max_execution_time', 500);
	$ssh = new SSH2('tscc-login.sdsc.edu');
	if (!$ssh->login($_POST["username"], $_POST["password"])) {
    		throw new \Exception('Login failed');
	}

	
	#get status
	$cdCMD = "qstat -nu ".$username."\n";
	$ssh->read('');
	$ssh->write($cdCMD);
	for ($x = 0; $x < 1; $x++) {
  		echo $ssh->read('');
	}
	echo "</p>";



	#produce invisible form and check status update button
	echo "<form id='next' action='status.php' method='post'>";
	echo "<input type='text' name='proName' value='",$proName,"' hidden = 'hidden'>";
	echo "<input type='text' name='username' value='",$username,"' hidden = 'hidden'><input type='text' name='password' value='",$password,"' hidden = 'hidden'>";
	echo "<br><button type='button' onclick='
		document.getElementById(\"body2\").innerHTML = \"<h3>Retrieving status from the TSCC. This may take some time</h3><div class=\\\"loader\\\"></div>\";
		document.getElementById(\"next\").submit();
	'>Refresh Status</button></form>";
	echo "</div><div id='body2' ></div><style>
	.info{
		background: #91A5B4;
		border-radius: 25px;
		width:600px;
		padding: 25px;
		margin-top: 5px;
  		margin-bottom: 5px;
  		margin-right: 5px;
 	 	margin-left: 5px;
		color: white;
	}
	.loader {
  		border: 16px solid #f3f3f3; /* Light grey */
  		border-top: 16px solid #3498db; /* Blue */
  		border-radius: 50%;
  		width: 60px;
  		height: 60px;
  		animation: spin 2s linear infinite;
	}

	@keyframes spin {
  		0% { transform: rotate(0deg); }
  		100% { transform: rotate(360deg); }
	}
	</style>";
?>

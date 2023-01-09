<?php
#next steps:
#
#add the attributes page
#
#dependencies to communicate with command line
include 'vendor/autoload.php';
use phpseclib3\Net\SSH2;

#use 0 and 1 rather than true and false which are evaluating to strings
$password = "";
$username = "";
$dir = "/projects/ps-bryansunlab";


if(isset($_POST["username"]) && isset($_POST["password"])){
	$username = $_POST["username"];
	$password = $_POST["password"];
}

echo "<title>Sun Lab RNAseq</title>";
echo "<div id='body'><h5>Start a new analysis</h5>";
echo "<form id='next' action='fileSelect.php' method='post'>Project name:<input type='text' name='proName' value=''><input type='text' name='username' value='",$username,"' hidden='hidden'><input type='text' name='password' value='",$password,"' hidden='hidden'><input type='text' name='directory' id='direct' value=\"",$dir,"\" hidden='hidden'><select name='step' id='stepS' hidden='hidden'><option value='fileselect' selected>File Select</option></select><br>
<button id='sub' type='button' onclick='
document.getElementById(\"body\").setAttribute(\"hidden\",\"true\");
document.getElementById(\"body2\").innerHTML = \"<h3>Opening ps-bryansunlab directory. This may take several minutes</h3><div class=\\\"loader\\\"></div>\";
document.getElementById(\"next\").submit();'>Submit</button>
</form>";
echo "<h5>Continue an old analysis</h5>";
echo "<form id='old' action='resume.php' method='post'><input type='text' name='username' value='",$username,"' hidden='hidden'><input type='text' name='password' value='",$password,"' hidden='hidden'><input type='submit'></form>";
echo "<h5>Download results of an old analysis</h5>";
echo "<form id='down' action='download.php' method='post'><input type='text' name='username' value='",$username,"' hidden='hidden'><input type='text' name='password' value='",$password,"' hidden='hidden'><input type='submit'></form></div><div id='body2'></div>";
echo "<style>
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
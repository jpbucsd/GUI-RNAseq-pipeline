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
$fSelect = "";
$sDir = "";
$proName = "";

if(isset($_POST["proName"]))
{
	$proName = $_POST["proName"];
}

if(isset($_POST["directory"]))
{
	$dir = $_POST["directory"];
}
if(isset($_POST["sDir"]))
{
	$sDir = $_POST["sDir"];
}
if(isset($_POST["username"]) && isset($_POST["password"])){
	$username = $_POST["username"];
	$password = $_POST["password"];
}
if(isset($_POST["step"])){
	if ($_POST["step"] == 'attributes')
	{
		echo "<title>Sun Lab RNAseq</title>";
		echo "<body><h3>Create attributes</h3><p>Name of attribute:</p><input type=\"\" id=\"attr\" value=\"\"><button onclick=\"addAttribute()\">Add attribute</button><table id=\"attributes\"><tr><th>Attributes:</th></tr></table>";
		echo "<table id=\"files\" hidden = 'hidden'><tbody><tr><th>Files:</th></tr></tbody><tbody id='addedFiles'>";
		if(isset($_POST["fileSelect"]))
		{
			$fSelect = $_POST["fileSelect"];
		}
		$fsArray = explode("?_?", $fSelect);
		if(count($fsArray) != 1 || $fsArray[0] != "")
		{
			for($i = 0, $size = count($fsArray); $i < $size; ++$i) {
				echo "<tr id='file",$i,"'><td id='file",$i,"path'>",$fsArray[$i],"</td><td><button onClick='rmFile(\"",$i,"\")'>remove</button></td><td hidden='hidden' class='index'>",$i,"</td></tr>";
			}
		}



		echo "</tbody></table>";

	
	
		//form with submit to move on
		echo "<button onClick='assignAttributes()'>Assign attributes to files</button>";
		echo "<form id='next' action='assign.php' method='post' hidden='hidden'>Project name:<input type='text' name='proName' value='",$proName,"'><br>Username: <input type='text' name='username' value='",$username,"'><br>Password: <input type='text' name='password' value='",$password,"'><br>Directory: <input type='text' name='directory' id='direct' value=\"",$dir,"\"><br>Selected Directory: <input type='text' name='sDir' id='sDir' value=\"",$sDir,"\"><br>Files: <input type='text' name='fileSelect' id='fSelect' value=\"",$fSelect,"\"><br>Attributes: <input type='text' name='attrSelect' id='aSelect' value=\"\"><br><select name='step' id='stepS'><option value='fileselect' selected>File Select</option><option value='attributes'>Define Attributes</option><option value='assign'>Assign</option><option value='parameters'>Parameters</option><option value='RNAseq'>RNAseq</option></select><br><input type='submit'></form>";
	
		//Add script
		echo "<script>
		var table1 = document.getElementById('files');
		var fileNames = [];
		var filePaths = [];
		var fileN = 0;
		console.log(\"rows detected: \" + table1.tBodies[1].rows.length);
		for (var i = 0; i < table1.tBodies[1].rows.length; i++) {
			const path = document.getElementById(\"file\" + fileN + \"path\").innerHTML;
			filePaths.push(path);
			fileN += 1;	
        	}
		function rmFile(idx)
		{
			const element = document.getElementById(\"file\" + idx);
			element.remove();
		}



		var attrs = [];
		var attrN = 0;
		// append new value to the array
		function addAttribute() {
  			attrs.push(document.getElementById(\"attr\").value);
  			document.getElementById(\"attributes\").innerHTML += \"<tr id='attr\" + attrN + \"'><td class='attribs'>\" + document.getElementById(\"attr\").value + \"</td><td><button onClick='rmAttr(\" + attrN + \")'>remove</button></td></tr>\";
  			document.getElementById(\"attr\").value = \"\";
  			attrN += 1;
		}
		function rmAttr(idx)
		{
			attrs.splice(idx, 1);
			const element = document.getElementById(\"attr\" + idx);
			element.remove();
		}
		function assignAttributes()
		{
			document.getElementById(\"next\").action = \"assign.php\";
			var attrString = \"\";
			for (var i = 0; i < attrs.length; i+=1) {
				attrString += attrs[i] + \"?_?\";
			}
			document.getElementById('aSelect').value = attrString.slice(0, -3);
			document.getElementById(\"stepS\").value = 'assign';
			document.getElementById(\"next\").submit();
		}
		</script>";

	} 
	

}
?>
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
if(isset($_POST["username"]) && isset($_POST["password"])){
	$username = $_POST["username"];
	$password = $_POST["password"];
}
if(isset($_POST["step"])){
	if($_POST["step"] == 'fileselect')
	{

	ini_set('max_execution_time', 300);
	$ssh = new SSH2('tscc-login.sdsc.edu');
	if (!$ssh->login($_POST["username"], $_POST["password"])) {
    		throw new \Exception('Login failed');
	}
	$cdCMD = "cd " . $dir . "\n";

	$ssh->read('');
	$ssh->write($cdCMD);
	$ssh->read('');
	$ssh->write("ls\n");
	$lsString = $ssh->read('');
	//filter string
	$lsA = str_split($lsString);
	$lsFilt = "";
	$dZone = 0;
	foreach ($lsA as $char) {
		if($dZone == 1)
		{
			if(ord($char) == 27)
			{
				$dZone = 2;
			}
		}
		elseif($dZone == 2)
		{
			if(ord($char) == 51)
			{
				$dZone = 3;
			}
		}elseif($dZone == 3)
		{
			if(ord($char) == 52)
			{
				$dZone = 4;
				$lsFilt = $lsFilt . chr(27) . chr(52);
			}elseif(ord($char) == 49)
			{
				$lsFilt = $lsFilt . chr(27) . chr(49);
				$dZone = 4;
			}else
			{
				//its unclear what these are, dont output anything, wait until the next round
				$lsFilt = $lsFilt . chr(27) . $char;
				$dZone = 4;
			}
		}elseif($dZone == 4)
		{
			if(ord($char) == 109)
			{
				$dZone = 0;
			}
		}
		elseif($dZone == 0)
		{
			if(ord($char) == 27)
			{
				$dZone = 1;
			}else{
				if(ord($char) == 10 || ord($char) == 13)
				{
				}
				else{
					$lsFilt = $lsFilt . $char;
				}	
			}
			
		}
		
    		//echo $char, ":",ord($char),"<br>";
	}

	//echo "<p>reading all characters, STAND BY!!!</p>";
	//$lsAB = str_split($lsString);
	//foreach ($lsAB as $char) {
    	//	echo $char, ":",ord($char),"<br>";
	//}

	$delimiter = chr(27);
	$lsArray = explode($delimiter, $lsFilt);

	//$ssh->exec('cd /projects/ps-bryansunlab');
	//echo "<p>current directory: </p>";
	//echo "<p>", $ssh->exec('pwd'), "</p>";
	//echo $ssh->exec('ls');
	
	echo "<title>Sun Lab RNAseq</title>";
	//create file selection table
	echo "<div id='body1'><table><tbody><tr><td><b>Directory</b>: ",$dir,"</td><td><button onClick='chooseDirectory()'>Select Directory as Fastq Directory</button></td><td><button onClick='parentDir()'>Go to parent directory</button></td></tr></tbody><tbody><tr><th>Type</th><th>Btn</th><th>Idx</th><th>Name</th><th>Path</th></tr>";
	//starting with i = 1 cuts out ls
	for($i = 1, $size = count($lsArray); $i < $size; ++$i) {
		$type = $lsArray[$i][0];
		$fName = substr($lsArray[$i], 1);
		echo "<tr><td id='type",$i,"'>";
		if($type == chr(52))
		{
			echo "directory";
			echo "</td><td><button onClick='visitDir(",$i,")'>Visit</button></td>";
		}
		elseif($type == chr(49))
		{
			echo "fastq file";
			echo "</td><td><button onClick='selectFile(",$i,")' hidden='hidden'>Select</button></td>";
		}else{
			echo "unknown type";
			echo "</td><td><button onClick='visitDir(",$i,")'>Visit as directory</button><button onClick='selectFile(",$i,")' hidden='hidden'>Select</button></td>";
		}
		echo "<td>",$i,"</td><td id = 'name",$i,"'>",$fName,"</td><td id = 'path",$i,"'>",$dir,"/'",$fName,"'</td></tr>";
	}
	echo "</tbody></table>";
	echo "<div id='rowN' hidden='hidden'>",count($lsArray),"</div>";
	echo "<table id=\"files\" hidden='hidden'><tbody><tr><th>Files:</th></tr></tbody><tbody id='addedFiles'>";

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
	//echo "<button onClick='uploadFiles()'>Assign Attributes</button>";
	
	//form with submit to move on
	echo "<form id='next' action='fileSelect.php' method='post' hidden='hidden'>Project name:<input type='text' name='proName' value='",$proName,"'><br>Username: <input type='text' name='username' value='",$username,"'><br>Password: <input type='text' name='password' value='",$password,"'><br>Directory: <input type='text' name='directory' id='direct' value=\"",$dir,"\"><br>Selected Directory: <input type='text' name='sDir' id='sDir' value=\"\"><br>Files: <input type='text' name='fileSelect' id='fSelect' value=\"",$fSelect,"\"><br><select name='step' id='stepS'><option value='fileselect' selected>File Select</option><option value='attributes'>Define Attributes</option><option value='parameters'>Parameters</option><option value='RNAseq'>RNAseq</option></select><br><input type='submit'></form>";
	
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

	function visitDir(idx)
	{
		const path = document.getElementById(\"path\" + idx).innerHTML;
		document.getElementById(\"direct\").value = path;

		var fString = \"\";
		var rows = document.getElementsByClassName(\"index\");
		for (var i = 0; i < rows.length; i++) {
   			var rowIndex = rows[i].innerHTML;
   			fString += filePaths[rowIndex] + \"?_?\";
		}
		
		document.getElementById('fSelect').value = fString.slice(0, -3);
		document.getElementById(\"stepS\").value = 'fileselect';

		document.getElementById(\"body1\").setAttribute(\"hidden\",\"true\");
		document.getElementById(\"body2\").innerHTML = \"<h3>Opening \" + path + \" directory. This may take several minutes</h3><div class=\\\"loader\\\"></div>\";
		
		document.getElementById(\"next\").submit();
	}
	function parentDir()
	{
		var fString = \"\";
		var rows = document.getElementsByClassName(\"index\");
		for (var i = 0; i < rows.length; i++) {
			var rowIndex = rows[i].innerHTML;
   			fString += filePaths[rowIndex] + \"?_?\";
		}
		
		document.getElementById('fSelect').value = fString.slice(0, -3);

		var dir = document.getElementById('direct').value;
		console.log(dir);
		var idxRM = dir.length;
		for(var i = dir.length - 1; i >= 0; i--)
		{
    			if(dir[i] == String.fromCharCode(47))
    			{
        			idxRM = i;
        			i = -1;
    			}
		}
		var pDir = dir.slice(0,-(dir.length - idxRM));
		console.log(pDir);
		document.getElementById(\"direct\").value = pDir;
		document.getElementById(\"stepS\").value = 'fileselect';
		
		document.getElementById(\"body1\").setAttribute(\"hidden\",\"true\");
		document.getElementById(\"body2\").innerHTML = \"<h3>Opening \" + pDir + \" directory. This may take several minutes</h3><div class=\\\"loader\\\"></div>\";

		document.getElementById(\"next\").submit();
	}
	function selectFile(idx)
	{
		const path = document.getElementById(\"path\" + idx).innerHTML;
		filePaths.push(path);
		const name = document.getElementById(\"name\" + idx).innerHTML;
		fileNames.push(name);
		document.getElementById(\"addedFiles\").innerHTML += \"<tr id='file\" + fileN + \"'><td>\" + name + \"</td><td><button onClick='rmFile(\" + fileN + \")'>remove</button></td><td hidden='hidden' class='index'>\" + fileN + \"</td></tr>\";
		fileN += 1;
	}
	function rmFile(idx)
	{
		const element = document.getElementById(\"file\" + idx);
		element.remove();
	}
	function chooseDirectory()
	{	
		document.getElementById('sDir').value = \"",$dir,"\";
		var iters = document.getElementById('rowN').innerHTML;
		for(var i = 1; i < iters; i++)
		{	
			if(document.getElementById('type' + i).innerHTML != 'directory')
			{
				var nm = document.getElementById('name' + i).innerHTML;
				var suffix = nm.slice(nm.length - 6, nm.length);
				var ssuffix = nm.slice(nm.length - 3, nm.length);
				if(suffix == '.fa.gz' || suffix == '.fq.gz' || ssuffix == '.fq' || ssuffix == '.fa')
				{
					selectFile(i);
				}
			}
		}
		uploadFiles();
		
	}
	function uploadFiles()
	{
		document.getElementById(\"next\").action = \"attributeSelection.php\";
		
		var fString = \"\";
		var rows = document.getElementsByClassName(\"index\");
		for (var i = 0; i < rows.length; i++) {
   			var rowIndex = rows[i].innerHTML;
   			fString += filePaths[rowIndex] + \"?_?\";
		}
		
		document.getElementById('fSelect').value = fString.slice(0, -3);
		document.getElementById(\"stepS\").value = 'attributes';
		document.getElementById(\"next\").submit();
	}
	</script>";
	} 
	elseif ($_POST["step"] == 'attributes')
	{
		echo "<p>Attributes</p>";
	}
	echo "</div><div id='body2'></div>";
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
}
?>
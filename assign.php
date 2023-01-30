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
$aSelect = "";
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
	if ($_POST["step"] == 'assign')
	{
		echo "<title>Sun Lab RNAseq</title>";
		#echo "<body><h3>Create attributes</h3><p>Name of attribute:</p><input type=\"\" id=\"attr\" value=\"\"><button onclick=\"addAttribute()\">Add attribute</button><table id=\"attributes\"><tr><th>Attributes:</th></tr></table>";
		
		#hidden file data storage table
		echo "<table id=\"fileStorage\" hidden = 'hidden'><tbody><tr><th>Files:</th></tr></tbody><tbody id='addedFiles'>";
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

		#hidden attribute data storage table
		echo "<table id=\"attrStorage\" hidden = 'hidden'><tbody><tr><th>Files:</th></tr></tbody><tbody id='addedAttributes'>";
		if(isset($_POST["attrSelect"]))
		{
			$aSelect = $_POST["attrSelect"];
		}
		$aArray = explode("?_?",$aSelect);
		for($i = 0, $size = count($aArray); $i < $size; ++$i)
		{
			echo "<tr id='attr",$i,"'><td id='attr",$i,"name'>",$aArray[$i],"</td><td class='attrIndex'>",$i,"</td></tr>";
		}
		echo "</tbody></table>";
		
		#file assignment to attributes
		echo "<table id=\"fileAssignment\"><tbody><tr><th>File</th><th>Attribute</th></tr></tbody><tbody id='sfile'>";
		echo "</tbody></table>";

		echo "<div hidden='hidden' class='info'>Differential expression analysis will be performed for each comparison.<br>To select which attributes will be compared, change the first two options below. If you would like to create a comparison within a subset of the samples, ( for example: sample A vs. B at time 24 hours in cell line a), select up to two parameters to be used as a subset as the second two inputs. If you do not want to use a subset leave these as N/A.</div>";
		#creating comparisons among attributes
		echo "<table id=\"comparisons\"><tbody><tr><th>Create Comparison: </th><th><select id='c1' name='c1'>";
		#for loop to add each attribute as an option
		for($i = 0, $size = count($aArray); $i < $size; ++$i)
		{
			echo "<option id = 'c1_",$i,"' value='",$i,"'>",$aArray[$i],"</option>";
		}
		echo "</select></th><th>vs.</th><th><select id='c2' name='c2'>";
		for($i = 0, $size = count($aArray); $i < $size; ++$i)
		{
			echo "<option id = 'c2_",$i,"' value='",$i,"'>",$aArray[$i],"</option>";
		}
		echo "</select></th><th>within</th><th><select id='c3' name='c3'>";
		echo "<option id = 'c3_-1' value='-1'>N/A - no intersection</option>";
		for($i = 0, $size = count($aArray); $i < $size; ++$i)
		{
			echo "<option id = 'c3_",$i,"' value='",$i,"'>",$aArray[$i],"</option>";
		}
		echo "</select></th><th>within</th><th><select id='c4' name='c4'>";
		echo "<option id = 'c4_-1' value='-1'>N/A - no intersection</option>";
		for($i = 0, $size = count($aArray); $i < $size; ++$i)
		{
			echo "<option id = 'c4_",$i,"' value='",$i,"'>",$aArray[$i],"</option>";
		}
		echo "</select></th><th><button onClick='compare()'>Add Comparison</button></th></tr></tbody><tbody id='cStorage'></tbody></table>";
			
		#submitting
		echo "<button onClick='submitAssignment()'>Submit Assignment</button>";
		//form with submit to move on
		echo "<form id='next' action='parameters.php' method='post' hidden='hidden'>Project name:<input type='text' name='proName' value='",$proName,"'><br>Username: <input type='text' name='username' value='",$username,"'><br>Password: <input type='text' name='password' value='",$password,"'><br>Directory: <input type='text' name='directory' id='direct' value=\"",$dir,"\"><br>Selected Directory: <input type='text' name='sDir' id='sDir' value=\"",$sDir,"\"><br>Files: <input type='text' name='fileSelect' id='fSelect' value=\"",$fSelect,"\"><br>Attributes: <input type='text' name='attrSelect' id='aSelect' value=\"",$aSelect,"\"><br>Assignment: <input type='text' name='assignment' id='assignment' value=''><br>Comparisons: <input type='text' name='comparison' id='compStore' value=''><br><select name='step' id='stepS'><option value='fileselect' selected>File Select</option><option value='attributes'>Define Attributes</option><option value='assign'>Assign</option><option value='parameters'>Parameters</option><option value='RNAseq'>RNAseq</option></select><br><input type='submit'></form>";
		//Add script
		echo "<script>
		var table1 = document.getElementById('fileStorage');
		var fileNames = [];
		var filePaths = [];
		var fileN = 0;
		for (var i = 0; i < table1.tBodies[1].rows.length; i++) {
			const path = document.getElementById(\"file\" + fileN + \"path\").innerHTML;
			filePaths.push(path);
			var idxRM = path.length;
			for(var z = path.length - 1; z >= 0; z--)
			{
    				if(path[z] == String.fromCharCode(47))
    				{
        				idxRM = z;
        				z = -1;
    				}
			}
			var name = path.slice(idxRM + 2,path.length - 1);
			fileNames.push(name);
			
			
			var table = document.getElementById(\"sfile\");
			var row = \"<tr id='file\" + fileN + \"s'><td>\" + name + \"</td><td id='nAttr\" + fileN + \"' hidden='hidden'>0</td><td><button onClick='addAttr(\" + fileN + \")' id='addAttr\" + fileN + \"'>Add attribute</button></td></tr>\";

			//<select name='f\" + fileN + \"attribute' id='f\" + fileN + \"attribute' value='-None Selected-'>\";		
			//var table2 = document.getElementById('addedAttributes');
			//for (var j = 0; j < table2.rows.length; j++)
			//{
			//	const value = document.getElementById(\"attr\" + j + \"name\").innerHTML;
			//	row += \"<option value=\" + j + \">\" + value + \"</value>\";
			//	
			//}
			//row += \"</select></td></tr>\";
			table.innerHTML += row;
			fileN += 1;
        	}
		var nComps = 0;
		function addAttr(idx)
		{
			var row = document.getElementById('file' + idx + 's');
			var buttn = document.getElementById('addAttr' + idx);
			var numAttrs = document.getElementById('nAttr' + idx).innerHTML;
			var slct = \"<td><select name='f\" + idx + \"attribute\" + numAttrs + \"' id='f\" + idx + \"attribute\" + numAttrs + \"' onchange='setAttr(\" + idx + \",\" + numAttrs + \")'>\";		
			var table2 = document.getElementById('addedAttributes');
			for (var j = 0; j < table2.rows.length; j++)
			{
				const value = document.getElementById(\"attr\" + j + \"name\").innerHTML;
				slct += \"<option id='opt\" + idx + \"_\" + numAttrs + \"_\" + j + \"' value=\" + j + \">\" + value + \"</value>\";
				
			}
			slct += \"</select></td>\";
			document.getElementById('nAttr' + idx).innerHTML = parseInt(numAttrs) + 1;
			row.innerHTML += slct;
			
			for(var j = 0; j < numAttrs - 1; j++)
			{
				var val = document.getElementById('f' + idx + 'attribute' + j).value;
				document.getElementById('f' + idx + 'attribute' + j).setAttribute('value',val);
				document.getElementById('f' + idx + 'attribute' + j).selectedIndex = parseInt(val);

				document.getElementById('opt' + idx + '_' + j + '_' + val).setAttribute('selected',true);	
			}
		}
		function setAttr(i,j)
		{
			var val = document.getElementById('f' + i + 'attribute' + j).value;
			//console.log(val);
			document.getElementById('f' + i + 'attribute' + j).setAttribute('value',val);
			document.getElementById('f' + i + 'attribute' + j).selectedIndex = val;
			document.getElementById('opt' + i + '_' + j + '_' + val).setAttribute('selected',true);
			console.log('opt' + i + '_' + j + '_' + val);
		}
		function compare()
		{
			nComps += 1;
			document.getElementById('cStorage').innerHTML += \"<tr id='comparison\" + nComps + \"'><td id='\" + nComps + \"_c1' value='\" + document.getElementById('c1').value + \"'>\" + document.getElementById('c1_' + document.getElementById('c1').value).innerHTML + \"</td><td id='\" + nComps + \"_c1_val' hidden='hidden'>\" + document.getElementById('c1').value + \"</td><td>vs.</td><td id='\" + nComps + \"_c2' value='\" + document.getElementById('c2').value + \"'>\" + document.getElementById('c2_' + document.getElementById('c2').value).innerHTML + \"</td><td id='\" + nComps + \"_c2_val' hidden='hidden'>\" + document.getElementById('c2').value + \"</td><td>Within</td><td id='\" + nComps + \"_c3' value='\" + document.getElementById('c3').value + \"'>\" + document.getElementById('c3_' + document.getElementById('c3').value).innerHTML + \"</td><td id='\" + nComps + \"_c3_val' hidden='hidden'>\" + document.getElementById('c3').value + \"</td><td>Within</td><td id='\" + nComps + \"_c4' value='\" + document.getElementById('c4').value + \"'>\" + document.getElementById('c4_' + document.getElementById('c4').value).innerHTML + \"</td><td id='\" + nComps + \"_c4_val' hidden='hidden'>\" + document.getElementById('c4').value + \"</td><td><button onClick='removeC(\" + nComps + \")'>Remove</button></td></tr>\";			
		}
		function removeC(idx)
		{
			document.getElementById('comparison' + idx).remove();
		}

		function submitAssignment(){
			//loop all files, get the name of their attribute, store this and place it in some kind of string, maybe just a string with attribute names separated by ?_? that matches to the file name.
			var table1 = document.getElementById(\"fileAssignment\")
			var attrString = \"\";
			for(var i = 0; i < filePaths.length; i++)
			{
				var numAttrs = document.getElementById('nAttr' + i).innerHTML;
				for(var j = 0; j < numAttrs; j++)
				{
					var attr = document.getElementById(\"f\" + i + \"attribute\" + j).value;
					attrString += attr + \"_\";
				}
				attrString = attrString.slice(0,-1);	
				attrString += \"?_?\";
			}
			document.getElementById(\"assignment\").value = attrString.slice(0,-3);

			var compString = \"\";
			for(var i = 1; i < document.getElementById(\"cStorage\").rows.length + 1; i++)
			{
				compString += document.getElementById(i + '_c1_val').innerHTML + '_' + document.getElementById(i + '_c2_val').innerHTML + '_' + document.getElementById(i + '_c3_val').innerHTML + '_' + document.getElementById(i + '_c4_val').innerHTML + '?_?';
			}
			document.getElementById(\"compStore\").value = compString.slice(0,-3);

			document.getElementById(\"next\").action = \"parameters.php\";
			document.getElementById(\"next\").submit();
		}
		</script>";

	} 
	

}
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
</style>";
?>
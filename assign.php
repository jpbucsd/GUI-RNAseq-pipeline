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
		echo "<table id=\"comparisons\"><tbody><tr><th hidden='hidden' id='cSequence'>12</th><th>Create Comparison: </th><th>name: <input type ='text' name='c1name'></th><th><select id='c1' name='c1'>";
		#for loop to add each attribute as an option
		for($i = 0, $size = count($aArray); $i < $size; ++$i)
		{
			echo "<option id = 'c_",$i,"' value='",$i,"'>",$aArray[$i],"</option>";
		}
		echo "</select></th><th>vs.</th><th>name: <input type ='text' name='c2name'></th><th><select id='c2' name='c2'>";
		for($i = 0, $size = count($aArray); $i < $size; ++$i)
		{
			echo "<option id = 'c_",$i,"' value='",$i,"'>",$aArray[$i],"</option>";
		}
		#adding the button to create intersections and what not
		echo "</select></th><th id='insertRegion'></th><th><button onClick='addCondition()'>Add Condition: </button></th><th><select id = 'cCondition' name='cCondition'>";
		echo "<option id = 'cN' value='cN' selected>Choose Condition</option>";
		echo "<option id = 'cA' value='cA'>Or - Comparison 1</option>";
		echo "<option id = 'cB' value='cB'>Or - Comparison 2</option>";
		echo "<option id = 'cC' value='cC'>And - Comparison 1</option>";
		echo "<option id = 'cD' value='cD'>And - Comparison 2</option>";

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

			attrs = document.('cSequence').innerHTML;
			storage = \"<tr id='comparison\" + nComps + \"'><td id='sequence\" + nComps + \"' hidden='hidden'>\" + attrs + \"</td><td id='n\" + nComps + \"name1' hidden='hidden'>\" + document.getElementById('c1name').value + \"</td><td id='n\" + nComps + \"name2' hidden='hidden'>\" + document.getElementById('c2name').value + \"</td>\";
			for (let i = 0; i < attrs.length; i++) {
				switch(attrs[i].value) {
					case \"1\":
    						break;
					case \"2\":
						storage += \"<td> vs. </td>\";
    						break;
					case \"A\":
						storage += \"<td>, parameter 1 union: </td>\";
    						break;
					case \"B\":
						storage += \"<td>, parameter 2 union: </td>\";
    						break;
					case \"C\":
						storage += \"<td>, parameter 1 intersection: </td>\";
    						break;
					case \"D\":
						storage += \"<td>, parameter 2 intersection: </td>\";
    						break;
  					default:
						break;
				}
				j = i + 1;
				id = 'c' + j;
				console.log(id);
				storage += \"<td id='\" + nComps + \"_c\" + j + \"' value='\" + document.getElementById(id).value + \"'>\" + document.getElementById('c_' + document.getElementById(id).value).innerHTML + \"</td><td id='\" + nComps + \"_\" + id + \"_val' hidden='hidden'>\" + document.getElementById(id).value + \"</td>\";
			}
			//TODO, this needs to be fixed based on the specific row. suggestion: read the csequence, and add terms based on that in a for loop.
			//purpuse of union with c1, this means c1 can either be from the orignal sample or this one.
			//purpose of interesection: all variables must be within this that are being compared
			//purpose of union: all variables must be within this or any other union term that are being compared. same as intersection for one term]

			//we need to remove the extra comparison parameters
			const extras = document.getElementsByClassName('extras');
    			while(extras.length > 0){
        			extras[0].parentNode.removeChild(extras[0]);
    			}
			document.getElementById('cSequence').innerHTML = '12';
			storage += \"<td><button onClick='removeC(\" + nComps + \")'>Remove</button></td></tr>\";

			
			document.getElementById('cStorage').innerHTML += storage;
		}
		function addCondition()
		{
			var len = document.getElementById('cSequence').innerHTML.length + 1;
			selectList = \"\";
			console.log(document.getElementById('cSequence').innerHTML);
			switch(document.getElementById('cCondition').value) {
				case 'cN':
    					break;
				case 'cA':
   	 				selectList += \"<a class='extras'> union with attribute 1: </a><select class='extras' id='c\" + len + \"' name='c\" + len + \"'>\";
					selectList += document.getElementById('c1').innerHTML;//adds all the options
					selectList += \"</select>\";
					document.getElementById('cSequence').innerHTML += \"A\";
    					break;
				case 'cB':
					selectList += \"<a class='extras'> union with attribute 2: </a><select class='extras' id='c\" + len + \"' name='c\" + len + \"'>\";
					selectList += document.getElementById('c1').innerHTML;//adds all the options
					selectList += \"</select>\";
					document.getElementById('cSequence').innerHTML += \"B\";
    					break;
				case 'cC':
					selectList += \"<a class='extras'> intersection with attribute 1: </a><select class='extras' id='c\" + len + \"' name='c\" + len + \"'>\";
					selectList += document.getElementById('c1').innerHTML;//adds all the options
					selectList += \"</select>\";
					document.getElementById('cSequence').innerHTML += \"C\";
    					break;
				case 'cD':
					selectList += \"<a class = 'extras'> intersection with attribute 1: </a><select class='extras' id='c\" + len + \"' name='c\" + len + \"'>\";
					selectList += document.getElementById('c1').innerHTML;//adds all the options
					selectList += \"</select>\";
					document.getElementById('cSequence').innerHTML += \"D\";
    					break;
  				default:
					break;
			}
			console.log(selectList);
			document.getElementById('insertRegion').innerHTML += selectList;

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
				compAttrs = document.getElementById('sequence' + i).innerHTML;
				names1 = document.getElementById('n' + i + 'name1').innerHTML;
				names2 = document.getElementById('n' + i + 'name2').innerHTML;
				names1 = names1.replace('_','-');
				names1 = names1.replace(' ','-');
				names2 = names2.replace('_','-');
				names2 = names2.replace(' ','-');
				compString += compAttrs + '_' + names1 + '_' + names2 + '_';
				for (let j = 1; j < compAttrs.length + 1; j++) {
					compString += document.getElementById(i + '_c' + j + '_val').innerHTML + '_';
				}
				compString = compString.slice(0,-1)
				compString += '?_?';
			}
			document.getElementById(\"compStore\").value = compString.slice(0,-3);
			console.log(\"string:\");
			console.log(compString.slice(0,-3));			
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
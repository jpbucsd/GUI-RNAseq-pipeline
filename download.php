<?php
#dependencies to communicate with command line
include 'vendor/autoload.php';
use phpseclib3\Net\SSH2;

#use 0 and 1 rather than true and false which are evaluating to strings
$password = "";
$username = "";

if(isset($_POST["username"]) && isset($_POST["password"])){
	$username = $_POST["username"];
	$password = $_POST["password"];
}


#get .slr project file

echo "<title>Sun Lab RNAseq</title>";
echo "<input type='file' id='file-select' accept='.slr'>";
echo "<pre id='data'></pre>";
echo "<pre id='output'></pre>";
echo "<script>
	const fileSelect = document.getElementById('file-select');
	fileSelect.addEventListener('change', (event) => {
	var fr=new FileReader();
	fr.onload=function(){
		//all code after file is selected goes here
		var contents=fr.result;
		var sContents = '';
		var lines = Array();
		var tempLine = '';
		for (var i = 0; i < contents.length; i++) {
			if(contents.charAt(i) == '\\n')
			{
					sContents += '\\\\n';
					console.log(tempLine);
					lines.push(tempLine);
					tempLine = '';
			}else{
				tempLine += contents.charAt(i);
				if(contents.charAt(i) == '\\t')
				{
					sContents += '\\\\t';
				}
				else if(contents.charAt(i) == \"'\")
				{
					sContents += \"\\\'\";
				}else{
					sContents += contents.charAt(i);
				}
			}
			
		}
		//all necessary variables
		var proName=event.target.files[0].name;
		var AQ=false;//default value
		var PCA=false;
		var DESEQ=false;
		var Quality=false;
		var Trimming=false;
		var sDir='';
		var comparison='';
		var readNames='';
		var assignment='';
		var attributes='';
		
		//loop each line to get info
		for(var i = 0; i < lines.length;i++)
		{
			
			var tempElement='';
			var elements = Array();
			for (var j = 0; j < lines[i].length; j++) {
				if(lines[i].charAt(j) == '\\t')
				{
					elements.push(tempElement);
					tempElement = '';
				}else if(lines[i].charAt(j) == \"'\")
				{
					tempElement += \"\\\'\";
					console.log('here ' + lines[i].charAt(j) + \"\\\'\");
				}else{
					tempElement += lines[i].charAt(j);
				}
			}
			elements.push(tempElement);
			if(elements[0]=='Directory:')
			{
				console.log(elements[1]);
				for(k = 0; k < elements[1].length;k++)
				{
					console.log(elements[1][k]);
					if(elements[1][k] == \"'\")
					{
						sDir += \"\\'\"
					}else{
						sDir += elements[1][k];
					}
				}
			}else if(elements[0]=='Computations:')
			{
				for(var k = 1; k < elements.length;k++)
				{
					if(elements[k] == 'AQ')
					{
						AQ=true;
					}else if(elements[k] == 'PCA')
					{
						PCA=true;
					}else if(elements[k] == 'DESEQ')
					{
						DESEQ=true;
					}
					else if(elements[k] == 'QUALITY')
					{
						Quality=true;
					}
					else if(elements[k] == 'TRIM')
					{
						Trimming=true;
					}
				}
			}else if(elements[0]=='Read:')
			{
				readNames += elements[1] + '?_?';
				for(var k = 2; k < elements.length;k++)
				{
					assignment += elements[k] + '_'
				}
				assignment = assignment.slice(0,-1) + '?_?';
			}else if(elements[0]=='Attributes:')
			{
				for(var k = 1; k < elements.length;k++)
				{
					attributes += elements[k] + '?_?';
				}
				attributes = attributes.slice(0,-3) + '?_?';
			}else if(elements[0]=='Comparison:')
			{
				comparison += elements[1] + '_' + elements[2] + '?_?';
			}	
			//document.getElementById('output').innerHTML += lines[i] + '<br>';
		}
		assignment = assignment.slice(0,-3);
		readNames = readNames.slice(0,-3);
		comparison = comparison.slice(0,-3);

		//populate data object with html form
		
		document.getElementById('data').innerHTML += \"<form id='next' action='downloadFiles.php' method='post'></form>\";
		doc = document.getElementById('next');
		doc.innerHTML += \"<input type='text' name='proName' value='\" + proName.slice(0,-4) + \"' hidden = 'hidden'><input type='text' name='username' value='",$username,"' hidden = 'hidden'><input type='text' name='password' value='",$password,"' hidden = 'hidden'>\"
		doc.innerHTML += \"<input type='text' name='sDir' id='sDir' value=\\\"\" + sDir + \"\\\" hidden = 'hidden'>\";
		doc.innerHTML += \"<input name='attributes' type='text' value = '\" + attributes + \"' hidden = 'hidden'>\";
		doc.innerHTML += \"<input name='assignment' type='text' value = '\" + assignment + \"' hidden = 'hidden'>\";
		doc.innerHTML += \"<input name='reads' type='text' value ='\" + readNames + \"' hidden = 'hidden'>\";
		doc.innerHTML += \"<input name='comparison' type='text' value ='\" + comparison + \"' hidden = 'hidden'>\";
		doc.innerHTML += \"<input type='text' name='slr' value =\\\"\" + sContents + \"\\\" hidden = 'hidden'>\";
		doc.innerHTML += \"<br>Retrieve & Download PCA results<input type='checkbox' name='PCA' value='Yes' id='PCA'>\";
		doc.innerHTML += \"<br>Retrieve & Download Volcano Plots<input type='checkbox' name='volcano' value='Yes' id='volcano'>\";
		doc.innerHTML += \"<br>Retrieve & Download Heatmaps<input type='checkbox' name='heat' value='Yes' id='heat'>\";
		doc.innerHTML += \"<br>Retrieve & Download Lists of Differentially Expressed Genes<input type='checkbox' name='diff' value='Yes' id='diff'>\";
		doc.innerHTML += \"<br>Retrieve & Download Quality reports for raw & cleaned reads<input type='checkbox' name='QUALITY' value='Yes' id='QUALITY'>\";
		doc.innerHTML += \"<br><button type='button' onclick='document.getElementById(\\\"body2\\\").innerHTML = \\\"<h3>Downloading Files from the TSCC. This may take some time.</h3><div class=\\\\\\\"loader\\\\\\\"></div>\\\";document.getElementById(\\\"next\\\").submit();'>Download</button></form></div><div id='body2' ></div><style>.info{background: #91A5B4;border-radius: 25px;		width:600px;		padding: 25px;		margin-top: 5px;  		margin-bottom: 5px;  		margin-right: 5px; 	 	margin-left: 5px;		color: white;	}	.loader {  		border: 16px solid #f3f3f3; /* Light grey */  		border-top: 16px solid #3498db; /* Blue */  		border-radius: 50%;  		width: 60px;  		height: 60px;  		animation: spin 2s linear infinite;	}	@keyframes spin {  		0% { transform: rotate(0deg); }  		100% { transform: rotate(360deg); }	}	</style>\";
	}
	fr.readAsText(event.target.files[0]);
	});
</script>";
?>
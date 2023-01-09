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
		var sDir='';
		var padJ='';//usefull info, give oportunity to change
		var logT='';
		var logA='';
		var Quality=false;
		var Trimming=false;
		var AQ=false;//default value
		var PCA=false;
		var DESEQ=false;
		var uEmail='';
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
			console.log(i + \": \" + elements[0]);
			if(elements[0]=='Pvalues:')
			{
				padJ = elements[1];
				logT = elements[2];
				logA = elements[3];
			}else if(elements[0]=='Directory:')
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
			}	
			//document.getElementById('output').innerHTML += lines[i] + '<br>';
		}

		//populate data object with html form
		
		document.getElementById('data').innerHTML += \"<form id='next' action='resumeRNAseq.php' method='post'></form>\";
		doc = document.getElementById('next');
		doc.innerHTML += \"Project name:<input type='text' name='proName' value='\" + proName.slice(0,-4) + \"'><input type='text' name='username' value='",$username,"' hidden = 'hidden'><input type='text' name='password' value='",$password,"' hidden = 'hidden'>\";
		doc.innerHTML += \"<br>Selected Directory: <input type='text' name='sDir' id='sDir' value=\\\"\" + sDir + \"\\\">\";
		doc.innerHTML += \"<br>PadJ: <input name='padj' type='number' value = '\" + padJ + \"'>\";
		doc.innerHTML += \"<br>Log10 P value: <input name='log10' type='number' value ='\" + logT + \"'>\";
		doc.innerHTML += \"<br>Log10 Annotation value: <input name='log10a' type='number' value ='\" + logA + \"'>\";
		//todo
		if(!Quality){
			doc.innerHTML += \"<br>Perform a Quality Assessment<input type='checkbox' name='Quality' value='Yes' id='Quality' checked>\"
		}
		if(!Trimming){
			doc.innerHTML += \"<br>Perform Trimming<input type='checkbox' name='Trimming' value='Yes' id='Trimming' checked>\";
		}
		if(!AQ){
			doc.innerHTML += \"<br>Compute Alignment and Quantification<input type='checkbox' name='AQ' value='Yes' id='AQ' checked>\";
		}

		doc.innerHTML += \"<br>Compute PCA<input type='checkbox' name='PCA' value='Yes' id='pca'><p>\";
		doc.innerHTML += \"<br>Compute Differential Expression Analysis<input type='checkbox' name='DESEQ' value='Yes' id='deseq'>\";
		doc.innerHTML += \"<br>Annotate Volcano Plots with a Gene List<input type='checkbox' name='GL' value='No' id='GL'>\";
		doc.innerHTML += \"<br>Gene List<input type='text' name='GeneList' id='GeneList'>\";
		
		doc.innerHTML += \"<br>Use specific genes for heatmaps<input type='checkbox' name='HGL' value='No' id='HGL'>\"; 
		doc.innerHTML += \"<br>Heatmap Gene List<input type='text' name='HGeneList' id='HGeneList'>\"; 


		doc.innerHTML += \"<br>User Email<input type='text' name='EMAIL' value = '\" + uEmail + \"' id = 'email'>\";

		doc.innerHTML += \"<br><input type='text' name='slr' value =\\\"\" + sContents + \"\\\">\";
		//todo
		doc.innerHTML += \"<br><button type='button' onclick='document.getElementById(\\\"body2\\\").innerHTML = \\\"<h3>Sending process to the TSCC. This may take some time.</h3><div class=\\\\\\\"loader\\\\\\\"></div>\\\";document.getElementById(\\\"next\\\").submit();'>Run Process</button></form></div><div id='body2' ></div><style>.info{background: #91A5B4;border-radius: 25px;		width:600px;		padding: 25px;		margin-top: 5px;  		margin-bottom: 5px;  		margin-right: 5px; 	 	margin-left: 5px;		color: white;	}	.loader {  		border: 16px solid #f3f3f3; /* Light grey */  		border-top: 16px solid #3498db; /* Blue */  		border-radius: 50%;  		width: 60px;  		height: 60px;  		animation: spin 2s linear infinite;	}	@keyframes spin {  		0% { transform: rotate(0deg); }  		100% { transform: rotate(360deg); }	}	</style>\";		
		//idea of how to make this functional: another input form that stores the slr data, and then sends back to the resume page, as well as buttons for collect results if that makes sense, all going back to resume page with same results
	}
	fr.readAsText(event.target.files[0]);
	});
</script>";
?>
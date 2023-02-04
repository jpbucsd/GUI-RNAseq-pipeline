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
$assignment = "";
$comparison = "";
$paired = FALSE;
$p1 = "";
$p2 = "";
$readLength = 150;
$threads = 16;
$padj = 0.5;
$log10 = 5;
$log10a = 30;
$AQ = TRUE;
$PCA = FALSE;
$DESEQ = FALSE;
$Quality = TRUE;
$Trimming = TRUE;
$sDir = "";
$proName = "";
$email="";
$hours=48;
$minutes=0;
$seconds=0;
$useGenes=0;
$HuseGenes=0;
$submit = TRUE;
$geneList="";

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


if(isset($_POST["fileSelect"]))
{
	$fSelect = $_POST["fileSelect"];
}

if(isset($_POST["attrSelect"]))
{
	$aSelect = $_POST["attrSelect"];
}

if(isset($_POST["assignment"]))
{
	$assignment = $_POST["assignment"];
}

if(isset($_POST["comparison"]))
{
	$comparison = $_POST["comparison"];
}

if(isset($_POST["paired"]))
{
	$paired = ($_POST["paired"] == 'paired');
}
if(isset($_POST["p1suffix"]))
{
	$p1 = $_POST["p1suffix"];
}
if(isset($_POST["p2suffix"]))
{
	$p2 = $_POST["p2suffix"];
}

if(isset($_POST["readLength"]))
{
	$readLength = $_POST["readLength"];
}

if(isset($_POST["threads"]))
{
	$threads = $_POST["threads"];
}
if(isset($_POST["hours"]))
{
	$hours = $_POST["hours"];
}
if(isset($_POST["minutes"]))
{
	$minutes = $_POST["minutes"];
}
if(isset($_POST["seconds"]))
{
	$seconds = $_POST["seconds"];
}

if(isset($_POST["padj"]))
{
	$padj = $_POST["padj"];
}

if(isset($_POST["log10"]))
{
	$log10 = $_POST["log10"];
}

if(isset($_POST["log10a"]))
{
	$log10a = $_POST["log10a"];
}
$Quality = isset($_POST["Quality"]);

$Trimming = isset($_POST["Trimming"]);

$AQ = isset($_POST["AQ"]);

$PCA = isset($_POST["PCA"]);

$DESEQ = isset($_POST["DESEQ"]);

$useGenes = isset($_POST["GL"]);

$HuseGenes = isset($_POST["HGL"]);

if(isset($_POST["GeneList"]))
{
	$geneList = $_POST["GeneList"];
}

if(isset($_POST["HGeneList"]))
{
	$HgeneList = $_POST["HGeneList"];
}

if(isset($_POST["EMAIL"]))
{
	$email = $_POST["EMAIL"];
}

$submit = isset($_POST["sub"]);

#create the slr

$slr = array();


	#add attributes
	$attrStrings = "Attributes:\\t";
	$aArray = explode("?_?",$aSelect);
	for($i = 0, $size = count($aArray); $i < $size; ++$i)
	{
		$attrStrings = $attrStrings.$aArray[$i]."\\t";
	}
	$slr[] = substr($attrStrings,0,-2);

	#add reads to slr
	$fsArray = explode("?_?", $fSelect);
	$asArray = explode("?_?", $assignment);

	for($i = 0, $size = count($fsArray); $i < $size; ++$i) {
			$chars = str_split($fsArray[$i]);
			$lastSlash = 0;
			$firstDot = 0;
			$indexI = 0;
			foreach ($chars as $char) {
	    			if($char == '/')
    				{
    					$lastSlash = $indexI;
   				}
    				elseif ($char == '.' && $firstDot == 0) 
				{
    					$firstDot = $indexI;
  	  			}
    				$indexI = $indexI + 1;
			}				
			$fname = substr($fsArray[$i],$lastSlash + 1,$firstDot - $lastSlash - 1);
			if($paired)
			{
				if(substr($fname,-strlen($p1)) == $p1)					
				{
					#only add the name once by selecting for the first pair substring only, we start indexing at 1 to get rid of the '
					$asAs = explode("_", $asArray[$i]);
					$strline = "Read:\\t".substr($fname,1,-strlen($p1));
					for($j = 0, $size2 = count($asAs); $j < $size2; ++$j)
					{
						$strline = $strline."\\t~".$asAs[$j];
					}
					$slr[] = $strline;
				}
			}else{
				$asAs = explode("_", $asArray[$i]);
					$strline = "Read:\\t".substr($fname,1,-strlen($p1));
					for($j = 0, $size2 = count($asAs); $j < $size2; ++$j)
					{
						$strline = $strline."\\t~".$asAs[$j];
					}
				$slr[] = $strline;
			}	
	}

	#add comparisons to slr
	$cArray = explode("?_?",$comparison);
	for($i = 0, $size = count($cArray); $i < $size; ++$i)
	{
		$strline = "Comparison:";
		$ccArray = explode("_",$cArray[$i] ?? '');
		for($j = 0, $size = count($ccArray); $j < $size; ++$j)
		{
			$strline = $strline."\\t".$ccArray[$j];
		}
		$slr[] = $strline;
	}

	#add pairing info to slr
	if($paired)
	{
		$slr[] = "Paired:\\t".$p1."\\t".$p2;
	}
	
	#add comleted computations
	$tempLine = "Computations:";
	if($AQ)
	{
		$tempLine = $tempLine."\\tAQ";
	}
	if($PCA)
	{
		$tempLine = $tempLine."\\tPCA";
	}
	if($DESEQ)
	{
		$tempLine = $tempLine."\\tDESEQ";
	}
	if($Quality)
	{
		$tempLine = $tempLine."\\tQUALITY";
	}
	if($Trimming)
	{
		$tempLine = $tempLine."\\tTRIM";
	}
	$slr[] = $tempLine;
	
	#add padj information
	$slr[] = "Pvalues:\\t".$padj."\\t".$log10."\\t".$log10a;
	
	#add readlength
	$slr[] = "Readlength:\\t".$readLength;
	
	#add directory
	$slr[] = "Directory:\\t".$sDir;
	
	#print slr file
	/*
	foreach ($slr as $line) {
  		echo "$line<br>";
	}
	*/
	#create RNAseq command
	/*hypothetical command
	Align: (uses -a) PCA: (-PCA) deseq: (-d) paired: (-p) (for now force paired)  read length: (-r)
	needs to add slr with project name
	bash RNAseq.sh -f $sDir -o RNAseqOut -a -PCA -d -p _1 _2 -r 150 -PADJ $padj -log10 $log10 -A $log10a -s $proName.slr
	*/
	$RNAseq = "bash RNAseq.sh -f ".$sDir." -o RNAseqOut ";
	if($AQ)
	{
		$RNAseq = $RNAseq."-a -r ".$readLength." -t ".$threads." ";
	}
	if($paired || TRUE)//for now since unpaired doesn't work this is how it is
	{
		$RNAseq = $RNAseq."-p ".$p1." ".$p2." ";
	}
	if($PCA)
	{
		$RNAseq = $RNAseq."-PCA ";
	}
	if($DESEQ)
	{
		$RNAseq = $RNAseq."-d -PADJ ".$padj." -log10 ".$log10." -A ".$log10a." ";
	}
	if($Quality)
	{
		$RNAseq = $RNAseq."-Q ";
	}
	if($Trimming)
	{
		$RNAseq = $RNAseq."-T ";
	}
	$cName="";
	$nchars = str_split($proName);

	foreach ($nchars as $char) {
		$nc = ord($char);
		if(($nc >= 48 && $nc <= 57) || ($nc >= 65 && $nc <= 90) || ($nc >= 97 && $nc <= 122))
		{
			$cName = $cName.$char;
		}else
		{
			$cName = $cName."_";
		}
	}
	$fPrefix = $cName.date("Y-m-d-H-i-s");
	$RNAseq = $RNAseq."-s ".$fPrefix.".slr";
	$geneListFname = $fPrefix."_gene_list.txt";
	if($useGenes)
	{
		$RNAseq = $RNAseq." -G ".$geneListFname;
	}
	$HgeneListFname = $fPrefix."_heatmap_gene_list.txt";
	if($HuseGenes)
	{
		$RNAseq = $RNAseq." -H ".$HgeneListFname;
	}
	#echo "<br>",$RNAseq;

	$minu="";
	$secu="";
	if($minutes < 10)
	{
		$minu = "0".strval($minutes);
	}else{
		$minu = strval($minutes);
	}
	if($seconds < 10)
	{
		$secu = "0".strval($seconds);
	}else{
		$secu = strval($seconds);
	}


	#log in to server and conduct RNAseq
	ini_set('max_execution_time', 3000);
	$ssh = new SSH2('tscc-login.sdsc.edu');
	if (!$ssh->login($_POST["username"], $_POST["password"])) {
    		throw new \Exception('Login failed');
	}

	#create qsub
	$qsub = array();
	#$qsub[] = "#!/bin/bash";//cant add this way doesnt work
	$qsub[] = "#PBS -l nodes=1:ppn=".$threads;
	$qsub[] = "#PBS -l walltime=".$hours.":".$minu.":".$secu;#future setting to add
	$qsub[] = "#PBS -N RNAseq".$fPrefix;
	$qsub[] = "#PBS -o RNAseq".$fPrefix."out.txt";
	$qsub[] = "#PBS -e RNAseq".$fPrefix."err.txt";
	$qsub[] = "#PBS -q hotel";
	$qsub[] = "#PBS -A bryansun-group";
	$qsub[] = "#PBS -m e";
	$qsub[] = "#PBS -M ".$email;
	$qsub[] = "cd /projects/ps-bryansunlab/labTools/RNAseq";
	$qsub[] = "echo \\\"beginning\\\" >> ".$fPrefix."output.txt";
	$qsub[] = $RNAseq." >> ".$fPrefix."output.txt";

	#print qsub file
	$qsubFname = $fPrefix."qsub.submit";
	/*commented out segment displays contents of the qsub submit on screen
	echo "<br>";
	foreach ($qsub as $line) {
  		echo "$line<br>";
	}
	*/
	#create qsub file on server
	$cdCMD = "cd /projects/ps-bryansunlab/labTools/RNAseq\n";
	$ssh->read('');
	$ssh->write($cdCMD);
	$ssh->read('');

	#add bin bash line first
	$ssh->write("echo -e '#!/bin/bash' >> ".$qsubFname."\n");
	$ssh->read('');

	foreach ($qsub as $line) {
		$qsubCMD = "echo -e \"".$line."\" >> ".$qsubFname."\n";
		$ssh->write($qsubCMD);
		$ssh->read('');
	}

	#write the gene list to the server
	if($useGenes)
	{
		
		$genesL = explode(",",$geneList);
		for($i = 0, $size = count($genesL); $i < $size; ++$i)
		{
			$glCMD = "echo -e \"".$genesL[$i]."\" >> ".$geneListFname."\n";
			$ssh->write($glCMD);
			$ssh->read('');
		}
	}

	#write the gene list to the server
	if($HuseGenes)
	{
		
		$HgenesL = explode(",",$HgeneList);
		for($i = 0, $size = count($HgenesL); $i < $size; ++$i)
		{
			$hglCMD = "echo -e \"".$HgenesL[$i]."\" >> ".$HgeneListFname."\n";
			$ssh->write($hglCMD);
			$ssh->read('');
		}
	}


	#download slr file to server
	$slrFname = $fPrefix.".slr";
	
	#we are already in the correct directory
	foreach ($slr as $line) {
		$slrCMD = "echo -e \"".$line."\" >> ".$slrFname."\n";
		$ssh->write($slrCMD);
		$ssh->read('');
	}

	#submit qsub
	$submitCMD = "qsub ".$qsubFname."\n";
	if($submit)
	{
		echo $submitCMD;
		$ssh->write($submitCMD);
		$ssh->read('');
	}
	

	echo "<title>Sun Lab RNAseq</title>";
	#produce invisible form and check status update button
	echo "<form id='next' action='status.php' method='post'>";
	echo "<input type='text' name='proName' value='",$fPrefix,"' hidden = 'hidden'>";
	echo "<input type='text' name='username' value='",$username,"' hidden = 'hidden'><input type='text' name='password' value='",$password,"' hidden = 'hidden'>";
	echo "<br><button type='button' onclick='
		document.getElementById(\"body2\").innerHTML = \"<h3>Retrieving status from the TSCC. This may take some time.</h3><div class=\\\"loader\\\"></div>\";
		document.getElementById(\"next\").submit();
	'>Check Status</button></form>";
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


	#download slr file to client
	echo "<script>
		var filename=\"",$slrFname,"\";
		var text=\"";
		foreach ($slr as $line) {
			echo $line,"\\n";
		}
		echo "\";
	
  		var element = document.createElement('a');
  		element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
  		element.setAttribute('download', filename);
	
 		element.style.display = 'none';
  		document.body.appendChild(element);
  		element.click();
  		document.body.removeChild(element);
	</script>";
		
	/*This method produces a server side file and then downloads it, javascript was better for the purpose	
	$slrname =  $cName.date("Y-m-d-H-i-s").".slr";
	$slrfile = fopen($slrname, "w") or die("Unable to open file with name ".$slrname);
	foreach ($slr as $line) {
		fwrite($slrfile, $line."\\n");
	}
	fclose($slrfile);

	header('Content-Description: File Transfer');
	header('Content-Disposition: attachment; filename='.basename($slrname));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($slrname));
	header("Content-Type: text/plain");
	readfile($slrname);
	*/

?>

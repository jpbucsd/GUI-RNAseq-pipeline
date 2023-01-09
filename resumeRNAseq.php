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
$padj = 0.5;
$log10 = 5;
$log10a = 30;
$Quality = FALSE;
$Trimming = FALSE;
$AQ = FALSE;
$PCA = FALSE;
$DESEQ = FALSE;
$sDir = "";
$proName = "";
$email="";
#slr="";
$useGenes=0;
$geneList="";
$HgeneList="";
$HuseGenes=0;

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
	$sDir = "";
	for ($i = 0; $i < strlen($_POST["sDir"]); $i++){
    		if($_POST["sDir"][$i] == "\\")
		{
			$i++;
			if($i < strlen($_POST["sDir"]))
			{
				if($_POST["sDir"][$i] == "'")
				{
					$sDir = $sDir."'";
				}else{
					$sDir = $sDir.$_POST["sDir"][$i - 1].$_POST["sDir"][$i];
				}
			}
		}else{
			$sDir = $sDir.$_POST["sDir"][$i];
		}
	}
}
if(isset($_POST["username"]) && isset($_POST["password"])){
	$username = $_POST["username"];
	$password = $_POST["password"];
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

if(isset($_POST["GeneList"]))
{
	$geneList = $_POST["GeneList"];
}

$HuseGenes = isset($_POST["HGL"]);

if(isset($_POST["HGeneList"]))
{
	$HgeneList = $_POST["HGeneList"];
}


if(isset($_POST["EMAIL"]))
{
	$email = $_POST["EMAIL"];
}
if(isset($_POST["slr"]))
{
	$slr = $_POST["slr"];
}
	#create RNAseq command
	/*hypothetical command
	Align: (uses -a) PCA: (-PCA) deseq: (-d) paired: (-p) (for now force paired)  read length: (-r)
	needs to add slr with project name
	bash RNAseq.sh -f $sDir -o RNAseqOut -a -PCA -d -p _1 _2 -r 150 -PADJ $padj -log10 $log10 -A $log10a -s $proName.slr
	*/
	$RNAseq = "bash RNAseq.sh -f ".$sDir." -o RNAseqOut ";
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
	echo "<br>",$RNAseq;


	#log in to server and conduct RNAseq
	ini_set('max_execution_time', 3000);
	$ssh = new SSH2('tscc-login.sdsc.edu');
	if (!$ssh->login($_POST["username"], $_POST["password"])) {
    		throw new \Exception('Login failed');
	}

	#create qsub
	$qsub = array();
	#$qsub[] = "#!/bin/bash";//cant add this way doesnt work
	$qsub[] = "#PBS -l nodes=1:ppn=16";
	$qsub[] = "#PBS -l walltime=48:00:00";#future setting to add
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

	echo "<br>";
	foreach ($qsub as $line) {
  		echo "$line<br>";
	}

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
	$slrA = array();
	$slrL = "";
	for ($i = 0; $i < strlen($slr); $i++){
    		if($slr[$i] == "\\")
		{
			$i++;
			if($i < strlen($slr))
			{
				if($slr[$i] == "n")
				{
					$slrA[] = $slrL;
					$slrL = "";
				}elseif($slr[$i] == "'")
				{
					$slrL = $slrL."'";
				}else{
					$slrL = $slrL.$slr[$i - 1].$slr[$i];
				}
			}
		}else{
			$slrL = $slrL.$slr[$i];
		}
	}
	
	$slrFname = $fPrefix.".slr";
	
	#we are already in the correct directory
	foreach ($slrA as $line) {
		$slrCMD = "echo -e \"".$line."\" >> ".$slrFname."\n";
		$ssh->write($slrCMD);
		$ssh->read('');
	}

	#submit qsub
	#TODO, last step
	$submitCMD = "qsub ".$qsubFname."\n";
	$ssh->write($submitCMD);
	$ssh->read('');
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

		

?>

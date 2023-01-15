<?php
#next steps:
#
#add the attributes page
#
#dependencies to communicate with command line
include 'vendor/autoload.php';
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

#use 0 and 1 rather than true and false which are evaluating to strings
$password = "";
$username = "";
$proName = "";
$sDir = "";
$assignment="";
$attributes="";
$reads="";
$comparison="";
$slr="";
$PCA = FALSE;
$volcano = FALSE;
$heat = FALSE;
$lists = FALSE;
$Qual = FALSE;
$Trim = FALSE;


if(isset($_POST["proName"]))
{
	$proName = $_POST["proName"];
}
if(isset($_POST["sDir"]))
{
	$sDir = $_POST["sDir"];
	$chars = str_split($sDir);
 	$ssDir = "";
	$quote = 0;
	foreach ($chars as $char) {
		if($char != "\\")
		{
    			$ssDir = $ssDir.$char;
		}
		if($char == " ")
		{
			$quote = 1;
		}
	}
	$sDir = $ssDir;
}
if(isset($_POST["username"]) && isset($_POST["password"])){
	$username = $_POST["username"];
	$password = $_POST["password"];
}
if(isset($_POST["assignment"]))
{
	$assignment = $_POST["assignment"];
}
if(isset($_POST["reads"]))
{
	$reads = $_POST["reads"];
}
if(isset($_POST["comparison"]))
{
	$comparison = $_POST["comparison"];
}
if(isset($_POST["attributes"]))
{
	$attributes = $_POST["attributes"];
}
if(isset($_POST["slr"]))
{
	$slr = $_POST["slr"];
}
$PCA = isset($_POST["PCA"]);
$volcano = isset($_POST["volcano"]);
$heat = isset($_POST["heat"]);
$lists = isset($_POST["diff"]);
$Qual = isset($_POST["QUALITY"]);
$Trim = isset($_POST["TRIM"]);

#name of the pca file: sDir/RNAseqOut/figures/pca/PCA.png
#name of volcanos:  sDir/RNAseqOut/results/c1_vs_c2/c1_vs_c2.png
#name of background: sDir/RNAseqOut/results/background.txt
#name of diff expressed: sDir/RNAseqOut/results/c1_vs_c2/upRegulatedGenes.txt
#sDir/RNAseqOut/results/c1_vs_c2/downRegulatedGenes.txt




$fsArray = explode("?_?", $reads);
$asArray = explode("?_?", $assignment);
$cArray = explode("?_?",$comparison);
$atArray = explode("?_?",$attributes);

$sDir2 = "";
foreach (str_split($sDir) as $char) {
    	if($char != "'")
	{
		$sDir2 = $sDir2.$char;
	}
}

echo "<title>Sun Lab RNAseq</title>";
#log in to server for downloads
ini_set('max_execution_time', 3000);
$sftp = new SFTP('tscc-login.sdsc.edu');
if (!$sftp->login($_POST["username"], $_POST["password"])) {
    exit('Login Failed');
}

if($PCA)
{
	$pcaFile = "".$sDir2."/RNAseqOut/figures/pca/PCA.eps"."";
	echo $pcaFile;
	$downloadName = $proName."_PCA.eps";
	$sftp->get($pcaFile, $downloadName);

	echo '<iframe src="individualDownload.php?file='.$downloadName.'"></iframe>';
		
	/*
	header('Content-Description: File Transfer');
	header('Content-Disposition: attachment; filename='.basename($downloadName));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($downloadName));
	header("Content-Type: text/plain");
	ob_clean();
	flush();
	readfile($downloadName);
	unlink($downloadName);
	*/
}
if($heat)
{
	$heatFile = "".$sDir2."/RNAseqOut/heatmap.eps"."";
	echo $heatFile;
	$downloadName = $proName."_heatmap.eps";
	$sftp->get($heatFile, $downloadName);

	echo '<iframe src="individualDownload.php?file='.$downloadName.'"></iframe>';
}

foreach ($cArray as $comp) {
	$coArray = explode("_",$comp);
	$c1 = $coArray[0];
	$c2 = $coArray[1];
	if($volcano)
	{
		$vFile = "".$sDir2."/RNAseqOut/results/".$atArray[$c1]."_vs_".$atArray[$c2]."/".$atArray[$c1]."_vs_".$atArray[$c2].".eps";
		$downloadName = $atArray[$c1]."_vs_".$atArray[$c2].".eps";
		$sftp->get($vFile, $downloadName);

		echo '<iframe src="individualDownload.php?file='.$downloadName.'"></iframe>';
	}
	if($lists)
	{
		$uFile = "".$sDir2."/RNAseqOut/results/".$atArray[$c1]."_vs_".$atArray[$c2]."/upRegulatedGenes.txt";
		$downloadName = $atArray[$c1]."_vs_".$atArray[$c2]."_upRegulatedGenes.txt";
		$sftp->get($uFile, $downloadName);

		echo '<iframe src="individualDownload.php?file='.$downloadName.'"></iframe>';

		$dFile = "".$sDir2."/RNAseqOut/results/".$atArray[$c1]."_vs_".$atArray[$c2]."/downRegulatedGenes.txt";
		$downloadName = $atArray[$c1]."_vs_".$atArray[$c2]."_downRegulatedGenes.txt";
		$sftp->get($dFile, $downloadName);

		echo '<iframe src="individualDownload.php?file='.$downloadName.'"></iframe>';
	}
}
if($lists)
{	
		$bFile = "".$sDir2."/RNAseqOut/results/background.txt";
		$downloadName = $proName."_background.txt";
		$sftp->get($bFile, $downloadName);

		echo '<iframe src="individualDownload.php?file='.$downloadName.'"></iframe>';
}
if($Qual)
{	
		#zip all quality files
		$ssh = new SSH2('tscc-login.sdsc.edu');
		if (!$ssh->login($_POST["username"], $_POST["password"])) {
    			throw new \Exception('Login failed');
		}
		$cdCMD = "cd ".$sDir."\n";

		$ssh->read('');
		$ssh->write($cdCMD);
		$ssh->read('');

		$zipCMD = "zip -r quality.zip quality"."\n";
		$ssh->write($zipCMD);
		$ssh->read('');

		#download zip
		$cFile = "".$sDir2."/quality.zip";
		$downloadName = $proName."_quality_report.zip";
		$sftp->get($cFile, $downloadName);

		echo '<iframe src="individualDownload.php?file='.$downloadName.'"></iframe>';		
}


?>

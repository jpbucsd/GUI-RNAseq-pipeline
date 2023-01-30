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
$sDir = "";
$proName = "";
$email = "";

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
echo "<title>Sun Lab RNAseq</title>";
echo "<div id='body1'><table id=\"fileStorage\" hidden = 'hidden'><tbody><tr><th>Files:</th></tr></tbody><tbody id='addedFiles'>";
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

#hidden attribute assignment data storage table
#echo "<table id=\"attrAssign\" hidden = 'hidden'><tbody><tr><th>Attribute:</th></tr></tbody><tbody id = 'assignedAttributes'>";
if(isset($_POST["assignment"]))
{
	$assignment = $_POST["assignment"];
}

if(isset($_POST["comparison"]))
{
	$comparison = $_POST["comparison"];
}
//form with submit to move on
echo "<form id='next' action='RNAseq.php' method='post'>Project name:<input type='text' name='proName' value='",$proName,"'><input type='text' name='username' value='",$username,"' hidden = 'hidden'><br><input type='text' name='password' value='",$password,"' hidden = 'hidden'><input type='text' name='fileSelect' id='fSelect' value=\"",$fSelect,"\" hidden='hidden'><input type='text' name='attrSelect' id='aSelect' value=\"",$aSelect,"\" hidden='hidden'><input type='text' name='assignment' id='assignment' value=\"",$assignment,"\" hidden='hidden'><input type='text' name='sDir' id='sDir' value=\"",$sDir,"\" hidden='hidden'><input type='text' name='comparison' id='compStore' value=\"",$comparison,"\" hidden='hidden'>";
//star options?
echo "<br><select name='paired'><option value='paired'>Paired-end reads</option><option value='unpaired'>Unpaired</option></select><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('pairedI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='pairedI'>Choose if your samples are paired reads. If you select paired you must fill in a suffix to indicate the pairing of files in the options below. _1 and _2 are the default suffixes. For example, the files read_1.fq and read_2.fq would be opposite pairs corresponding to the same read.<br> Unpaired option is untested and has been disabled.</div>";
echo "<br>Pair 1 suffix<input name='p1suffix' type='text' value = ''>";
echo "<br>Pair 2 suffix<input name='p2suffix' type='text' value = ''>";
echo "<br>Read Length: <input name='readLength' type='number' value='150'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('rlI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='rlI'>Read length in base pairs. Previously run sequences with the same base pair length will not have to compute a genome indexing with STAR and RSEM (saves about 6 hours)</div>";
echo "<br>Thread Count: <input name='threads' type='number' value='16'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('thI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='thI'>This indicates the number of threads that will be allocated on the super computer to run necessary tasks. A minimum of 12 threads are necessary to perform alignment with STAR. Suggested number for optimal computation time is currently 16</div>";
echo "<br>Wall Time: <input name='hours' type='number' value='48' min='0' max='168'>:<input name='minutes' type='number' value='00' min='0' max='59'>:<input name='seconds' type='number' value='00' min='0' max='59'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('wtI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='wtI'>This is the maximum amount of time allocated on the super computer for your analysis to run. If this time ends before the analysis is complete, it is possible results will not be salvagable. The lab is not charged for unused hours that are allocated, and the task will terminate after computations finish. The lab is charged for the number of hours actually used multiplied by the number of threads. <br> Genome indexing may take around 6 hours, alignment and quantification may take around 22 hours. Principle component analysis and Differential expression analysis should not take longer than 2 hours. To be safe it is recommended to use more time than expected. Probably choose more than 48 hours.</div>";
echo "<br>PadJ: <input name='padj' type='number' value = '0.5'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('padjI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='padjI'>This is the \"adjusted p value\", a value of 0.5 implies 50% of significant results are false positives. results with Padj above 50% are filtered out</div>";
echo "<br>Log<sub>10</sub>P value: <input name='log10' type='number' value ='5'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('log10I').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='log10I'>This value is used to determine which results are significant. Values with a log<sub>10</sub>(p-value) above this parameter will be determined as differentially expressed genes</div>";
echo "<br>Log<sub>10</sub>Annotation value: <input name='log10a' type='number' value ='30'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('annI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='annI'>Similar to the previous metric, except this value is purely for display. Genes with a log<sub>10</sub>(p-value) above this result will be annotated on volcano plots</div>";
echo "<br>Perform a Quality Assessment<input type='checkbox' name='Quality' value='Yes' id='Quality' checked><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('qualityI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='qualityI'>This option indicates you want to use FASTQC to produce a quality report of the Raw reads.</div>";
echo "<br>Perform Trimming<input type='checkbox' name='Trimming' value='Yes' id='Trimming' checked><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('trimI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='trimI'>This option indicates you want to trim the raw reads, to remove adapter sequences and remove low quality reads</div>";
echo "<br>Compute Alignment and Quantification<input type='checkbox' name='AQ' value='Yes' id='AQ' checked><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('aqI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='aqI'>This option indicates you want to compute an alignment of the selected files to the transcriptome, and then quantify genes (the first steps in RNA sequencing). If this is unchecked, files in the selected directory must be in form \".genes.results\". The former mode has not been tested, and may not succeed. If this fails continue from an old project instead</div>";
echo "<br>Compute PCA<input type='checkbox' name='PCA' value='Yes' id='pca'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('pcaI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='pcaI'>This option indicates that you want to produce a PCA chart of all samples after alignment and qauntification is completed. This chart can be downloaded after the task is completed under the get results option in the menu page</div>";
echo "<br>Compute Differential Expression Analysis<input type='checkbox' name='DESEQ' value='Yes' id='deseq'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('deseqI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='deseqI'>This indicates that you want to perform differential expression analysis. This will produce volcano plots for each comparison, and will produce list of up and down regulated genes for each comparison. It will also produce a list of background genes. This background list is necessary to perform an analysis of differentially regulated genes at <a href='https://david.ncifcrf.gov/'>david.ncifcrf.gov</a>. These files can be downloaded after the task is completed under the get results option in the menu page</div>";
echo "<br>Annotate Volcano Plots with a Gene List<input type='checkbox' name='GL' value='No' id='GL'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('glI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='glI'>Checking this box indicates that you have a list of genes you would like to be annotated on the volcano plots that result from differential expression analysis. If this box is unchecked, the genes above the annotation value previous specified will be used. If this box is checked, please enter names of genes separated by comma in the text box below. <br>For example:<br>CREG1,BUB1,TOP2A,UNC5B</div>"; 
echo "<br>Volcano Plot Gene List<input type='text' name='GeneList' id='GeneList'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('genlI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='genlI'>These genes will be highlighted in the volcano plots.<br>Please enter names of genes separated by comma in this text box<br>For example:<br>CREG1,BUB1,TOP2A,UNC5B</div>"; 
echo "<br>Use specific genes for heatmaps<input type='checkbox' name='HGL' value='No' id='HGL'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('HglI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='HglI'>Checking this box indicates that you have a list of genes you would like to display differential expression for in the Heatmap. If this box is unchecked, all genes will be used. If this box is checked, please enter names of genes separated by comma in the text box below. <br>For example:<br>CREG1,BUB1,TOP2A,UNC5B</div>"; 
echo "<br>Heatmap Gene List<input type='text' name='HGeneList' id='HGeneList'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('HgenlI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='HgenlI'>These genes will be selected for the heatmap.<br>Please enter names of genes separated by comma in this text box<br>For example:<br>CREG1,BUB1,TOP2A,UNC5B</div>"; 
echo "<br>User Email<input type='text' name='EMAIL' value = '' id = 'email'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('emailI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='emailI'>Enter your email adress here. You will recieve an email from the super computer when your analysis is done. After it is done you can download your results!</div>";
echo "<br>Submit<input type='checkbox' name='sub' value='Yes' id='sub'><button type='button' onclick=\"{var infos = document.getElementsByClassName('info');for (var i = 0; i < infos.length; i++) {infos[i].setAttribute('hidden','true');};document.getElementById('submitI').removeAttribute('hidden');}\">?</button><div hidden='hidden' class='info' id='submitI'>Uncheck this box to not submit your task to the TSCC. This will produce a configuration file downloaded to your computer, and a .qsub file on the TSCC, but will not submit it to be executed. This is for debug purposes, and to create comparisons for samples executed in different tasks. One case where that would be helpful is if the estimated wall time for all samples alignment and quantification is over 168 hours. Alignment can be done in a series of smaller tasks, and then the results can be combined later for the faster differential expression analysis.</div>";
#echo "<select name='step' id='stepS' checked><option value='fileselect' selected>File Select</option><option value='attributes'>Define Attributes</option><option value='assign'>Assign</option><option value='parameters'>Parameters</option><option value='RNAseq'>RNAseq</option></select>";
echo "<br><button type='button' onclick='

document.getElementById(\"body1\").setAttribute(\"hidden\",\"true\");
document.getElementById(\"body2\").innerHTML = \"<h3>Creating Tasks and Submitting to Queue. This may take several minutes</h3><div class=\\\"loader\\\"></div>\";
document.getElementById(\"next\").submit();
'>Submit</button></form>";
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
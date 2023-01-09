<?php
#use 0 and 1 rather than true and false which are evaluating to strings
$downloadName = $_GET['file'];
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
?>

<?php
// Oracle database connection details
$u = "ELECTRON7";
$p = "Admin123$";
$conn = oci_connect($u, $p , 'localhost:1521/XEPDB1'); 

// Check connection
if (!$conn) {
    $m = oci_error();
    echo $m['message'], "\n";
    exit;
}
?>

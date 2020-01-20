<?php 

if ( (int) $_GET['output'] == 1) {
	require 'pages/paysUE.php';
}

if ( (int) $_GET['output'] == 2) {
	require 'pages/areaAfric.php';
}

if ( (int) $_GET['output'] == 10) {
	$dbh->deleteTable();
}


 ?>
<?php 
session_start();
require 'vendor/autoload.php';
require 'class/DynamoHandler.php'; 

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="assets/css/style.css">
	<title>Document</title>
</head>
<body>
	
	<?php 
	require 'pages/navigation.php';
	$dbh = new DynamoHandler();
	?>
	<div class="main">
		<section>
			<?php 
			echo "La table est initialisée : "
			. $dbh::$table_init
			."<br>"
			."La table est chargée en donnée : "
			. $dbh::$table_data_loaded 
			."<br>";
			if (isset($_GET['output']) && !empty($_GET['output'])) {
				require 'router.php';
			}
			?>
		</section>
		<aside><?php echo $dbh::$message; ?></aside>
	</div>



</body>
</html>
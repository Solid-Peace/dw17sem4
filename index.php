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
			if (isset($_GET['output']) && !empty($_GET['output'])) {
				require 'router.php';
			}
			?>
		</section>
		<aside><?php echo $dbh::$message; ?></aside>
	</div>

	<footer class="show-table">
		<?php if ($dbh::$table_init == 1 && $dbh::$table_data_loaded == 1): ?>
			<a class="table-delete" href="?output=10"><button>Recharger la Table</button></a>
			<?php $dbh->showAllData(); ?>
		<?php endif ?>

	</footer>



</body>
</html>
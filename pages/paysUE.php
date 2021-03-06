<?php 
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
?>
<h3>Liste des pays de l'UE</h3>

<?php 

if ($dbh::$table_init == 1 && $dbh::$table_data_loaded == 1) {
	
	$a = new DynamoHandler();
	$marshaler = new Marshaler();

	$eav = $marshaler->marshalJson('
	{		
		":rg": "Europe"
	}
	');

	$a->params = [
		'TableName' => $a->tableName,
		'ProjectionExpression' => '#rg, nom',
		'FilterExpression' => '#rg = :rg',
		'ExpressionAttributeNames'=> [ '#rg' => 'region' ],
		'ExpressionAttributeValues'=> $eav
	];

	echo "Scanning for countries of Europe.\n";

	try {
		while (true) {
			$result = $a->dynamodb->scan($a->params);

			foreach ($result['Items'] as $i) {
				$i = $marshaler->unmarshalItem($i);
				echo $i['nom'] . '<br>';
			}

			if (isset($result['LastEvaluatedKey'])) {
				$params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
			} else {
				break;
			}
		}

	} catch (DynamoDbException $e) {
		echo "Unable to scan:\n";
		echo $e->getMessage() . "\n";
	}
}else{
	echo 'La table ou ses donnée ne sont pas initialisée...';
}
?>

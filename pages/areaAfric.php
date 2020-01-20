<?php 
	use Aws\DynamoDb\Exception\DynamoDbException;
	use Aws\DynamoDb\Marshaler;
?>
<h3>Superficie des Pays africain de la 10ème à la 22ème postion</h3>

<?php 

if ($dbh::$table_init == 1 && $dbh::$table_data_loaded == 1) {
	
	$area_classement = [];

	$a = new DynamoHandler();
	$marshaler = new Marshaler();

	$eav = $marshaler->marshalJson('
	{		
		":rg": "Africa"
	}
	');

	$a->params = [
		'TableName' => $a->tableName,
		'ProjectionExpression' => '#rg, nom, area',
		'FilterExpression' => '#rg = :rg',
		'ExpressionAttributeNames'=> [ '#rg' => 'region' ],
		'ExpressionAttributeValues'=> $eav
	];

		try {
		while (true) {
			$result = $a->dynamodb->scan($a->params);

			foreach ($result['Items'] as $i) {
				$i = $marshaler->unmarshalItem($i);
				//echo $i['nom'] . " : " . $i['area'] . '<br>';
				$area_classement[$i['nom']] = $i['area'];
			}

			if (isset($result['LastEvaluatedKey'])) {
				$params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
			} else {
				break;
			}
		}

	} catch (DynamoDbException $e) {
		$a::$message .= "Unable to scan: <br>";
		$a::$message .= $e->getMessage() . "\br>";
	}

	asort($area_classement);
	$i = 0;
	foreach ($area_classement as $key => $value) {
			if($i >= 10 && $i <= 22) {
				echo $i . " : " . $key ." => " . $value . "<br>";
			}
			$i++;
		}

	}
 ?>
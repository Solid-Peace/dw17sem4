<?php 

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class DynamoHandler
{

	public $sdk;
	public $dynamodb;
	public $tableName;
	public $params;
	public static $table_init;
	public static $table_data_loaded;
	public static $message;

	function __construct()
	{
		$this->sdk = new Aws\Sdk([
			'endpoint'    => 'http://localhost:8000',
			'region'      => 'us-west-2',
			'version'     => 'latest',
			'credentials' => [
				'key'    => 'not-a-real-key',
				'secret' => 'not-a-real-secret',
			],
		]);
		$this->tableName = 'dw17quentin';
		$this::$message = "";
		$this->dynamodb  = $this->sdk->createDynamoDb();
		$this::$table_init = $this->init();
		$this::$table_data_loaded = $this->loadData();
	}


	public function init(): bool {
		$table_exists = false;
		$iterator = $this->dynamodb->getIterator('ListTables');

		foreach ($iterator as $tableName) {
			if ($tableName == $this->tableName) $table_exists = true;
		}

		if(!$table_exists) {
			$table_exists = $this->createTable();
			$this->dynamodb->waitUntil('TableExists', array(
				'TableName' => $this->tableName
			));
			return $table_exists;
		}
		return $table_exists;
	}

	public function createTable() {
		$params = [
			'TableName' => $this->tableName,
			'KeySchema' => [
				[
					'AttributeName' => 'nom',
            'KeyType' => 'HASH'  //Partition key
          ],
          [
          	'AttributeName' => 'region',
            'KeyType' => 'RANGE'  //Sort key
          ]
        ],
        'AttributeDefinitions' => [
        	[
        		'AttributeName' => 'nom',
        		'AttributeType' => 'S'
        	],
        	[
        		'AttributeName' => 'region',
        		'AttributeType' => 'S'
        	],

        ],
        'ProvisionedThroughput' => [
        	'ReadCapacityUnits' => 10,
        	'WriteCapacityUnits' => 10
        ]
      ];

      try {
      	$result = $this->dynamodb->createTable($params);
      	$this::$message .= 'Created table.  Status: <br>' 
      	. $result['TableDescription']['TableStatus']
      	.'<br>';
      	return true;

      } catch (DynamoDbException $e) {
      	$this::$message .= "Unable to create table: <br>";
      	$this::$message .= $e->getMessage() . "<br>";
      	return false;
      }
    }


    public function loadData(){

    	$table_count = $this->dynamodb->describeTable([
    		'TableName' => $this->tableName
    	]);

    	(int) $table_count['Table']['ItemCount'] > 2 ? $table_data_loaded = true : $table_data_loaded = false;

    	if($table_data_loaded){$this::$message .= " >> La tables est chargée en donnée et compte : ".$table_count['Table']['ItemCount']." entrées <br> <br>";}

    	if(!$table_data_loaded) {
    		
    		$pays = json_decode(file_get_contents('https://raw.githubusercontent.com/mledoze/countries/master/countries.json'), true);

    		$marshaler = new Marshaler();

    		foreach ($pays as $p) {
    			$nom     = $p['name']['common'];
    			$region  = $p['region'];
    			$langues = $p['languages'];
    			$area    = $p['area'];

    			$json = json_encode([
    				'nom'       => $nom,
    				'region'    => $region,
    				'languages' => $langues,
    				'area'      => $area 
    			]);

    			$params = [
    				'TableName' => $this->tableName,
    				'Item'      => $marshaler->marshalJson($json)
    			];

    			try {
    				$result = $this->dynamodb->putItem($params);
    				$this::$message .= "Added pays: " . $p['name']['common'] . " " . $p['region'] . "<br>";
    				$table_data_loaded = true;
    			} catch (DynamoDbException $e) {
    				$this::$message .= "Unable to add pays:<br>";
    				$this::$message .= $e->getMessage() . "<br>";
    				$table_data_loaded = false;
    				break;
    			}		
    		}

    		return $table_data_loaded;
    	}

    	return $table_data_loaded;
    }


    public function showAllData() {
    	$params = [
    		'TableName' => $this->tableName
    	];

    	$marshaler = new Marshaler();

    	try {
    		while (true) {
    			$result = $this->dynamodb->scan($params);

    			foreach ($result['Items'] as $i) {
    				$i = $marshaler->unmarshalItem($i);
    				echo "nom => <strong>" . $i['nom']
    				. "</strong>, region => " . $i['region']
    				. ", languages => " .  json_encode($i['languages'])
    				. ", area => " . $i['area']
    				. "<br>";
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
    }

    public function deleteTable() {
    	$params = [
    		'TableName' => $this->tableName
    	];

    	try {
    		$result = $this->dynamodb->deleteTable($params);
    		$this::$message .= "Deleted table.<br>";
    		header('Location: /dw17sem4/index.php');

    	} catch (DynamoDbException $e) {
    		$this::$message .= "Unable to delete table:<br>";
    		$this::$message .= $e->getMessage() . "<br>";
    	}
    }
  }

  ?>
<?php 

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class DynamoHandler
{

	public $sdk;
	public $dynamodb;
	public $tableName;
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

    	var_dump($table_count['Table']['ItemCount']);
      if($table_data_loaded){$this::$message .= "hello world";}

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
    }
  }

  ?>
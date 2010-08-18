<?php
class MyMongoDB
{
	protected $mongo = null;
	protected $db = null;
	protected $collection = null;
	protected $db_name = null;
	protected $collection_name = null;

	public function __construct($db, $collection)
	{
		$config = self::getConfig();
		try {
			$this->mongo = new Mongo($config['param']['dns']);
		} catch (MongoConnectionException $e) {
			throw new Exception('Can\'t connect to "'.$config['param']['dns'].'".');
		}

		$this->db = $this->mongo->selectDB($db);
		$this->collection = $this->db->selectCollection($collection);
		$this->db_name = $db;
		$this->collection_name = $collection;
	}
	public function getDbName()
	{
		return $this->db_name;
	}

	public function getCollectionName()
	{
		return $this->collection_name;
	}

	public function insert(MongoDto $data)
	{
		return $this->collection->insert($data->toArray());
	}

	public function group($keys, $initial, $reduce, $options = array())
	{
		return $this->collection->group($keys, $initial, $reduce, $options);
	}

	public function distinct($key)
	{
		return $this->db->command(array('distinct' => $this->collection_name, 'key' => $key));
	}

	public function showCollections()
	{
		$result = array();
		$collections = $this->db->listCollections();
		foreach ($collections as $collection) {
			if ($collection instanceof MongoCollection) {
				$result[] = $collection->__toString();
			}
		}
		return $result;
	}

	public function mapReduce(MongoCode $map, MongoCode $reduce, array $options = array(), array $find_options = array())
	{
		if ($options['out']) {
			$options['out'] = 'mr_' . $this->collection_name . '_' . $options['out'];
		}

		$collections = $this->showCollections();
		if (array_search($this->db_name . '.' . $options['out'], $collections)) {
			$response['cursor'] = $this->db->selectCollection($options['out'])->find($find_options);
			return $response;
		}

		$response = $this->db->command(array(
			"mapreduce" => $this->collection_name,
			"map" => $map,
			"reduce" => $reduce
		) + $options);
		$response['cursor'] = $this->db->selectCollection($response['result'])->find($find_options);
		return $response;
	}

	protected static function getConfig()
	{
		$config_file = sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . 'mongodb.yml';
		$config = sfYaml::load($config_file);
		return $config['all']['master'];
	}
}

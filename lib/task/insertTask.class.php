<?php
/**
 * insert task.
 *
 * @package    sample
 * @subpackage task
 * @author     Masashi Sekine <sekine@cloudrop.jp>
 */
class insertTask extends sfBaseTask
{
	protected function configure()
	{
		// // add your own arguments here
		// $this->addArguments(array(
		//   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
		// ));

		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
			//new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
			new sfCommandOption('timeout', null, sfCommandOption::PARAMETER_REQUIRED, 'timeout seconds', 60),
			// add your own options here
			));

		$this->namespace = 'sample';
		$this->name = 'insert';
		$this->briefDescription = '';
		$this->detailedDescription = 'The [insert|INFO] task does things.' .
			'Call it with:' .
			PHP_EOL .
			'  [php symfony insert|INFO]';
	}

	protected function execute($arguments = array(), $options = array())
	{
		//initialize the database connection
		//$databaseManager = new sfDatabaseManager($this->configuration);
		//$connection = $databaseManager->getDatabase($options['connection'])->getConnection();

		// add your code here
		$collection = 'log_' . date('Ymd');
		$url = 'http://' . sfConfig::get('app_twitter_user_name') . ':' . sfConfig::get('app_twitter_password') . '@stream.twitter.com/1/statuses/sample.json?delimited=length';
		$db = new MyMongoDB('twitter', $collection);
		if (!$fp = fopen($url, 'r')) {
			throw new Exception('Can\'t connect to URL "' . $url . '"');
		}

		$start = time();
		$executed_count = 0;
		$timeout = (int) $options['timeout'];
		$this->log('Started at ' . date('Y-m-d H:i:s', $start));

		$header = stream_get_meta_data($fp);
		//$this->log($header['wrapper_data']);

		while ($json = fgets($fp)) {
			$tweet = json_decode($json, true);
			if (!isset($tweet['id'])) continue;

			$source_url = '';
			$source = '';
			if (preg_match('{<a href="(.+?)".+?>(.+?)</a>}', $tweet['source'], $match)) {
				$source_url = $match[1];
				$source = $match[2];
			} else {
				$source = $tweet['source'];
			}

			$timestamp = strtotime($tweet['created_at']);
			$date = date('Y-m-d H:i:s', $timestamp);
			$time_zone = isset($tweet['user']['time_zone']) ? $tweet['user']['time_zone'] : '';
			$utc_offset = isset($tweet['user']['utc_offset']) ? $tweet['user']['utc_offset'] : '';
			$lang = isset($tweet['user']['lang']) ? $tweet['user']['lang'] : '';
			//echo $date."\t".$source."\t".$source_url."\t".$time_zone."\t".$utc_offset."\t".$lang.PHP_EOL;

			$data = new SampleDto();
			$data->setTimestamp($timestamp);
			$data->setDate($date);
			$data->setSource($source);
			$data->setSourceUrl($source_url);
			$data->setTimeZone($time_zone);
			$data->setUtcOffset($utc_offset);
			$data->setLang($lang);
			if ($db->insert($data)) {
				$executed_count++;
			}

			if ((time() - $start) >= $timeout) break;
		}

		$this->log($executed_count . ' documents are inserted.');
		$this->log('Finished at ' . date('Y-m-d H:i:s'));
		exit;
	}
}

<?php
/**
 * Sample Dao
 *
 * @package sample
 * @subpackage dao
 * @author  Masashi Sekine <sekine@cloudrop.jp>
 */
class SampleDao
{
	protected $db = null;

	public function __construct($collection = 'sample')
	{
		$this->db = new MyMongoDB('twitter', $collection);
	}

	public function getAll()
	{
		$keys = array('source'=>true, 'source_url'=>true);
		$initial = array('count'=>0);
		$reduce = 'function(obj, prev){ prev.count++; }';
		$conditons = array();

		$result = $this->db->group($keys, $initial, $reduce, $conditons);

		$source = array();
		$count = array();
		$source_url = array();
		foreach ($result['retval'] as $key=>$row) {
			$source[$key] = $row['source'];
			$count[$key] = $row['count'];
			$source_url[$key] = $row['source_url'];
		}
		array_multisort($count, SORT_DESC, $source, SORT_ASC, $source_url, SORT_ASC, $result['retval']);
		unset($source, $count, $source_url);
		return $result;
	}

	public function getAllByForm(sfForm $form)
	{
		$lang = $form->getValue('lang');
		$time_zone = $form->getValue('time_zone');
		$utc_offset = $form->getValue('utc_offset');

		$keys = array('source'=>true, 'source_url'=>true);
		$initial = array('count'=>0);
		$reduce = 'function(obj, prev){ prev.count++; }';

		$conditons = array();
		if ($lang) $conditons['lang'] = $lang;
		if ($time_zone) $conditons['time_zone'] = $time_zone;
		if ($utc_offset) $conditons['utc_offset'] = $utc_offset;

		$result = $this->db->group($keys, $initial, $reduce, $conditons);

		$source = array();
		$count = array();
		$source_url = array();
		foreach ($result['retval'] as $key=>$row) {
			$source[$key] = $row['source'];
			$count[$key] = $row['count'];
			$source_url[$key] = $row['source_url'];
		}
		array_multisort($count, SORT_DESC, $source, SORT_ASC, $source_url, SORT_ASC, $result['retval']);
		unset($source, $count, $source_url);
		return $result;
	}

	public function getAllWithMapReduceByForm(sfForm $form)
	{
		$lang = $form->getValue('lang');
		$time_zone = $form->getValue('time_zone');
		$utc_offset = $form->getValue('utc_offset');

		$extra_map = '';
		if ($lang) $extra_map .= 'if(this.lang != \'' . $lang . '\') return;';
		if ($time_zone) $extra_map .= 'if(this.time_zone != \'' . $time_zone . '\') return;';
		if ($utc_offset) $extra_map .= 'if(this.utc_offset != \'' . $utc_offset . '\') return;';

		$map = new MongoCode('
			function(){' .
			$extra_map . '
				emit(this.source, {count: 1, url: this.source_url});
			}
		');

		$reduce = new MongoCode('
			function(source, values){
				var total = 0;
				for(var i=0; i<values.length; i++){
					total += values[i].count;
				}
				return { count: total, url: values[0].url };
			}
		');

		$options = array();
		if ($this->db->getCollectionName() != 'log_' . date('Ymd')) {
			if (empty($lang) && empty($time_zone) && empty($utc_offset)) {
				$options['out'] = 'all';
			} else {
				$options['out'] = 'part';
				$options['out'] = empty($lang) ? $options['out'] : $options['out'] . '_' . $lang;
				$options['out'] = empty($time_zone) ? $options['out'] : $options['out'] . '_' . rawurlencode($time_zone);
				$options['out'] = empty($utc_offset) ? $options['out'] : $options['out'] . '_' . rawurlencode($utc_offset);
			}
		}

		$response = $this->db->mapReduce($map, $reduce, $options);
		$result = array();
		$result['retval'] = array();

		$source = array();
		$count = array();
		$position = 0;
		foreach ($response['cursor'] as $key=>$row) {
			$result['retval'][$position]['source'] = $row['_id'];
			$result['retval'][$position]['source_url'] = $row['value']['url'];
			$result['retval'][$position]['count'] = $row['value']['count'];
			$source[$position] = $row['_id']; //$key
			$count[$position] = $row['value']['count'];
			$position++;
		}

		array_multisort($count, SORT_DESC, $source, SORT_ASC, $result['retval']);
		$result['time_millis'] = isset($response['timeMillis']) ? $response['timeMillis'] : 0;
		$result['total_count'] = isset($response['counts']['input']) ? $response['counts']['input'] : array_sum($count);
		$result['count'] = isset($response['counts']['emit']) ? $response['counts']['emit'] : array_sum($count);
		$result['keys'] = isset($response['counts']['output']) ? $response['counts']['output'] : count($result['retval']);

		unset($source, $count, $url, $response);
		return $result;
	}

	public function getTimeTableByForm($source, sfForm $form)
	{
		if (!is_array($source)) {
			$source = array($source);
		}

		$lang = $form->getValue('lang');
		$time_zone = $form->getValue('time_zone');
		$utc_offset = $form->getValue('utc_offset');

		$extra_map = '';
		if ($lang) $extra_map .= 'if(this.lang != \'' . $lang . '\') return;';
		if ($time_zone) $extra_map .= 'if(this.time_zone != \'' . $time_zone . '\') return;';
		if ($utc_offset) $extra_map .= 'if(this.utc_offset != \'' . $utc_offset . '\') return;';

		$map = new MongoCode('
			function(){' .
			$extra_map . '
				var hour = this.date.slice(11,13);
				emit(this.source + "\t" + hour, 1);
			}
		');

		$reduce = new MongoCode('
			function(source, values){
				var total = 0;
				for(var i=0; i<values.length; i++){
					total += values[i];
				}
				return total;
			};
		');

		$options = array();
		if ($this->db->getCollectionName() != 'log_' . date('Ymd')) {
			if (empty($lang) && empty($time_zone) && empty($utc_offset)) {
				$options['out'] = 'time_table_all';
			} else {
				$options['out'] = 'time_table';
				$options['out'] = empty($lang) ? $options['out'] : $options['out'] . '_' . $lang;
				$options['out'] = empty($time_zone) ? $options['out'] : $options['out'] . '_' . rawurlencode($time_zone);
				$options['out'] = empty($utc_offset) ? $options['out'] : $options['out'] . '_' . rawurlencode($utc_offset);
			}
		}
		$find_options = array('$where'=>'function(){ var source = this._id.slice(0,this._id.indexOf("\t")); return source == "' . implode('" || source == "', $source) . '"; }');
		$response = $this->db->mapReduce($map, $reduce, $options, $find_options);
		$response['cursor']->sort(array('_id'=>1));

		$result = array();
		foreach ($response['cursor'] as $key=>$row) {
			list($source, $hour) = explode("\t", $row['_id']);
			$result[$source][$hour] = $row['value'];
		}
		return $result;
	}
	/**
	 * 言語一覧を返却
	 * @return array
	 */
	public function getLangs()
	{
		$result = $this->db->distinct('lang');
		if ($result['ok'] == 1) {
			return $result['values'];
		}
		return array();
	}

	public function getTimeZones()
	{
		$result = $this->db->distinct('time_zone');
		if ($result['ok'] == 1) {
			return $result['values'];
		}
		return array();
	}

	public function getUtcOffsets()
	{
		$result = $this->db->distinct('utc_offset');
		if ($result['ok'] == 1) {
			return $result['values'];
		}
		return array();
	}

	public function getLogNames()
	{
		$result = array();
		$collections = $this->db->showCollections();
		foreach ($collections as $collection) {
			if (strpos($collection, 'twitter.log_', 0) === false) continue;
			$date = substr($collection, 12);
			$year = substr($collection, 12, 4);
			$month = substr($collection, 16, 2);
			$day = substr($collection, 18);
			$result[$date] = $year . '-' . $month . '-' . $day;
		}
		arsort($result);
		return $result;
	}
}

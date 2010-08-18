<?php
/**
 * api actions.
 *
 * @package    sample
 * @subpackage api
 * @author     Masashi Sekine <sekine@cloudrop.jp>
 */
class apiActions extends sfActions
{
	/**
	 * Executes index action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeIndex(sfWebRequest $request)
	{
		$this->forward404();
	}

	public function executeTime_table(sfWebRequest $request)
	{
		$this->forward404Unless($request->getMethod() == sfWebRequest::POST);
		$this->forward404Unless($source = $request->getParameter('source'));
		$extract = $request->getParameter(ExtractForm::FORM_NAME);

		if ($extract['log'] === NULL) $extract['log'] = date('Ymd', strtotime('-1 day'));
		$sample = new SampleDao('log_' . $extract['log']);

		$form = new ExtractForm(null, array('dao' => $sample));
		$form->bind($extract);
		if (!$form->isValid()) {
			$this->flushByJson(array('error' => 'Invalid Parameter'));
		}

		$position = 0;
		$result = array();
		$data_array = array();
		$response = $sample->getTimeTableByForm($source, $form);
		foreach ($source as $source_name) {
			$result['data'][$position]['source'] = $source_name;
			$result['data'][$position]['time_table'] = $response[$source_name];
			$data_array = array_merge($data_array, $response[$source_name]);
			$position++;
		}

		sort($data_array);
		$result['max'] = array_pop($data_array);
		$result['min'] = array_shift($data_array);
		$result['log'] = $extract['log'];
		$result['lang'] = $extract['lang'];
		$result['time_zone'] = $extract['time_zone'];
		$result['utc_offset'] = $extract['utc_offset'];
		$this->flushByJson($result);
	}

	protected function flushByJson($value)
	{
		header('Content-Type: application/json; UTF-8');
		echo json_encode($value);
		exit;
	}

}

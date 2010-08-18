<?php
/**
 * top actions.
 *
 * @package    sample
 * @subpackage top
 * @author     Masashi Sekine <sekine@cloudrop.jp>
 */
class topActions extends sfActions
{
	/**
	 * Executes index action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeIndex(sfWebRequest $request)
	{
		$request_params = $request->getParameter(ExtractForm::FORM_NAME);
		if ($request_params['log'] === NULL) $request_params['log'] = date('Ymd', strtotime('-1 day'));

		$sample = new SampleDao('log_' . $request_params['log']);

		if ($request->getMethod() === sfRequest::POST) {
			$this->form = new ExtractForm(null, array('dao' => $sample));
			$this->form->bind($request_params);
			if (!$this->form->isValid()) {
				$this->error_message = 'Invalid Parameter';
				return sfView::SUCCESS;
			}
		} else {
			$this->form = new ExtractForm(array('log' => $request_params['log']), array('dao' => $sample));
		}

		if ($request_params['log'] != date('Ymd')) {
			$this->result = $sample->getAllWithMapReduceByForm($this->form);
		} else {
			$this->result = $sample->getAllByForm($this->form);
		}

		$this->getResponse()->setTitle('Twitter Client Ranking by Streaming API');
	}

}

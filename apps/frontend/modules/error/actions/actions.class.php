<?php
/**
 * error actions.
 *
 * @package    sample
 * @subpackage error
 * @author     Masashi Sekine <sekine@cloudrop.jp>
 */
class errorActions extends sfActions
{
	/**
	 * Executes index action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeIndex(sfWebRequest $request)
	{
		$this->forward('error', 'status404');
	}

	public function executeStatus404(sfWebRequest $request)
	{
		$this->getResponse()->setStatusCode(404);
		$this->getResponse()->setTitle('404 Not Found');
		$this->setLayout('error_layout');
	}

	public function executeStatus401(sfWebRequest $request)
	{
		$this->getResponse()->setStatusCode(401);
		$this->getResponse()->setTitle('401 Authorization Required');
		$this->setLayout('error_layout');
	}
}

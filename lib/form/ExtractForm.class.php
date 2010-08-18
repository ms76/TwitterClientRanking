<?php
/**
 * Extract Form
/*
 * @package    sample
 * @subpackage form
 * @author     Masashi Sekine <sekine@cloudrop.jp>
 */
class ExtractForm extends sfFormSymfony
{
	const FORM_NAME = 'extract';

	public function setup()
	{
		parent::setup();
		$sample = $this->getOption('dao');
		if (!($sample instanceof SampleDao)) {
			throw new Exception(get_class($sample) . ' is not instance of SampleDao.');
		}

		$this->widgetSchema->getFormFormatter()->setErrorListFormatInARow('%errors%');
		$this->widgetSchema->getFormFormatter()->setErrorRowFormatInARow('%error%');

		$log_names = $sample->getLogNames();
		$langs = $sample->getLangs();
		$time_zones = $sample->getTimeZones();
		$utc_offsets = $sample->getUtcOffsets();

		$this->widgetSchema['log'] = new sfWidgetFormChoice(array('choices' => $log_names));
		$this->widgetSchema['lang'] = new sfWidgetFormChoice(array('choices' => $this->getChoices($langs, 'All Languages')));
		$this->widgetSchema['time_zone'] = new sfWidgetFormChoice(array('choices' => $this->getChoices($time_zones, 'All Tiem zones')));
		$this->widgetSchema['utc_offset'] = new sfWidgetFormChoice(array('choices' => $this->getChoices($utc_offsets, 'All UTC Offsets')));

		$this->validatorSchema['log'] = new sfValidatorChoice(array('choices' => array_keys($log_names), 'required' => true));
		$this->validatorSchema['lang'] = new sfValidatorChoice(array('choices' => $this->getChoices($langs), 'required' => false));
		$this->validatorSchema['time_zone'] = new sfValidatorChoice(array('choices' => $this->getChoices($time_zones), 'required' => false));
		$this->validatorSchema['utc_offset'] = new sfValidatorChoice(array('choices' => $this->getChoices($utc_offsets), 'required' => false));

		$this->widgetSchema->setNameFormat(self::FORM_NAME . '[%s]');
	}

	protected function getChoices($array, $empty_word = false)
	{
		if ($empty_word !== false) $choices[''] = $empty_word;
		foreach ($array as $key => $value) {
			if ($value === "") continue;
			$choices[$value] = $value;
		}
		return $choices;
	}
}

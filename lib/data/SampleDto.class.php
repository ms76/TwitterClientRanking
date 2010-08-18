<?php
/**
 * Sample dto.
 *
 * @package    sample
 * @subpackage dto
 * @author     Masashi Sekine <sekine@cloudrop.jp>
 */
class SampleDto extends MongoDto
{
	protected $timestamp;
	protected $date;
	protected $source;
	protected $source_url;
	protected $time_zone;
	protected $utc_offset;
	protected $lang;
}

<?php

namespace Axisubs\Helper;
use Carbon\Carbon;

/**
 * Permission helper.
 */
class DateFormat{
	use CarbonHelper;

	public static $instance = null;
	public static $store_format = null;

	public function __construct($properties=null) {
	}

	/**
	 * get an instance
	 * @param array $config
	 * @return 
	 * * */
	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}
		
		self::$store_format = 'Y-m-d H:i:s';//TODO: change to dynamic

		return self::$instance;
	}

	/**
	 * Method to format the date 
	 * Assume the supplied date is in UTC and format it to local timezone
	 * 
	 * */
	public function get_formatted_date( $local='', $options=array() ) {
		$format = self::$store_format ;
		$tz = 'UTC'; //TODO: change to dynamic
		
		//Carbon::setLocale('de');

		$date = $this->getDate( $local );
		$date->setTimeZone($tz);
		
		if(isset($options['format']) && $options['format']) {
			$format = isset($options['format']) ? $options['format'] : 'Y-m-d H:i:s'; 
		}
		if (empty($format)) {
			$format = 'Y-m-d H:i:s';
		}

		$result = $date->format($format);
		return $result;
	}

	/**
	 * An Alias for get_formated_date
	 * */
	public function format( $local, $options=array() ) {
		$date = $this->get_formatted_date($local,$options);
		return $date;
	}

	public function getCarbonDate( $date ='' ){
		return $this->getDate($date);
	}

	public function getOnlyDate( $date ='' ){
		return substr($this->getCarbonDate($date), 0, 10);
	}
}
<?php namespace Ekotechnology\Guzzlecal\Representations;

class NewEvent {
	protected $event;

	function __construct() {
		$this->event = array(
			'kind' => 'calendar#event',
			'calendarId' => '',
			'start' => array(),
			'end' => array()
		);
	}

	/**
	 * Magic Get allows us to call on the content of the object
	 * (that is actually stored in the object's content array)
	 * @param  string $name The key to which you want the value
	 * @return mixed
	 */
	function __get($name) {
		if (array_key_exists($name, $this->event)) {
			return $this->event[$name];
		}
	}
	/**
	 * Magic Set allows us to call on the content of the object
	 * (that is actually stored in the object's content array)
	 * @param  string $name The key to which you want the value
	 * @param  string $value The value
	 * @return mixed
	 */
	function __set($name, $value) {
		return $this->event[$name] = $value;
	}

	function toJSON() {
		return json_encode($this->event);
	}
}
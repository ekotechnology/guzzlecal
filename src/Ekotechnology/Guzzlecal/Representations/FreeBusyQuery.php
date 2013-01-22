<?php namespace Ekotechnology\Guzzlecal\Representations;

class FreeBusyQuery implements Representation {
	
	/**
	 * Stores the contents of the representation
	 * @var array
	 */
	protected $content = array();

	/**
	 * Returns the name of the class with no namespace
	 * @return string Class Name
	 */
	function className() {
		$class = explode('\\', __CLASS__);
		return end($class);
	}

	/**
	 * Returns the `kind` field provided by the input
	 * @return string Kind
	 */
	function identify(){
		return $this->content['kind'];
	}
	
	/**
	 * Get the items from the input (each of which will be an object)
	 * If there aren't any, just an empty array
	 * @param  string  Secondary key to fetch
	 * @return array
	 */
	function items($secondary='') {
		if ($secondary) {
			if (array_key_exists('items', $this->content) && array_key_exists($secondary, $this->content['items'])) {
				return $this->content['items'][$secondary];
			}
			elseif (array_key_exists('items', $this->content)) {
				return $this->content['items'];
			}
			else {
				return null;
			}
		}
		else {
			if (array_key_exists('items', $this->content)) {
				return $this->content['items'];
			}
			else {
				return null;
			}
		}	
	}
	/**
	 * Take the input from Google and populate the class
	 * @param [type] $data       [description]
	 * @param array  $exceptions [description]
	 */
	function __construct($data, $exceptions=array()) {
		$time = function($input, $key) {
			if (is_array($input)) {
				if (array_key_exists('date', $input)) {
					return new \DateTime($input['date']);
				}
				if (array_key_exists('dateTime', $input)) {
					return new \DateTime($input['dateTime']);
				}
				return $input;
			}
			else {
				return new \DateTime($input);
			}
		};
		$mutators['start'] = $time;
		$mutators['end'] = $time;

		foreach ($data as $key => $val) {
			if (!is_array($val)) {
				$this->content[$key] = $val;
			}
			elseif ($key == 'calendars') {
				foreach ($val as $calendar => $instances) {
					foreach ($instances as $key => $val) {
							$mutator = $mutators['start'];
							foreach ($val as $instance => $times) {
								$this->content['items'][$calendar][] = array(
									'start' => $mutator($times['start'], 'start'),
									'end' => $mutator($times['end'], 'end')
								);
							}
					}
				}
			}				
		}
	}

}
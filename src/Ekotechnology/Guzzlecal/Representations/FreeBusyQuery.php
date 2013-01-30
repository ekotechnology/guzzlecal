<?php namespace Ekotechnology\Guzzlecal\Representations;

class FreeBusyQuery implements Representation {
	
	/**
	 * Stores the contents of the representation
	 * @var array
	 */
	protected $content = array();

	/**
	 * Store the mutators for fields
	 * @var array
	 */
	protected $mutators = array();

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
		$this->generateMutators();
		foreach ($data as $key => $val) {
			if (!is_array($val)) {
				$this->content[$key] = $val;
			}
			elseif ($key == 'calendars') {
				foreach ($val as $calendar => $instances) {
					foreach ($instances as $key => $val) {
						foreach ($val as $instance => $times) {
							$this->content['items'][$calendar][] = array(
								'start' => $this->mutate('start', $times['start']),
								'end' => $this->mutate('end', $times['end'])
							);
						}
					}
				}
			}				
		}
	}

	/**
	 * Adds the mutators to the object level
	 * ($this->mutators)
	 * @return void
	 */
	protected function generateMutators() {
		$mutators[] = array(
			'fields' => array('created', 'updated', 'start', 'finish', 'end'),
			'get' => function($input, $key) {
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
			},
			'set' => function($input, $key) {
				if ($input instanceOf \DateTime) {
					return array('dateTime' => $input->format('c'));
				}
				else {
					return $input;
				}
			}
		);
		$this->mutators = $mutators;
	}

	protected function mutate($key, $value) {
		if ($this->hasMutator($key, 'get')) {
			$m = $this->hasMutator($key, 'get');
			return $m($value, $key);
		}
		return $value;
	}

	/**
	 * Determines if a field has a mutator or not
	 * @param  string  $key    Field name to find mutator for
	 * @param  string  $return 'get'  or 'set', or nothing to just check existence
	 * @return mixed           Either returns the requested mutator, true, or false if not found
	 */
	protected function hasMutator($key, $return='') {
		foreach ($this->mutators as $mutator) {
			if (in_array($key, $mutator['fields'])) {
				if ($return == 'get') {
					return $mutator['get'];
				}
				elseif ($return == 'set') {
					return $mutator['set'];
				}
				else {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Magic Get allows us to call on the content of the object
	 * (that is actually stored in the object's content array)
	 * @param  string $name The key to which you want the value
	 * @return mixed
	 */
	function __get($name) {
		if (array_key_exists($name, $this->content)) {
			if ($m = $this->hasMutator($name, 'get')) {
				return $m($this->content[$name], $name);
			}
			else {
				return $this->content[$name];
			}
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
		if ($m = $this->hasMutator($name, 'set')) {
			return $this->content[$name] = $m($value, $name);
		}
		else {
			return $this->content[$name] = $value;
		}
	}


}
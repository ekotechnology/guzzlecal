<?php namespace Ekotechnology\Guzzlecal\Representations;

class NewEvent {
	protected $mutators;
	protected $content;

	function __construct() {
		$this->generateMutators();
		$this->content = array(
			'kind' => 'calendar#event',
			'calendarId' => '',
			'start' => array(
				'dateTime' => ''
			),
			'end' => array(
				'dateTime' => ''
			)
		);
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

	function toJSON() {
		return json_encode($this->content);
	}
}
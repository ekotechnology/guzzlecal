<?php namespace Ekotechnology\Guzzlecal\Representations;

trait RepresentationTrait {
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
	 * Returns the etag, unless a comparison is provided,
	 * in which case the result will be a bool on if the etag matches
	 * the comparison.
	 * @param  string  $comparison ETag to compare
	 * @param  boolean $strict     Whether or not to use strict comparison
	 * @return mixed               Return bool on comparison or ETag string
	 */
	function etag($comparison='', $strict=false){
		$etag = str_replace('"', '', $this->content['etag']);
		if ($comparison == '') {
			return $etag;
		}
		elseif ($comparison != '' && $strict == false && $comparison != $etag) {
			return false;
		}
		elseif ($comparison != '' && $strict == true && $comarison !== $etag) {
			return false;
		}
		else {
			return true;
		}
	}
	/**
	 * Returns the DateTime of when the item was last updated,
	 * unless a comparison is provided.  If the comparison is the same
	 * it returns true, otherwise false.
	 * @param  DateTime $comparison Updated DateTime
	 * @return mixed                DateTime or boolean comparison
	 */
	function updated(\DateTime $comparison=null) {
		if (array_key_exists('updated', $this->content)) {
			if (!is_null($comparison)) {
				if ($this->content['updated'] != $comparison) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return $this->content['updated'];
			}
		}
		else {
			return false;
		}
	}
	/**
	 * Get the items from the input (each of which will be an object)
	 * If there aren't any, just an empty array
	 * @param  string  Secondary key to fetch
	 * @return array
	 */
	function items($secondary='') {
		if (array_key_exists('items', $this->content)) {
			return $this->content['items'];
		}
		else {
			return array();
		}
	}
	/**
	 * The constructor basically fills out the object,
	 * if the top level array key is items, it will then try
	 * and turn each of the items into another filled
	 * object as defined by the kind/type of item it is
	 * @param mixed $json          Anything that can be run through foreach
	 * @param array $exceptions    Allows you to not include values in the filled object
	 */
	function __construct($json=array(), $exceptions=array()) {
		$this->generateMutators();

		foreach ($json as $key => $val) {
			if ($key !== 'items' && !in_array($key, $exceptions)) {
				$this->content[$key] = $val;
			}
			elseif (!in_array($key, $exceptions)) {
				foreach ($val as $ikey => $item) {
					switch ($item['kind']) {
						case 'calendar#calendarListEntry':
							$this->content['items'][$ikey] = new CalendarListEntry($item, $exceptions);
						break;
						case 'calendar#event':
							$this->content['items'][$ikey] = new Event($item, $exceptions);
						break;
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
			'fields' => array('created', 'updated', 'start', 'finish'),
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
	
	function toArray() {
		return $this->content;
	}
}

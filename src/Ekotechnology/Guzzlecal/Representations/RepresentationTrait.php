<?php namespace Ekotechnology\Guzzlecal\Representations;

trait RepresentationTrait {

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
	 * @return array
	 */
	function items() {
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
	function __construct($json, $exceptions=array()) {
		$mutators[] = array(
			'fields' => array('created', 'updated', 'start', 'finish'),
			'handler' => function($input, $key) {
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
			}
		);	
		foreach ($json as $key => $val) {
			if ($key !== 'items' && !in_array($key, $exceptions)) {
				foreach ($mutators as $mutator) {
					if (in_array($key, $mutator['fields'])) {
						$action = $mutator['handler'];
						$this->content[$key] = $action($val, $key);
					}
					else {
						$this->content[$key] = $val;
					}
				}
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
	 * Magic Get allows us to call on the content of the object
	 * (that is actually stored in the object's content array)
	 * @param  string $name The key to which you want the value
	 * @return mixed
	 */
	function __get($name) {
		if (array_key_exists($name, $this->content)) {
			return $this->content[$name];
		}
	}
}
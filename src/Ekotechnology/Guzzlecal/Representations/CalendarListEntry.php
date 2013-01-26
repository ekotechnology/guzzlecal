<?php namespace Ekotechnology\Guzzlecal\Representations;

class CalendarListEntry implements Representation {
	use RepresentationTrait;

	function __get($key) {
		if (array_key_exists($key, $this->content)) {
			return $this->content[$key];
		}
		else {
			return null;
		}
	}
}
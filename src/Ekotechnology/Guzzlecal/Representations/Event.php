<?php namespace Ekotechnology\Guzzlecal\Representations;

class Event implements Representation {
	use RepresentationTrait;
	function toJSON() {
		return json_encode($this->content);
	}
}
<?php namespace Ekotechnology\Guzzlecal\Representations;

interface Representation {
	function className();
	function identify();
	function items($secondary='');
	function __construct($data, $exceptions=array());
}
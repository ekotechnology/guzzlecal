<?php namespace Ekotechnology\Guzzlecal\Representations;

interface Representation {
	function className();
	function identify();
	function etag();
	function items();
	function __construct($data, $exceptions=array());
}
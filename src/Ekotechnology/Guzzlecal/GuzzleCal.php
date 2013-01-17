<?php namespace Ekotechnology\Guzzlecal;
use Ekotechnology\Guzzlecal\Representations\CalendarList;
use Ekotechnology\Guzzlecal\Representations\EventsList;
use Ekotechnology\Guzzlecal\Auth\GoogleOauth2;

use Guzzle\Http\Client;

class Guzzlecal {
	var $oauth;
	var $client;

	public function init($config = array()) {
		$this->oauth = new GoogleOauth2($config);
		$client = new Client('https://www.googleapis.com/calendar/v3/');
		$this->client = $client->addSubscriber($this->oauth);

	}
	public function calendarsList($exceptions=array()) {
		$raw = $this->client->get('users/me/calendarList')->send()->json();
		return new CalendarList($raw, $exceptions);
	}

	public function eventsList($calendar, $exceptions=array()) {
		return new EventsList($this->client->get('calendars/' . urlencode($calendar) .'/events')->send()->json(), $exceptions);
	}

	public function freeBusy($calendars = array(), \DateTime $timeMin, \DateTime $timeMax) {

	}

	public function authURL() {
		return $this->oauth->authUrl();
	}

	public function handleOauth() {
		return $this->oauth->handleOauth();
	}
}
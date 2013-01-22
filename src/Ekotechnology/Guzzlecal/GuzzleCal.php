<?php namespace Ekotechnology\Guzzlecal;
use Ekotechnology\Guzzlecal\Representations\CalendarList;
use Ekotechnology\Guzzlecal\Representations\EventsList;
use Ekotechnology\Guzzlecal\Representations\FreeBusyQuery;
use Ekotechnology\Guzzlecal\Representations\Event;
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
	public function createEvent(\Ekotechnology\Guzzlecal\Representations\NewEvent $event) {

		$resp = $this->client->post('calendars/' . $event->calendarId . '/events')->setHeader('Content-Type', 'application/json')->setBody($event->toJSON())->send()->json();

		return new Event($resp);
	}
	public function freeBusy($calendars = array(), \DateTime $timeMin, \DateTime $timeMax, $exceptions=array()) {
		$params = array(
			'items' => $calendars,
			'timeMin' => $timeMin->format('c'),
			'timeMax' => $timeMax->format('c')
		);
		return new FreeBusyQuery($this->client->post('freeBusy')->setHeader('Content-Type', 'application/json')->setBody(json_encode($params))->send()->json(), $exceptions);
	}

	public function authURL() {
		return $this->oauth->authUrl();
	}

	public function handleOauth() {
		return $this->oauth->handleOauth();
	}

	public function getClient() {
		return $this->client;
	}
}
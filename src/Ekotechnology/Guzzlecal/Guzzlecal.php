<?php namespace Ekotechnology\Guzzlecal;
use Ekotechnology\Guzzlecal\Representations\Calendar;
use Ekotechnology\Guzzlecal\Representations\CalendarList;
use Ekotechnology\Guzzlecal\Representations\EventsList;
use Ekotechnology\Guzzlecal\Representations\FreeBusyQuery;
use Ekotechnology\Guzzlecal\Representations\Event;
use Ekotechnology\Guzzlecal\Auth\Oauth2;

use Guzzle\Http\Client;

class Guzzlecal {
	var $oauth;
	var $client;

	public function init($config = array()) {
		$this->oauth = new Oauth2($config);
		$client = new Client('https://www.googleapis.com/calendar/v3/');
		$this->client = $client->addSubscriber($this->oauth);

	}
	
	public function calendarsList($exceptions=array(), $returnJson=false) {
		$raw = $this->client->get('users/me/calendarList')->send()->json();
		if ($returnJson) {
			return $raw;			
		}
		return new CalendarList($raw, $exceptions);
	}

	public function createCalendar(\Ekotechnology\Guzzlecal\Representations\Calendar $cal) {
		return new Calendar($this->client->post('calendars')->setHeader('Content-Type', 'application/json')->setBody($cal->toJSON())->send()->json());
	}

	public function eventsList($calendar, $parameters=array(), $exceptions=array(), $returnJson=false) {
		$request = $this->client->get('calendars/' . urlencode($calendar) . '/events');
		$query = $request->getQuery();

		foreach ($parameters as $key => $value) {
			$query->set($key, $value);
		}
		$raw = $request->send()->json();

		if ($returnJson) {
			return $raw;
		}
		return new EventsList($request->send()->json(), $exceptions);
	}


	public function createEvent(\Ekotechnology\Guzzlecal\Representations\NewEvent $event) {
		return new Event($this->client->post('calendars/' . $event->calendarId . '/events')->setHeader('Content-Type', 'application/json')->setBody($event->toJSON())->send()->json());
	}

	public function getEvent($calendar, $eventId, $exceptions=array(), $returnJson=false) {
		$raw = $this->client->get('calendars/' . $calendar . '/events/' . $eventId)->send()->json();
		if ($returnJson) {
			return $raw;
		}
		return new Event($raw, $exceptions);
	}

	public function updateEvent($calendar, \Ekotechnology\Guzzlecal\Representations\Event $event) {
		return new Event($this->client->put('calendars/' . $calendar . '/events/' . $event->id)->setHeader('Content-Type', 'application/json')->setBody($event->toJSON())->send()->json());
	}

	public function freeBusy($calendars = array(), \DateTime $timeMin, \DateTime $timeMax, $exceptions=array()) {
		foreach ($calendars as $cal) {
			$cals[] = array('id' => $cal);
		}
		$params = array(
			'items' => $cals,
			'timeMin' => $timeMin->format('c'),
			'timeMax' => $timeMax->format('c')
		);
		return new FreeBusyQuery($this->client->post('freeBusy')->setHeader('Content-Type', 'application/json')->setBody(json_encode($params))->send()->json(), $exceptions);
	}

	public function authURL($state="") {
		return $this->oauth->authUrl($state);
	}

	public function handleOauth() {
		return $this->oauth->handleOauth();
	}

	public function getClient() {
		return $this->client;
	}

	public function setUID($id) {
		return $this->oauth->updateConfig('userID', $id);
	}

	public function setConfig($key, $value) {
		return $this->oauth->updateConfig($key, $value);
	}
}
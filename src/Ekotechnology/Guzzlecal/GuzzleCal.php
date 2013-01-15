<?php namespace Ekotechnology\Guzzlecal;
use Ekotechnology\Guzzlecal\Representations\CalendarList;
use Ekotechnology\Guzzlecal\Representations\EventsList;

class Guzzlecal {

	static public function calendarsList($exceptions=array()) {
		$client = \App::make('OauthClient');
		$raw = $client->get('users/me/calendarList')->send()->json();
		return new CalendarList($raw, $exceptions);
	}

	static public function eventsList($calendar, $exceptions=array()) {
		$client = \App::make('OauthClient');
		return new EventsList($client->get('calendars/' . urlencode($calendar) .'/events')->send()->json(), $exceptions);
	}
}
<?php namespace Ekotechnology\Guzzlecal\Auth;

use Guzzle\Http\Client;
use Guzzle\Common\Event;
use Guzzle\Common\Collection;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Guzzle\Http\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Ekotechnology\GuzzleCal\Exceptions\ClientDeniedException;
use Ekotechnology\GuzzleCal\Exceptions\UnexpectedInput;
use Ekotechnology\GuzzleCal\Exceptions\KeyExpired;
use Ekotechnology\GuzzleCal\Exceptions\InvalidRefresh;


class Oauth2 implements EventSubscriberInterface {

	/**
	 * @var Collection Configuration settings
	 */
	protected static $config;

	const URL_STUB = 'https://accounts.google.com/o/oauth2';
	const AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
	const REVOKE_URL = 'https://accounts.google.com/o/oauth2/revoke';

	/**
	 * Create a new OAuth2 plugin
	 *
	 * @param array Configuration array
	 *     - string 'clientId'
	 *     - string 'clientSecret'
	 *     - string 'developerKey'
	 *     - string 'token'
	 *     - string 'redirectUri'
	 *     - string 'state'
	 *     - string 'type'
	 *     - string 'prompt'
	 *     - array  'scopes'
	 */
	public function __construct($config) {
		self::$config = Collection::fromConfig($config, array(
				'type' => 'offline',
				'prompt' => 'auto'
			),
			array(
				'clientId', 'clientSecret', 'redirectUri', 'scope'
			)
		);		
	}

	public static function getSubscribedEvents() {
		return array(
			'request.before_send' => array('onRequestBeforeSend', -1000),
			'request.error' => array('onRequestError', -100)
		);
	}

	/**
	 * Handles signing requests that are going to be sent to the server
	 * @param  Event  $event Event given by the Event Dispatcher
	 * @return array         Returns the OAuth2 params for the request
	 */
	public function onRequestBeforeSend(Event $event) {
		$event['request']->setHeader('Authorization', $this->_buildAuthorizationHeader());
	}

	protected function _buildAuthorizationHeader() {
		$storage = self::$config['storage']['getToken'];
		return 'Bearer ' . $storage();
	}

	public function onRequestError(Event $event) {
		if ($event['response']->getStatusCode() == 401) {
			// The access token has expired.  We need to get a new token using the refresh token,
			// and then sign a new request with the new access token
			if ($storage = self::$config['storage']['getRefresh']) {
				$client = new Client(self::URL_STUB);
				$params = array(
					'refresh_token' => $storage(),
					'client_id' => self::$config['clientId'],
					'client_secret' => self::$config['clientSecret'],
					'grant_type' => 'refresh_token',
				);
				try {
					$response = $client->post('token')->addPostFields($params)->send();
				} catch (Guzzle\Http\Exception\ClientErrorResponseException $e) {
					throw new InvalidRefresh;
				}
				foreach ($response->json() as $key => $val) {
					if ($key == 'access_token') {
						$storage = self::$config['storage']['token'];
						$storage($val);
					}
				}
				$newRequest = clone $event['request'];
				$newRequest = $newRequest->setHeader('Authorization', $this->_buildAuthorizationHeader())->send();
				$event['response'] = $newRequest;
			}
			else {
				throw new KeyExpired;
			}
			$event->stopPropagation();
		}
	}

	public function authURL($state="") {
		$params = array(
			'response_type' => 'code',
			'client_id' => self::$config['clientId'],
			'redirect_uri' => self::$config['redirectUri'],
			'scope' => self::$config['scope'],
			'access_type' => self::$config['type'],
			'approval_prompt' => self::$config['prompt']
		);
		if ($state) {
			$params['state'] = $state;
		}
		return self::AUTH_URL . '?' . http_build_query($params);
	}

	public function handleOauth() {
		if (\Input::get('error')) {
			throw new ClientDeniedException;
		}
		elseif (\Input::get('code')) {
			// Now go get our tokens!
			$client = new Client(self::URL_STUB);
			$params = array(
				'code' => \Input::get('code'),
				'client_id' => self::$config['clientId'],
				'client_secret' => self::$config['clientSecret'],
				'redirect_uri' => self::$config['redirectUri'],
				'grant_type' => 'authorization_code'
			);
			$response = $client->post('token')->addPostFields($params)->send();
			foreach ($response->json() as $key => $val) {
				if ($key == 'refresh_token') {
					$storage = self::$config['storage']['refresh'];
					$storage($val);
				}
				if ($key == 'access_token') {
					$storage = self::$config['storage']['token'];
					$storage($val);
				}
			}
			return true;
		}
		else {
			throw new UnexpectedInput;
		}
	}
}

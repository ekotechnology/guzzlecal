# Guzzlecal

Google Calendar Client that uses [Guzzle](http://guzzlephp.org) at its core.


## Features

### Authentication
* Helpers for OAuth2 URL creation
* Helpers for capturing OAuth2 authentication
* Requests sent through the Guzzle client are signed via plugin
  * If the OAuth2 settings specify an `offline` mode, expired access tokens can be automatically refreshed

### Calendar List
* List calendars that are available to the authenticated user
* Remove calendars from the listing

### Calendar
* Create new account sub-calendars
* View/Update metadata on existing calendars
* Delete account sub-calendars

### Events
* Create new events
	* Including recurring events
	* Add attendees/invitations
* View/Update metadata on events
* Delete single or recurring events

### Free/Busy Queries
* Allows you to query for times that are marked as `Busy` on the calendar

### Custom Requests
* If you don't want to use the response representations provided via Guzzlecal, we provide a passthru option that will just give you an OAuth2 authenticated Guzzle client that you can do whatever you want on.  If you're using `offline` mode, expired tokens will still be refreshed for you automatically.

## Installation
### Install via Composer
### Install via Git/Filesystem
> If you are going to use this method, it is suggested that you star the repository so that you can integrate any security patches or bug fixes as soon as possible.

## Use with Laravel 4
> This package was built for and within Laravel 4, although the goal is that it will function properly in other PHP projects.  See the section below for more info.

### Configuration
Guzzlecal ships with a Service Provider and Facade so that it can tie in very nicely with Laravel 4. Once you've installed Guzzlecal, configuring it for Laravel is pretty simple.  You just need to add an entry in for the Service Provider and the Facade.

From the base directory of your app, you need to go to the app configuration file which is found in app/config/app.php.  You'll need to add an entry in the `providers` array with a value of `Ekotechnology\Guzzlecal\GuzzlecalServiceProvider`.  This might make your `providers` array look something like this:

	'providers' => array(
		'Illuminate\Foundation\Providers\ArtisanServiceProvider',
		'Illuminate\Auth\AuthServiceProvider',
		'Illuminate\Cache\CacheServiceProvider',
		'Illuminate\Foundation\Providers\CommandCreatorServiceProvider',
		'Illuminate\Foundation\Providers\ComposerServiceProvider',
		...
		'Illuminate\Validation\ValidationServiceProvider',
		'Illuminate\View\ViewServiceProvider',
		'Illuminate\Workbench\WorkbenchServiceProvider',
		'Ekotechnology\Guzzlecal\GuzzlecalServiceProvider'

	),

Now, we just need to register the alias for the Facade.  So in the `aliases` array, you'll need to add an entry with the value of `Ekotechnology\Guzzlecal\Facades\GuzzlecalFacade`.  This might make your `aliases` array look like so:

	'aliases' => array(
		'App'             => 'Illuminate\Support\Facades\App',
		'Artisan'         => 'Illuminate\Support\Facades\Artisan',
		'Auth'            => 'Illuminate\Support\Facades\Auth',
		'Blade'           => 'Illuminate\Support\Facades\Blade',
		'Cache'           => 'Illuminate\Support\Facades\Cache',
		'ClassLoader'     => 'Illuminate\Foundation\ClassLoader',
		'Config'          => 'Illuminate\Support\Facades\Config',
		...
		'Route'           => 'Illuminate\Support\Facades\Route',
		'Schema'          => 'Illuminate\Support\Facades\Schema',
		'Session'         => 'Illuminate\Support\Facades\Session',
		'URL'             => 'Illuminate\Support\Facades\URL',
		'Validator'       => 'Illuminate\Support\Facades\Validator',
		'View'            => 'Illuminate\Support\Facades\View',
		'Guzzlecal'       => 'Ekotechnology\Guzzlecal\Facades\GuzzlecalFacade'
	),

### Example

## Use outside of Laravel 4
If you're not using Laravel 4, hopefully we can still get you up and running in your application.

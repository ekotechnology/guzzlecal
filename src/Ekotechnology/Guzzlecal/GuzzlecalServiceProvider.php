<?php namespace Ekotechnology\Guzzlecal;

use Illuminate\Support\ServiceProvider;

class GuzzlecalServiceProvider extends ServiceProvider {
	
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['guzzlecal'] = function($app) {
			return new Guzzlecal;
		};
	}
}
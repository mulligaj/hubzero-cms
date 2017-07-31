<?php
namespace Components\Drwho\Api\Controllers;

use Components\Drwho\Models\Season;
use Hubzero\Component\ApiController;
use Request;
use Route;
use App;

require_once(dirname(dirname(__DIR__)) . DS . 'models' . DS . 'show.php');

/**
 * API controller class for the show
 */
class Showv1_0 extends ApiController
{
	/**
	 * Display documentation for seasons API
	 *
	 * @apiMethod GET
	 * @apiUri    /drwho/seasons
	 * @return  void
	 */
	public function seasonsTask()
	{
		App::redirect(Request::base() . '/drwho/seasons');
	}

	/**
	 * Display documentation for characters API
	 *
	 * @apiMethod GET
	 * @apiUri    /drwho/characters
	 * @return  void
	 */
	public function charactersTask()
	{
		App::redirect(Request::base() . '/drwho/characters');
	}
}

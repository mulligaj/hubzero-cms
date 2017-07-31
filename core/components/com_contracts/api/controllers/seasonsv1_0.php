<?php
namespace Components\Drwho\Api\Controllers;

use Components\Drwho\Models\Season;
use Hubzero\Component\ApiController;
use Hubzero\Utility\Date;
use Exception;
use stdClass;
use Request;
use Route;
use Lang;
use App;

require_once dirname(dirname(__DIR__)) . '/models/show.php';

/**
 * API controller class for seasons
 */
class Seasonsv1_0 extends ApiController
{
	/**
	 * Display a list of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /drwho/seasons/list
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"name":          "start",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "sort",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 *      "default":       "created",
	 * 		"allowedValues": "created, title, alias, id, publish_up, publish_down, state"
	 * }
	 * @apiParameter {
	 * 		"name":          "sort_Dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @return  void
	 */
	public function listTask()
	{
		$response = new stdClass;
		$response->total = Season::all()
			->whereEquals('state', 1)
			->count();

		$record = Season::all()
			->whereEquals('state', 1);

		if ($limit = Request::getInt('limit', 20))
		{
			$record->limit($limit);
		}
		if ($start = Request::getInt('limitstart', 0))
		{
			$record->start($start);
		}
		if (($orderby  = Request::getWord('sort', 'title'))
		 && ($orderdir = Request::getWord('sortDir', 'ASC')))
		{
			$record->order($orderby, $orderdir);
		}

		$response->records = $record->rows()->toObject();

		if (count($response->records) > 0)
		{
			foreach ($response->records as $i => $entry)
			{
				$response->records[$i]->uri = Route::url('index.php?option=' . $this->_option . '&controller=characters&season=' . $entry->alias);
			}
		}

		$response->success = true;

		$this->send($response);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /drwho/seasons
	 * @apiParameter {
	 * 		"name":        "title",
	 * 		"description": "Entry title",
	 * 		"type":        "string",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "alias",
	 * 		"description": "Entry alias",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "created",
	 * 		"description": "Created timestamp (YYYY-MM-DD HH:mm:ss)",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     "now"
	 * }
	 * @apiParameter {
	 * 		"name":        "crated_by",
	 * 		"description": "User ID of entry creator",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     0
	 * }
	 * @apiParameter {
	 * 		"name":        "state",
	 * 		"description": "Published state (0 = unpublished, 1 = published)",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     0
	 * }
	 * @apiParameter {
	 * 		"name":        "premiere_date",
	 * 		"description": "Season Premiere timestamp (YYYY-MM-DD HH:mm:ss)",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     "now"
	 * }
	 * @apiParameter {
	 * 		"name":        "finale_date",
	 * 		"description": "Season finale timestamp (YYYY-MM-DD HH:mm:ss)",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     null
	 * }
	 * @return    void
	 */
	public function createTask()
	{
		$this->requiresAuthentication();
		$this->authorizeOrFail();

		$fields = array(
			'title'          => Request::getVar('title', null, 'post', 'none', 2),
			'alias'          => Request::getVar('alias', 0, 'post'),
			'created'        => Request::getVar('created', with(new Date('now'))->toSql(), 'post'),
			'created_by'     => Request::getInt('created_by', 0, 'post'),
			'state'          => Request::getInt('state', 0, 'post'),
			'doctor_id'      => Request::getInt('doctor_id', 0, 'post'),
			'premiere_date'  => Request::getVar('premiere_date', null, 'post'),
			'finale_date'    => Request::getVar('finale_date', null, 'post')
		);

		// Create object and store content
		$record = Season::oneOrNew(null)->set($fields);

		// Do the actual save
		if (!$record->save())
		{
			App::abort(500, Lang::txt('COM_DRWHO_ERROR_RECORD_CREATE_FAILED'));
		}

		$row = $record->toObject();
		$row->uri = Route::url($record->link());

		$this->send($row, 201);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /drwho/seasons/{id}
	 * @apiParameter {
	 * 		"name":        "id",
	 * 		"description": "Entry identifier",
	 * 		"type":        "integer",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @return    void
	 */
	public function readTask()
	{
		$id = Request::getInt('id', 0);

		// Error checking
		if (empty($id))
		{
			App::abort(404, Lang::txt('COM_DRWHO_ERROR_MISSING_ID'));
		}

		try
		{
			$record = Season::oneOrFail($id);
		}
		catch (Hubzero\Error\Exception\RuntimeException $e)
		{
			App::abort(404, Lang::txt('COM_DRWHO_ERROR_RECORD_NOT_FOUND'));
		}

		$row = $record->toObject();
		$row->uri = Route::url($record->link());

		$this->send($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /drwho/seasons/{id}
	 * @apiParameter {
	 * 		"name":        "id",
	 * 		"description": "Entry identifier",
	 * 		"type":        "integer",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "title",
	 * 		"description": "Entry title",
	 * 		"type":        "string",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "alias",
	 * 		"description": "Entry alias",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "created",
	 * 		"description": "Created timestamp (YYYY-MM-DD HH:mm:ss)",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     "now"
	 * }
	 * @apiParameter {
	 * 		"name":        "crated_by",
	 * 		"description": "User ID of entry creator",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     0
	 * }
	 * @apiParameter {
	 * 		"name":        "state",
	 * 		"description": "Published state (0 = unpublished, 1 = published)",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     0
	 * }
	 * @apiParameter {
	 * 		"name":        "premiere_date",
	 * 		"description": "Season Premiere timestamp (YYYY-MM-DD HH:mm:ss)",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     "now"
	 * }
	 * @apiParameter {
	 * 		"name":        "finale_date",
	 * 		"description": "Season finale timestamp (YYYY-MM-DD HH:mm:ss)",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     null
	 * }
	 * @return    void
	 */
	public function updateTask()
	{
		$this->requiresAuthentication();
		$this->authorizeOrFail();

		$id = Request::getInt('id');

		if (!$id)
		{
			App::abort(404, Lang::txt('COM_DRWHO_ERROR_MISSING_ID'));
		}

		$record = Season::oneOrFail($id);

		$fields = array(
			'title'         => Request::getVar('title', $record->get('title'), 'post', 'none', 2),
			'alias'         => Request::getVar('alias', $record->get('alias'), 'post'),
			'created'       => Request::getVar('created', $record->get('created'), 'post'),
			'created_by'    => Request::getInt('created_by', $record->get('created_by'), 'post'),
			'state'         => Request::getInt('state', $record->get('state'), 'post'),
			'doctor_id'     => Request::getInt('doctor_id', $record->get('doctor_id'), 'post'),
			'premiere_date' => Request::getVar('premiere_date', $record->get('premiere_date'), 'post'),
			'finale_date'   => Request::getVar('finale_date', $record->get('finale_date'), 'post')
		);

		// Create object and store content
		$record->set($fields);

		// Do the actual save
		if (!$record->save())
		{
			App::abort(500, Lang::txt('COM_DRWHO_ERROR_RECORD_UPDATE_FAILED'));
		}

		$row = $record->toObject();
		$row->uri = Route::url($record->link());

		$this->send($row, 201);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /drwho/seasons/{id}
	 * @apiParameter {
	 * 		"name":        "id",
	 * 		"description": "Entry identifier",
	 * 		"type":        "integer",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @return    void
	 */
	public function deleteTask()
	{
		$this->requiresAuthentication();
		$this->authorizeOrFail();

		$id = Request::getInt('id');

		if (!$id)
		{
			App::abort(404, Lang::txt('COM_DRWHO_ERROR_MISSING_ID'));
		}

		// Create object and store content
		$record = Season::oneOrFail($id);

		// Do the actual save
		if (!$record->destroy())
		{
			App::abort(500, Lang::txt('COM_DRWHO_ERROR_RECORD_DELETE_FAILED'));
		}

		$this->send(null, 204);
	}

	/**
	 * Checks to ensure appropriate authorization
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	private function authorizeOrFail()
	{
		// Make sure action can be performed
		if (!User::authorise('core.manage', $this->_option))
		{
			App::abort(401, Lang::txt('COM_DRWHO_ERROR_UNAUTHORIZED'));
		}

		return true;
	}
}

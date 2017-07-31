<?php
namespace Components\Drwho\Api\Controllers;

use Components\Drwho\Models\Character;
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
 * API controller class for characters
 */
class Charactersv1_0 extends ApiController
{
	/**
	 * Display a list of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /drwho/characters/list
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
	 * @apiParameter {
	 * 		"name":          "season",
	 * 		"description":   "ID of the season to filter by",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @return  void
	 */
	public function listTask()
	{
		$response = new stdClass;
		$response->total = Character::all()
			->whereEquals('state', 1)
			->count();

		$record = Character::all()
			->whereEquals('state', 1);

		if ($limit = Request::getInt('limit', 20))
		{
			$record->limit($limit);
		}
		if ($start = Request::getInt('limitstart', 0))
		{
			$record->start($start);
		}
		if (($orderby  = Request::getWord('sort', 'name'))
		 && ($orderdir = Request::getWord('sortDir', 'ASC')))
		{
			$record->order($orderby, $orderdir);
		}

		$season = Request::getInt('season', 0);

		$response->records = $record->rows()->toObject();

		if (count($response->records) > 0)
		{
			foreach ($response->records as $i => $entry)
			{
				$response->records[$i]->uri = Route::url('index.php?option=' . $this->_option . '&controller=characters&task=view&id=' . $entry->id);
			}
		}

		$response->success = true;

		$this->send($response);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /drwho/characters
	 * @apiParameter {
	 * 		"name":        "name",
	 * 		"description": "Name",
	 * 		"type":        "string",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "bio",
	 * 		"description": "Biography",
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
	 * 		"name":        "doctor",
	 * 		"description": "Is the doctor?",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     "0"
	 * }
	 * @apiParameter {
	 * 		"name":        "friend",
	 * 		"description": "Is a friend of the doctor?",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     "0"
	 * }
	 * @apiParameter {
	 * 		"name":        "enemy",
	 * 		"description": "Is an enemy of the doctor?",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     "0"
	 * }
	 * @apiParameter {
	 * 		"name":        "species",
	 * 		"description": "Species of the character",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     "???"
	 * }
	 * @return    void
	 */
	public function createTask()
	{
		$this->requiresAuthentication();
		$this->authorizeOrFail();

		$fields = array(
			'name'       => Request::getVar('name', null, 'post', 'none', 2),
			'bio'        => Request::getVar('bio', null, 'post', 'none', 2),
			'created'    => Request::getVar('created', with(new Date('now'))->toSql(), 'post'),
			'created_by' => Request::getInt('created_by', 0, 'post'),
			'state'      => Request::getInt('state', 0, 'post'),
			'doctor'     => Request::getInt('doctor', 0, 'post'),
			'friend'     => Request::getInt('friend', 0, 'post'),
			'enemy'      => Request::getInt('enemy', 0, 'post'),
			'doctor'     => Request::getVar('species', '???', 'post')
		);

		// Create object and store content
		$record = Character::oneOrNew(null)->set($fields);

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
	 * @apiUri    /drwho/characters/{id}
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
			$record = Character::oneOrFail($id);
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
	 * @apiUri    /drwho/characters/{id}
	 * @apiParameter {
	 * 		"name":        "id",
	 * 		"description": "Entry identifier",
	 * 		"type":        "integer",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "name",
	 * 		"description": "Name",
	 * 		"type":        "string",
	 * 		"required":    true,
	 * 		"default":     null
	 * }
	 * @apiParameter {
	 * 		"name":        "bio",
	 * 		"description": "Biography",
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
	 * 		"name":        "doctor",
	 * 		"description": "Is the doctor?",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     "0"
	 * }
	 * @apiParameter {
	 * 		"name":        "friend",
	 * 		"description": "Is a friend of the doctor?",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     "0"
	 * }
	 * @apiParameter {
	 * 		"name":        "enemy",
	 * 		"description": "Is an enemy of the doctor?",
	 * 		"type":        "integer",
	 * 		"required":    false,
	 * 		"default":     "0"
	 * }
	 * @apiParameter {
	 * 		"name":        "species",
	 * 		"description": "Species of the character",
	 * 		"type":        "string",
	 * 		"required":    false,
	 * 		"default":     "???"
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

		$fields = array(
			'name'       => Request::getVar('name', $record->get('name'), 'post', 'none', 2),
			'bio'        => Request::getVar('bio', $record->get('bio'), 'post', 'none', 2),
			'created'    => Request::getVar('created', $record->get('created'), 'post'),
			'created_by' => Request::getInt('created_by', $record->get('created_by'), 'post'),
			'state'      => Request::getInt('state', $record->get('state', 1), 'post'),
			'doctor'     => Request::getInt('doctor', $record->get('doctor', 0), 'post'),
			'friend'     => Request::getInt('friend', $record->get('friend', 0), 'post'),
			'enemy'      => Request::getInt('enemy', $record->get('enemy', 0), 'post'),
			'doctor'     => Request::getVar('species', $record->get('species', '???'), 'post')
		);

		// Create object and store content
		$record = Character::oneOrFail($id)->set($fields);

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
	 * @apiUri    /drwho/characters/{id}
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
		$record = Character::oneOrFail($id);

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

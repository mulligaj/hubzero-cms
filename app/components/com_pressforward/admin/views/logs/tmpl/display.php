<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('log');

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_LOGS'));
if ($canDo->get('core.admin'))
{
	Toolbar::preferences($this->option);
	Toolbar::spacer();
}
Toolbar::appendButton('Link', 'help', 'help', 'https://github.com/PressForward/pressforward/wiki');


// Default log location is in the uploads directory
if (!defined('PF_DEBUG_LOG'))
{
	$log_path = Config::get('log_path') . '/pressforward.log';
}
else
{
	$log_path = PF_DEBUG_LOG;
}
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">

	<div class="wrap">
		<h2>Current Log</h2>

		<p>Does not update in real time.</p>
		<p>Total Current Feed Items:
		<?php
			echo Components\PressForward\Models\Post::items()->whereEquals('post_status', 'publish')->total();
		?><br />
		<?php
			echo 'Month to date Feed Items: ' . Components\PressForward\Models\Post::items()
				->where('post_date_gmt', '>=', Date::format('Y-m-01 00:00:00'))
				->total();
			echo '<br />Last month Feed Items: ' . Components\PressForward\Models\Post::items()
				->where('post_date_gmt', '>=', Date::modify('-1 Month')->format('Y-m-01 00:00:00'))
				->where('post_date_gmt', '<', Date::format('Y-m-01 00:00:00'))
				->total();
		?>
		</p>
		<p>Total Current Nominations:
		<?php
			echo Components\PressForward\Models\Post::nominations()->whereEquals('post_status', 'draft')->total();
			echo '<br />Month to date Nominations: ' . Components\PressForward\Models\Post::nominations()
				->whereEquals('post_status', 'draft')
				->where('post_date_gmt', '>=', Date::format('Y-m-01 00:00:00'))
				->total();
			echo '<br />Last month Nominations: ' . Components\PressForward\Models\Post::nominations()
				->whereEquals('post_status', 'draft')
				->where('post_date_gmt', '>=', Date::modify('-1 Month')->format('Y-m-01 00:00:00'))
				->where('post_date_gmt', '<', Date::format('Y-m-01 00:00:00'))
				->total();
		?>
		</p>
		<p>Total Actions Taken:
		<?php
			echo Components\PressForward\Models\Relationship::all()->total();
		?>
		</p>
		<p>Total Nominations Published:
		<?php
			echo Components\PressForward\Models\Post::nominations()->whereEquals('post_status', 'publish')->total();
		?>
		</p>
		<p>Total Retrieval Chunks Begun This:
		<?php
			pf_iterate_cycle_state('retrieval_chunks_begun', false, true);
		?>
		</p>
		<p>Total Retrieval Cycles Begun This:
		<?php
			pf_iterate_cycle_state('retrieval_cycles_begun', false, true);
		?>
		</p>
		<p>Total Retrieval Cycles Ended This:
		<?php
			pf_iterate_cycle_state('retrieval_cycles_ended', false, true);
		?>
		</p>
		<br /><br />
		<?php
			if (file_exists($log_path))
			{
				echo '<pre>';
				echo file_get_contents($log_path);
				echo '</pre>';
			}
			else
			{
				echo "The log does not exist.";
			}
		?>
	</div>

	<input type="hidden" name="option" value="<?php echo $this->option ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />

	<?php echo Html::input('token'); ?>
</form>

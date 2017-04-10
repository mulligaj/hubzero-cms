<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('feed');

// Toolbar is a helper class to simplify the creation of Toolbar 
// titles, buttons, spacers and dividers in the Admin Interface.
//
// Here we'll had the title of the component and options
// for saving based on if the user has permission to
// perform such actions. Everyone gets a cancel button.

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_FEEDS') . ': ' . Lang::txt('JACTION_CREATE'));
if ($canDo->get('core.create'))
{
	Toolbar::save('subscribe');
	Toolbar::spacer();
}
Toolbar::cancel();
Toolbar::spacer();
Toolbar::help('feeds');

Html::behavior('switcher', 'submenu');
?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}

	submitform(pressbutton);
}
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">
	<nav role="navigation" class="sub-navigation">
		<div id="submenu-box">
			<div class="submenu-box">
				<div class="submenu-pad">
					<ul id="submenu" class="member-nav">
						<li><a href="#" onclick="return false;" id="subscribe" class="active"><?php echo Lang::txt('Subscribe to Feeds'); ?></a></li>
						<li><a href="#" onclick="return false;" id="opml"><?php echo Lang::txt('OPML as Feed'); ?></a></li>
						<li><a href="#" onclick="return false;" id="alerts"><?php echo Lang::txt('Alerts'); ?></a></li>
					</ul>
					<div class="clr"></div>
				</div>
			</div>
			<div class="clr"></div>
		</div>
	</nav><!-- / .sub-navigation -->

	<div id="member-document">
		<div id="page-subscribe" class="tab">
			<?php echo $this->loadTemplate('subscribe'); ?>
		</div>

		<div id="page-opml" class="tab">
			<?php echo $this->loadTemplate('opml'); ?>
		</div>

		<div id="page-alerts" class="tab">
			<?php echo $this->loadTemplate('alerts'); ?>
		</div>
	</div>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="import" />

	<?php echo Html::input('token'); ?>
</form>
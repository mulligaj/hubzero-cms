<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('tools');

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_TOOLS'));
if ($canDo->get('core.admin'))
{
	Toolbar::preferences($this->option);
	Toolbar::spacer();
}
Toolbar::appendButton('Link', 'help', 'help', 'https://github.com/PressForward/pressforward/wiki');

Html::behavior('switcher', 'submenu2');

$this->css('pressforward.css');
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">
	<nav role="navigation" class="sub-navigation">
		<div id="submenu2-box">
			<div class="submenu-box">
				<div class="submenu-pad">
					<ul id="submenu2" class="member-nav">
						<li><a href="#" onclick="return false;" id="bookmarklet" class="active"><?php echo Lang::txt('Bookmarklet'); ?></a></li>
						<li><a href="#" onclick="return false;" id="debug"><?php echo Lang::txt('Debug and Refresh'); ?></a></li>
						<li><a href="#" onclick="return false;" id="retrieval"><?php echo Lang::txt('Retrieval Status'); ?></a></li>
						<li><a href="#" onclick="return false;" id="opml"><?php echo Lang::txt('OPML Link'); ?></a></li>
					</ul>
					<div class="clr"></div>
				</div>
			</div>
			<div class="clr"></div>
		</div>
	</nav><!-- / .sub-navigation -->

	<div id="tools-document">
		<div id="page-bookmarklet" class="tab">
			<?php echo $this->loadTemplate('bookmarklet'); ?>
		</div>

		<div id="page-debug" class="tab">
			<?php echo $this->loadTemplate('debug'); ?>
		</div>

		<div id="page-retrieval" class="tab">
			<?php echo $this->loadTemplate('retrieval'); ?>
		</div>

		<div id="page-opml" class="tab">
			<?php echo $this->loadTemplate('opml'); ?>
		</div>
	</div>

	<input type="hidden" name="option" value="<?php echo $this->option ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />

	<?php echo Html::input('token'); ?>
</form>

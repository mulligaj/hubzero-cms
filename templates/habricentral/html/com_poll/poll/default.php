<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

$pagetitle = $this->params->get('page_title');
$pagetitle = ($pagetitle) ? $pagetitle : JText::_('Polls');

$app = JFactory::getApplication();
$pathway = $app->getPathway();

if (count($pathway->getPathWay()) <= 0) {
	$pathway->addItem($this->escape($pagetitle),'index.php?option=com_polls');
} else {
	$pathway->setItemName(0,$pagetitle);
}
if (trim($this->poll->title) != '') {
	$pathway->addItem($this->poll->title,'index.php?option=com_polls&id='.$this->poll->id);
}
?>

<?php JHTML::_('stylesheet', 'poll_bars.css', 'components/com_poll/assets/'); ?>

<form action="index.php" method="post" name="poll" id="poll">
<?php if ($this->params->get( 'show_page_title')) : ?>
	<div id="content-header" class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
		<h2><?php echo $this->poll->title ? $this->escape($this->poll->title) : $pagetitle; ?></h2>
	</div>
<?php endif; ?>
	<div class="main section">
		<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
			<label for="id">
				<?php echo JText::_('Select Poll'); ?>
				<?php echo $this->lists['polls']; ?>
			</label>
		</div>

		<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
			<?php echo $this->loadTemplate('graph'); ?>
		</div>
	</div><!-- / .main section -->
</form>
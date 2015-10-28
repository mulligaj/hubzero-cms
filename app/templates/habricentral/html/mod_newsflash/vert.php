<?php defined('_JEXEC') or die('Restricted access'); ?>
<table class="newsflash <?php echo $params->get( 'moduleclass_sfx' ); ?>">
	<tbody>
<?php foreach ($list as $item) :
	modNewsFlashHelper::renderItem($item, $params, $access);
?>
<?php endforeach; ?>
	</tbody>
</table>
<?php if ($params->get('readmore')) { ?>
<p class="more"><a href="/news">More news &rsaquo;</a></p>
<?php } ?>
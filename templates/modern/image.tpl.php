	<h2><?php echo $sg->image->name(); ?></h2>
	<h4><?php echo $sg->image->byArtistText(); ?></h4>
</div>
<div id="sgContent">  

	<div class="sgImageWrapper">
		<div class="sgImageBox" style="width: <?php echo $sg->image->width(); ?>px; ">
			<?php echo $sg->image->imageHTML() ?>
			<a class="thumb" style="width: <?php echo $sg->image->width().'px;" href="'.$sg->image->parent->URL().'"><span>'.$sg->translator->_g("image|Thumbnails").'</span></a>'; ?>
			<?php if($sg->image->hasPrev()) {echo '<a class="prev" style="height:'.$sg->image->height().'px;" href="'.$sg->image->prevURL().'"><span style="margin-top:'.floor(($sg->image->height())/2).'px;">'.$sg->translator->_g("image|Previous").'</span></a>'; } ?>
			<?php if($sg->image->hasNext()) {echo '<a class="next" style="height:'.$sg->image->height().'px;" href="'.$sg->image->nextURL().'"><span style="margin-top:'.floor(($sg->image->height())/2).'px;">'.$sg->translator->_g("image|Next").'</span></a>'; } ?>
		</div>
	</div>

    <?php if ($sg->config->show_fullsizeURL == 1) { ?>
        <p class="sgFullsize"><a href="<?php echo $sg->image->realURL(); ?>"><?php echo $sg->translator->_g("image|View Full Size Image"); ?></a> <a href="<?php echo $sg->image->realURL(); ?>" target="_new">[+]</a></p>
    <?php } ?>

	<div class="sgDetailsList">
		<dl>
			<?php foreach($sg->image->detailsArray() as $key => $value): ?>
				<dt><?php echo $key; ?>:</dt><dd><?php echo $value; ?></dd>
			<?php endforeach; ?>
		</dl>
	</div>

	<div class="sgPreview">
		<?php echo $sg->previewThumbnails(); ?>
		<p>
			<?php if($sg->image->hasPrev()) echo "&#8249; ".$sg->image->prevLink()." &nbsp;&nbsp; "; ?>
			<?php echo $sg->image->parentLink(); ?>
			<?php if($sg->image->hasNext()) echo " &nbsp;&nbsp; ".$sg->image->nextLink()." &#8250;"; ?>	
		</p>
	</div>
</div>
<?php echo $sg->imageMap() ?>
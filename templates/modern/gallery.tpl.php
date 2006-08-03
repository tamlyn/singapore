	<h2><?php echo $sg->gallery->name(); ?></h2>
	<h4><?php echo $sg->gallery->byArtistText(); ?></h4>
</div>
<div id="sgMain-nav">
	<?php if($sg->gallery->hasPrev()) echo $sg->gallery->prevLink()." | "; ?> 
	<?php if(!$sg->gallery->isRoot()) echo $sg->gallery->parentLink(); ?> 
	<?php if($sg->gallery->hasNext()) echo " | ".$sg->gallery->nextLink(); ?>
</div>

<div id="sgContent">
	<p class="sgTab"><?php echo $sg->galleryTab(); ?></p>
  
    <?php for($index = $sg->gallery->startat; $index < $sg->gallery->galleryCountSelected()+$sg->gallery->startat; $index++): ?> 
      	<div class="sgGallery <?php if ($sg->config->thumb_float_gallery == 1) { echo 'sgGalleryFloat';} ?>">
        	<?php echo $sg->gallery->galleries[$index]->thumbnailLink(); ?>      
			<h3 title="View Gallery"><?php echo $sg->gallery->galleries[$index]->nameLink(); ?></h3>
        	<p><?php echo $sg->gallery->galleries[$index]->summary(); ?></p>
        	<p class="sgCount"><?php echo $sg->gallery->galleries[$index]->itemCountText(); ?></p>
    		<?php if ($sg->config->show_slideshowURL == 1) { ?>
    		<?php if ($sg->gallery->galleries[$index]->hasImages()) { ?>
				<p><a href="<?php echo $sg->gallery->galleries[$index]->images[0]->URL(); if (!strstr($sg->gallery->galleries[$index]->images[0]->URL(), '?')) { echo '?'; } else { echo '&'; } ?>action=slideshow">View Slideshow</a></p>
    		<?php } ?>
			<?php } ?>
			<div class="sgFoot"></div>		
      	</div>
		<?php if($index % 2) {echo '<div class="clear"></div>';} ?>
    <?php endfor; ?> 

	<p class="sgTab"><?php echo $sg->galleryTab(); ?></p>
  
	<div class="sgDetailsList">
		<dl>
			<?php foreach($sg->gallery->detailsArray() as $key => $value): ?>
				<dt><?php echo $key; ?>:</dt><dd><?php echo $value; ?></dd>
			<?php endforeach; ?>
		</dl>
	</div>

</div>
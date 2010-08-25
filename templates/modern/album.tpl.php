	<h2><?php echo $sg->gallery->name(); ?></h2>
	<h4><?php echo $sg->gallery->byArtistText(); ?></h4>
</div>

<div id="sgMain-nav">
</div>

<div id="sgContent">
	<p class="sgTab"><?php echo $sg->galleryTab(); ?></p>

	<div class="sgAlbum">
    	<?php for($index = $sg->gallery->startat; $index < $sg->gallery->imageCountSelected()+$sg->gallery->startat; $index++): ?>
      		<?php echo $sg->gallery->images[$index]->thumbnailLink(); ?> 
    	<?php endfor; ?>
		<div class="sgFoot"></div>
	</div>

	<p class="sgTab"><?php echo $sg->galleryTab(); ?></p>

	<div class="sgDetailsList">
		<dl>
			<?php foreach($sg->gallery->detailsArray() as $key => $value): ?>
				<dt><?php echo $key; ?>:</dt><dd><?php echo $value; ?></dd>
			<?php endforeach; ?>
		</dl>
	</div>

</div>
 

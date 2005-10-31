<p class="sgNavBar sgTopNavBar">
<?php echo $sg->previewThumbnails();?>
<br />
<?php if($sg->image->hasPrev()) echo $sg->image->prevLink()." | "; ?> 
<?php echo $sg->image->parentLink(); ?> 
<?php if($sg->image->hasNext()) echo " | ".$sg->image->nextLink(); ?>
</p>
<h2 class="sgTitle"><?php echo $sg->image->name(); ?></h2>
  
<div class="sgContainer">
  <table class="sgContent"><tr><td><div class="sgContent">
    <?php echo $sg->image->imageHTML() ?>
  </div></td></tr></table>
</div>
  
<div class="sgNavBar sgTopNavBar"><p>
<?php if($sg->image->hasPrev()) echo $sg->image->prevLink()." | "; ?> 
<?php echo $sg->image->parentLink(); ?> 
<?php if($sg->image->hasNext()) echo " | ".$sg->image->nextLink(); ?>
</p></div>

<p><em><?php echo $sg->image->name() ?></em><?php echo $sg->image->byArtistText() ?></p>
  
<p>
<?php foreach($sg->image->detailsArray() as $key => $value): ?>
<strong><?php echo $key ?>:</strong> <?php echo $value ?><br />
<?php endforeach; ?>
</p>

<?php echo $sg->imageMap() ?>
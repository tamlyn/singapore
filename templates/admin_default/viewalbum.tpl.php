<p class="sgNavBar sgTopNavBar">
<?php if($sg->gallery->hasPrev()) echo $sg->gallery->prevLink()." | "; ?> 
<?php if(!$sg->gallery->isRoot()) echo $sg->gallery->parentLink(); ?> 
<?php if($sg->gallery->hasNext()) echo " | ".$sg->gallery->nextLink(); ?>
</p>

<h2 class="sgTitle"><?php echo $sg->gallery->name(); ?></h2>

<div class="sgContainer">
  <div class="sgTab"><?php echo $sg->galleryTab()?></div>
  <div class="sgContent">
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
    <input type="hidden" name="action" value="multi" />
    <input type="hidden" name="gallery" value="<?php echo $sg->gallery->idEntities(); ?>" />
    <div class="sgGallery">
      <?php echo $sg->translator->_g("With selected:"); ?>
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->translator->_g("Copy or move"); ?>" />
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->translator->_g("Delete"); ?>" />
    </div>
    <?php for($index = $sg->gallery->startat; $index < $sg->gallery->imageCountSelected()+$sg->gallery->startat; $index++): ?> 
    <div class="sgThumbnail">
      <input type="checkbox" class="sgImageCheckbox checkbox" name="sgImages[]" value="<?php echo $sg->gallery->images[$index]->idEntities(); ?>" />
      <table><tr><td>
        <?php echo $sg->gallery->images[$index]->thumbnailLink() ?> 
      </td></tr></table>
    </div>
    <?php endfor; ?>
    <div class="stretcher"></div>
  </div>
  </form>
</div>  
  
<p>
<?php foreach($sg->gallery->detailsArray() as $key => $value): ?>
  <strong><?php echo $key ?>:</strong> <?php echo $value ?><br />
<?php endforeach; ?>
</p>

<h2 class="sgTitle"><?php echo $sg->galleryName(); ?></h2>
<h4 class="sgSubTitle"><?php echo $sg->galleryByArtist(); ?></h4>

<div class="sgContainer">
  <div class="sgTab"><?php echo $sg->galleryTab()?></div>
  <div class="sgContent">
    <?php /* <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
    <input type="hidden" name="action" value="multiimage" />
    <input type="hidden" name="gallery" value="<?php echo $sg->galleryIdEncoded(); ?>" />
    <div class="sgGallery">
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->i18n->_g("Delete selected"); ?>" />
      <input type="submit" class="button" name="subaction" value="<?php echo $sg->i18n->_g("Move selected"); ?>" />
      <select name="sgMoveTo">
        <option value=""><?php echo $sg->i18n->_g("Select gallery..."); ?></option>
      </select>
    </div> */ ?>
    <?php for($index = $sg->startat; $index < $sg->gallerySelectedImagesCount()+$sg->startat; $index++): ?> 
    <div class="sgThumbnail">
      <?php /* <input type="checkbox" class="sgImageCheckbox checkbox" name="sgImages[<?php echo urlencode($sg->gallery->images[$index]->filename) ?>]" /> */ ?>
      <table><tr><td>
        <?php echo $sg->imageThumbnailLinked($index) ?> 
      </td></tr></table>
    </div>
    <?php endfor; ?>
    <div class="stretcher"></div>
  </div>
  <?php /* </form> */ ?>
</div>  
  
<p>
<?php foreach($sg->galleryDetailsArray() as $key => $value): ?>
  <strong><?php echo $key ?>:</strong> <?php echo $value ?><br />
<?php endforeach; ?>
</p>

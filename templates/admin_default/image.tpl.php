<div class="sgNavBar"><p>
  <?php echo $sg->imagePreviewThumbnails(); ?>
<br />
<?php echo $sg->imagePrevLink(); ?>
<?php echo $sg->imageParentLink(); ?>
<?php echo $sg->imageNextLink(); ?>
</p></div>
  
<h2 class="sgTitle"><?php echo $sg->imageName(); ?></h2>
<h4 class="sgSubTitle"><?php echo $sg->imageByArtist(); ?></h4>
  
<div class="sgContainer">
  <table class="sgContent"><tr><td><div class="sgContent">
    <?php echo $sg->image() ?>
  </div></td></tr></table>
</div>
  
<div class="sgNavBar"><p>
<?php echo $sg->imagePrevLink(); ?>
<?php echo $sg->imageParentLink(); ?>
<?php echo $sg->imageNextLink(); ?>
</p></div>

<p><em><?php echo $sg->imageName() ?></em><?php echo $sg->imageByArtist() ?></p>
  
<p>
<?php foreach($sg->imageDetailsArray() as $key => $value): ?>
<strong><?php echo $key ?>:</strong> <?php echo $value ?><br />
<?php endforeach; ?>
</p>

<?php echo $sg->imageMap() ?>
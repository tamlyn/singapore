<p class="sgNavBar sgTopNavBar">
<?php if($sg->gallery->hasPrev()) echo $sg->gallery->prevLink()." | "; ?> 
<?php if(!$sg->gallery->isRoot()) echo $sg->gallery->parentLink(); ?> 
<?php if($sg->gallery->hasNext()) echo " | ".$sg->gallery->nextLink(); ?>
</p>

<h2 class="sgTitle"><?php echo $sg->gallery->name(); ?></h2>
<h4 class="sgSubTitle"><?php echo $sg->gallery->byArtistText(); ?></h4>

<div class="sgShadow"><table class="sgShadow" cellspacing="0">
  <tr>
    <td class="tabl"></td>
    <td class="tabm">
    <div><?php echo $sg->galleryTab(); ?></div>
    </td>
    <td class="tabr"></td>
  </tr>
  <tr>
    <td class="tl"></td>
    <td class="tm"></td>
    <td class="tr"></td>
  </tr>
  <tr>
    <td class="ml"></td>
    <td class="mm">
  
    <?php for($index = $sg->gallery->startat; $index < $sg->gallery->galleryCountSelected()+$sg->gallery->startat; $index++): ?> 
    <div class="sgGallery"><table class="sgGallery"><tr valign="top">
      <td class="sgGalleryThumb">
        <?php echo $sg->gallery->galleries[$index]->thumbnailLink(); ?> 
      </td>
      <td>
        <p><strong><?php echo $sg->gallery->galleries[$index]->nameLink(); ?></strong></p>
        <p><?php echo $sg->gallery->galleries[$index]->summary(); ?></p>
        <p>[<?php echo $sg->gallery->galleries[$index]->itemCountText(); ?>]</p>
      </td>
    </tr></table></div>
    <?php endfor; ?> 

    </td>
    <td class="mr"></td>
  </tr>
  <tr>
    <td class="bl"></td>
    <td class="bm"></td>
    <td class="br"></td>
  </tr>
</table></div>

  
  
<p class="sgDetailsList">
<?php foreach($sg->gallery->detailsArray() as $key => $value): ?>
<strong><?php echo $key; ?>:</strong> <?php echo $value; ?><br />
<?php endforeach; ?>
</p>
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
  
    <?php for($index = $sg->gallery->startat; $index < $sg->gallery->imageCountSelected()+$sg->gallery->startat; $index++): ?>
    <div class="sgThumbnail">
      <div class="sgThumbnailContent">
        <img class="borderTL" src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/slide-tl.gif" alt="" />
        <img class="borderTR" src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/slide-tr.gif" alt="" />
        
        <table><tr><td>
          <?php echo $sg->gallery->images[$index]->thumbnailLink(); ?> 
        </td></tr></table>
        
        <div class="roundedCornerSpacer">&nbsp;</div>
      </div>
      <div class="bottomCorners">
        <img class="borderBL" src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/slide-bl.gif" alt="" />
        <img class="borderBR" src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/slide-br.gif" alt="" />
      </div>
    </div>
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

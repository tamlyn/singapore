<h2 class="sgTitle"><?php echo $sg->gallery->name(); ?></h2>
<h4 class="sgSubTitle"><?php echo $sg->gallery->byArtistText(); ?></h4>

<div class="sgShadow"><table class="sgShadow" cellspacing="0">
  <tr>
    <td><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template?>images/shadow-tabl.gif" alt="" /></td>
    <td class="tabm"><table class="sgShadowTab" cellspacing="0"><tr><td>
  
    <?php echo $sg->galleryTab(); ?>
  
    </td><td><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/shadow-tabr.gif" alt="" /></td></tr></table></td>
    <td class="tabr"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="tl"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" width="32" height="16" alt="" /></td>
    <td class="tm"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="tr"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="ml"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
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
    <td class="mr"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="bl"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="bm"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="br"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" width="32" height="32" alt="" /></td>
  </tr>
</table></div>
  
<p class="sgDetailsList">
<?php foreach($sg->gallery->detailsArray() as $key => $value): ?>
<strong><?php echo $key; ?>:</strong> <?php echo $value; ?><br />
<?php endforeach; ?>
</p>

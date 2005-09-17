<p class="sgNavBar sgTopNavBar">
<?php echo $sg->previewThumbnails();?>
<br />
<?php echo sgUtils::conditional($sg->image->prevLink(),"%s | "); ?> 
<?php echo $sg->image->parentLink(); ?> 
<?php echo sgUtils::conditional($sg->image->nextLink()," | %s"); ?> 
</p>
<h2 class="sgTitle"><?php echo $sg->image->name(); ?></h2>
<h4 class="sgSubTitle"><?php echo $sg->image->byArtistText(); ?></h4>
  
<div class="sgShadow"><table class="sgShadow" cellspacing="0">
  <tr>
    <td class="tl"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" width="32" height="16" alt="" /></td>
    <td class="tm"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="tr"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="ml"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="mm">
  
    <?php echo $sg->image->imageHTML() ?> 
  
    </td>
    <td class="mr"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="bl"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="bm"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="br"><img src="<?php echo $sg->config->base_url.$sg->config->pathto_current_template ?>images/blank.gif" width="32" height="32" alt="" /></td>
  </tr>
</table></div>
  
<p class="sgNavBar sgBottomNavBar">
<?php echo sgUtils::conditional($sg->image->prevLink(),"%s | "); ?> 
<?php echo $sg->image->parentLink(); ?> 
<?php echo sgUtils::conditional($sg->image->nextLink()," | %s"); ?> 
</p>

<h4 class="sgNameByArtist"><em><?php echo $sg->image->name() ?></em><?php echo $sg->image->byArtistText() ?></h4>
  
<p class="sgDetailsList">
<?php foreach($sg->image->detailsArray() as $key => $value): ?>
<strong><?php echo $key ?>:</strong> <?php echo $value ?><br />
<?php endforeach; ?>
</p>

<?php echo $sg->imageMap() ?>

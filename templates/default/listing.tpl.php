<h1 style="margin-bottom: 0px"><?php echo $sg->galleryName()?></h1>
<p style="margin-top: 0;"><?php echo $sg->galleryByArtist()?></p>

<div class="sgShadow"><table class="sgShadow" cellspacing="0">
  <tr>
    <td><img src="<?php echo $sg->config->pathto_current_template?>images/shadow-tabl.gif" alt="" /></td>
    <td class="tabm"><table class="sgShadowTab" cellspacing="0"><tr><td>
  
    <?php echo $sg->galleryTab()?>
  
    </td><td><img src="<?php echo $sg->config->pathto_current_template?>images/shadow-tabr.gif" alt="" /></td></tr></table></td>
    <td class="tabr"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="tl"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" width="32" height="16" alt="" /></td>
    <td class="tm"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="tr"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="ml"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="mm">
    
    <?php foreach($sg->gallerySelectedGalleriesArray() as $index => $gal): ?>
    <div class="sgGallery"><table class="sgGallery">
    <tr valign="top">
      <td class="sgGallery">
        <?php echo $sg->galleryThumbnailLinked($index+$sg->startat) ?>
      </td>
      <td><p><strong><a href="<?php echo $sg->config->base_url ?>gallery=<?php echo urlencode($gal->id) ?>"><?php echo $gal->name ?></a></strong></p><p><?php echo $gal->desc ?><br />[<?php echo $sg->galleryContents($index+$sg->startat) ?>]</p></td>
    </tr>
    </table>
    </div>
    <?php endforeach; ?>
    
    </td>
    <td class="mr"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="bl"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="bm"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" alt="" /></td>
    <td class="br"><img src="<?php echo $sg->config->pathto_current_template ?>images/blank.gif" width="32" height="32" alt="" /></td>
  </tr>
</table></div>
  
  
<p>
<?php foreach($sg->galleryDetailsArray() as $detail): ?>
<strong><?php echo $detail->name ?>:</strong> <?php echo $detail->value ?><br />
<?php endforeach; ?>
</p>
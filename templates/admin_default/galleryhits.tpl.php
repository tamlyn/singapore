<h1><?php echo $sg->galleryName()?></h1>

<div class="sgShadow"><table class="sgShadow" cellspacing="0">
  <tr>
    <td><img src="<?php echo $sg->config->pathto_admin_template?>images/shadow-tabl.gif" alt="" /></td>
    <td class="tabm"><table class="sgShadowTab" cellspacing="0"><tr><td>
  
    <?php echo $sg->galleryHitsTab()?>
  
    </td><td><img src="<?php echo $sg->config->pathto_admin_template?>images/shadow-tabr.gif" alt="" /></td></tr></table></td>
    <td class="tabr"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="tl"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" width="32" height="16" alt="" /></td>
    <td class="tm"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
    <td class="tr"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="ml"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
    <td class="mm">
    <table class="sgList">
      <tr><th><?php echo $sg->i18n->_g("Gallery name") ?></th><th><?php echo $sg->i18n->_g("hits table|Hits") ?></th><th><?php echo $sg->i18n->_g("hits table|Last hit") ?></th><th><?php echo $sg->i18n->_g("hits table|Graph") ?></th></tr>
      <?php foreach($sg->galleryGalleriesArray() as $index => $gal): ?>
      <tr>
        <tr class="sgRow<?php echo $index%2 ?>"><td><a href="admin.php?action=<?php echo $sg->isGallery($index) ? "showimagehits": "showgalleryhits" ?>&amp;gallery=<?php echo rawurlencode($gal->id) ?>"><?php echo $gal->name ?></a></td>
        <td align="right"><?php echo $gal->hits->hits==0 ? "0" : $gal->hits->hits ?></td>
        <td align="right" title="<?php echo $gal->hits->lasthit==0 ? "n/a" : date("Y-m-d H:i:s",$gal->hits->lasthit) ?>"><?php echo $gal->hits->lasthit==0 ? "n/a" : date("D j H:i",$gal->hits->lasthit) ?></td>
        <td><img src="<?php echo $sg->config->pathto_admin_template ?>images/graph.gif" height="8" width="<?php echo $sg->gallery->maxhits==0 ? "0" : floor(($gal->hits->hits/$sg->gallery->maxhits)*300) ?>" /></td></tr>
      </tr>
      <?php endforeach; ?>
    </table>
    
    </td>
    <td class="mr"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
  </tr>
  <tr>
    <td class="bl"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
    <td class="bm"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" alt="" /></td>
    <td class="br"><img src="<?php echo $sg->config->pathto_admin_template ?>images/blank.gif" width="32" height="32" alt="" /></td>
  </tr>
</table></div>

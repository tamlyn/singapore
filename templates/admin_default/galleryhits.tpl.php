<p class="sgNavBar sgTopNavBar">
<?php if($sg->gallery->hasPrev()) echo $sg->gallery->prevLink("showgalleryhits")." | "; ?> 
<?php if(!$sg->gallery->isRoot()) echo $sg->gallery->parentLink("showgalleryhits"); ?> 
<?php if($sg->gallery->hasNext()) echo " | ".$sg->gallery->nextLink("showgalleryhits"); ?>
</p>

<h1><?php echo $sg->gallery->name(); ?></h1>

<div class="sgContainer">
  <div class="sgContent">
    <table class="sgList">
      <tr><th><?php echo $sg->translator->_g("Gallery name") ?></th><th><?php echo $sg->translator->_g("hits table|Hits") ?></th><th><?php echo $sg->translator->_g("hits table|Last hit") ?></th><th><?php echo $sg->translator->_g("hits table|Graph") ?></th></tr>
      <?php $maxhits = $sg->getMaxHits($sg->gallery->galleries); ?>
      <?php foreach($sg->gallery->galleries as $index => $gal): ?>
      <tr class="sgRow<?php echo $index%2 ?>">
        <td><?php echo $gal->nameLink($gal->isGallery()?"showgalleryhits":"showimagehits"); ?> (<?php echo $gal->idEntities() ?>)</td>
        <td align="right"><?php echo $gal->hits; ?></td>
        <td align="right" title="<?php echo $gal->lasthit==0 ? "n/a" : date("Y-m-d H:i:s",$gal->lasthit) ?>"><?php echo $gal->lasthit==0 ? "n/a" : date("D j H:i",$gal->lasthit) ?></td>
        <td><img src="<?php echo $sg->config->base_url.$sg->config->pathto_admin_template ?>images/graph.gif" height="8" width="<?php echo $maxhits==0 ? "0" : floor(($gal->hits/$maxhits)*300) ?>" alt="" /></td>
      </tr>
      <?php endforeach; ?>
    </table>
    
  </div>
</div>

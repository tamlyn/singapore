<p class="sgNavBar sgTopNavBar">
<?php if($sg->gallery->hasPrev()) echo $sg->gallery->prevLink("showimagehits")." | "; ?> 
<?php if(!$sg->gallery->isRoot()) echo $sg->gallery->parentLink("showimagehits"); ?> 
<?php if($sg->gallery->hasNext()) echo " | ".$sg->gallery->nextLink("showimagehits"); ?>
</p>

<h1><?php echo $sg->gallery->name(); ?></h1>

<div class="sgContainer">
  <div class="sgContent">
    <table class="sgList">
      <tr><th><?php echo $sg->translator->_g("Image name") ?></th><th><?php echo $sg->translator->_g("hits table|Hits") ?></th><th><?php echo $sg->translator->_g("hits table|Last hit") ?></th><th><?php echo $sg->translator->_g("hits table|Graph") ?></th></tr>
      <?php $maxhits = $sg->getMaxHits($sg->gallery->images); ?>
      <?php foreach($sg->gallery->images as $index => $img): ?>
      <tr class="sgRow<?php echo $index%2 ?>">
        <td><?php echo $img->nameLink(); ?> (<?php echo $img->id ?>)</td>
        <td align="right"><?php echo empty($img->hits) ? "0" : $img->hits ?></td>
        <td align="right" title="<?php echo empty($img->lasthit) ? "n/a" : date("Y-m-d H:i:s",$img->lasthit) ?>"><?php empty($img->lasthit) ? "n/a" : date("D j H:i",$img->lasthit) ?></td>
        <td><img src="<?php echo $sg->config->base_url.$sg->config->pathto_admin_template ?>images/graph.gif" height="8" width="<?php echo $maxhits==0 ? "0" : floor(($img->hits/$maxhits)*300) ?>" /></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>    
</div>

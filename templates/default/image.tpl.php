<p class="sgNavBar sgTopNavBar">
<?php echo $sg->previewThumbnails(); ?>
<br />
<?php if($sg->image->hasPrev()) echo $sg->image->prevLink()." | "; ?> 
<?php echo $sg->image->parentLink(); ?> 
<?php if($sg->image->hasNext()) echo " | ".$sg->image->nextLink(); ?>
</p>
<h2 class="sgTitle"><?php echo $sg->image->name(); ?></h2>
<h4 class="sgSubTitle"><?php echo $sg->image->byArtistText(); ?></h4>
  
<div class="sgShadow"><table class="sgShadow" cellspacing="0">
  <tr>
    <td class="tl"></td>
    <td class="tm"></td>
    <td class="tr"></td>
  </tr>
  <tr>
    <td class="ml"></td>
    <td class="mm">
    <?php echo $sg->image->imageHTML() ?> 
    </td>
    <td class="mr"></td>
  </tr>
  <tr>
    <td class="bl"></td>
    <td class="bm"></td>
    <td class="br"></td>
  </tr>
</table></div>
  
<p class="sgNavBar sgBottomNavBar">
<?php if($sg->image->hasPrev()) echo $sg->image->prevLink()." | "; ?> 
<?php echo $sg->image->parentLink(); ?> 
<?php if($sg->image->hasNext()) echo " | ".$sg->image->nextLink(); ?>
</p>

<h4 class="sgNameByArtist"><em><?php echo $sg->image->name() ?></em><?php echo $sg->image->byArtistText() ?></h4>
  
<p class="sgDetailsList">
<?php foreach($sg->image->detailsArray() as $key => $value): ?>
<strong><?php echo $key ?>:</strong> <?php echo $value ?><br />
<?php endforeach; ?>
</p>

<?php echo $sg->imageMap() ?>

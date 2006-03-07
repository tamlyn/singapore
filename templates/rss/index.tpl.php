<?php 

/**
 * RSS 2.0 output for singapore.
 * 
 * @author Ross Howard <abitcloser.com
 * @copyright (c)2006 Tamlyn Rhodes
 * @version 0.8
 */

if(headers_sent())
  die("ERROR: Unable to send XML content-type header.");
else
  header("Content-type: application/rss+xml; charset=".$sg->character_set);

echo '<?xml version="1.0" encoding="ISO-8859-1"?>'; ?>
<rss version="2.0" 
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:wfw="http://wellformedweb.org/CommentAPI/"
  xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>
    <title><?php echo $sg->gallery->name(); ?></title>
    <link><?php echo "http://".$_SERVER['HTTP_HOST'].($sg->config->use_mod_rewrite ? '' : dirname($_SERVER['PHP_SELF']).'/').str_replace("template=rss", "", $sg->gallery->URL()); ?></link>
    <description><?php echo $sg->gallery->name(); ?> Feed</description>
    <?php if(($timestamp = @strtotime($sg->gallery->date())) !== false && $timestamp != -1)
            echo '<pubDate>'.date('r', $timestamp).'</pubDate>';  ?> 
    <generator>http://www.sgal.org/</generator>
    <?php if($sg->isAlbumPage()) { ?>
    <?php for($index = $sg->gallery->startat; $index < $sg->gallery->imageCountSelected()+$sg->gallery->startat; $index++): ?> 
    <item>
       <title><?php echo $sg->gallery->images[$index]->name; ?></title>
       <link><?php echo "http://".$_SERVER['HTTP_HOST'].str_replace("?template=rss", "", $sg->gallery->images[$index]->url()); ?></link>
       <?php if(($timestamp = @strtotime($sg->gallery->images[$index]->date())) !== false && $timestamp != -1)
               echo '<pubDate>'.date('r', $timestamp).'</pubDate>'; ?> 
       <dc:creator><?php echo $sg->gallery->images[$index]->artist(); ?></dc:creator>
       <description>
         <![CDATA[ <?php echo $sg->gallery->images[$index]->description(); ?> ]]>
       </description>
     </item>
     <?php endfor; ?>
     <?php } elseif($sg->isGalleryPage()) { ?>
     <?php for($index = $sg->gallery->startat; $index < $sg->gallery->galleryCountSelected()+$sg->gallery->startat; $index++): ?> 
     <item>
       <title><![CDATA[<?php echo $sg->gallery->galleries[$index]->name(); ?> ]]></title>
       <link><?php echo "http://".$_SERVER['HTTP_HOST'].str_replace("?template=rss", "", $sg->gallery->galleries[$index]->url()); ?></link>
       <?php if(($timestamp = @strtotime($sg->gallery->date())) !== false && $timestamp != -1)
               echo '<pubDate>'.date('r', $timestamp).'</pubDate>'; ?> 
       <dc:creator><?php echo $sg->gallery->galleries[$index]->artist(); ?></dc:creator>
       <description>
         <![CDATA[ <?php echo $sg->gallery->galleries[$index]->description(); ?>]]>
       </description>
     </item>
     <?php endfor; ?>
   <?php } ?> 
  </channel>
</rss>

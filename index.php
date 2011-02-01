<?php

/**
 * Main file drives the gallery.
 *
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003-9 Tamlyn Rhodes
 * @version $Id$
 */

require_once "sgal/sgal.class.php";
sgal::getInstance()->template->run();

?>

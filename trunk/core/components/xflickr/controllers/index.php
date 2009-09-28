<?php
/**
 * @package xflickr
 * @subpackage controllers
 */
require_once dirname(dirname(__FILE__)).'/model/xflickr/xflickr.class.php';
$xflickr = new XFlickr($modx);
return $xflickr->initialize('mgr');

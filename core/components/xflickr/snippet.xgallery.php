<?php
/**
 * XGallery
 *
 * Display flickr photos as gallery.
 *
 * @name XGallery
 * @author atma <atma@atmaworks.com>
 * @package xflickr
 */
require_once $modx->getOption('core_path').'components/xflickr/model/xflickr/xflickr.class.php';

/** @var string $context The context to initialize XGallery in. */
if (!isset($scriptProperties['context'])) $scriptProperties['context'] = 'web';

/* start up xflickr */
$xflickr = new XFlickr($modx,$scriptProperties);
return $xflickr->initialize($scriptProperties['context'], 'gallery');

<?php
/**
 * XPhoto
 *
 * Display single flickr photo with additional information.
 *
 * @name XPhoto
 * @author atma <atma@atmaworks.com>
 * @package xflickr
 */
require_once $modx->getOption('core_path').'components/xflickr/model/xflickr/xflickr.class.php';

/** @var string $context The context to initialize XPhoto in. */
if (!isset($scriptProperties['context'])) $scriptProperties['context'] = 'web';

/* start up xflickr */
$xflickr = new XFlickr($modx,$scriptProperties);
return $xflickr->initialize($scriptProperties['context'], 'photo');

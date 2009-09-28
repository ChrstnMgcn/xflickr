<?php
/**
 * Generate flickr authentification url
 *
 * @package xflickr
 * @subpackage processors.photos
 */
if ($_REQUEST['photo_id']) {
	$photo_id = $_REQUEST['photo_id'];
} else {
	return $modx->error->failure();
}
$photo_sizes = $modx->xflickr->photos_getSizes ($photo_id);
if (!$photo_sizes) {
	$error = $modx->xflickr->getError();
	return $modx->error->failure($modx->lexicon('xflickr.error').': '.$modx->lexicon('xflickr.error_code').' '.$error['code'].' - '.$modx->lexicon('xflickr.error_msg').' '.$error['message']);
}
foreach ($photo_sizes as $size) {
	$key = 'xflickr.'.strtolower($size['label']);
	$size['label'] = $modx->lexicon($key);
}

return $modx->error->success('',$photo_sizes);

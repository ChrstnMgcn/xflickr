<?php
/**
 * Get photo information
 *
 * @package xflickr
 * @subpackage processors.photos
 */
if ($_REQUEST['photo_id']) {
	$photo_id = $_REQUEST['photo_id'];
} else {
	return $modx->error->failure('photo_id can not be empty');
}
$photo_info = $modx->xflickr->photos_getInfo ($photo_id);
if (!$photo_info) {
	$xferror = $modx->xflickr->getError();
	$error_msg = $modx->lexicon('xflickr.error').': '.$modx->lexicon('xflickr.error_code').' '.$xferror['code'].' - '.$modx->lexicon('xflickr.error_msg').' '.$xferror['message'];
	
	return $modx->error->failure($error_msg);
}
if (count($photo_info['tags']['tag'])) {
	$tags = array();
	foreach ($photo_info['tags']['tag'] as $tag) {
		$tags[] = $tag['raw'];
	}
	$photo_info['alltags'] = implode(', ', $tags);
} else {
	$photo_info['alltags'] = '';
}
$response = array();
$response['success'] = true;
$response['message'] = '';
$response['total'] = 1;
$response['object'][0] = $photo_info;
return $response;

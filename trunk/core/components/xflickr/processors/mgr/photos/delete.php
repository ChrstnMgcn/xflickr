<?php
/**
 * Delete photo
 *
 * @package xflickr
 * @subpackage processors.photos
 */
if (!$_REQUEST['photo_id']) {
	return $modx->error->failure('photo_id can not be empty');
}

if ($modx->xflickr->photos_delete($_REQUEST['photo_id']) == FALSE) {
	$xferror = $modx->xflickr->getError();
	return $modx->error->failure($modx->lexicon('xflickr.error').': '.$modx->lexicon('xflickr.error_code').' '.$xferror['code'].' - '.$modx->lexicon('xflickr.error_msg').' '.$xferror['message']);
}

$modx->xflickr->clear_cache();
return $modx->error->success('Photo with id '.$_REQUEST['photo_id'].' deleted');

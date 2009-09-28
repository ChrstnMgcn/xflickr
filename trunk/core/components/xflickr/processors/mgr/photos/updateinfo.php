<?php
/**
 * Save photo information
 *
 * @package xflickr
 * @subpackage processors.photos
 */
if ((!$_POST['id']) || (!$_POST['title'])) {
	return $modx->error->failure('photo_id or title can not be empty');
}
$description = ($_POST['description']) ? $_POST['description'] : '';
$tags = ($_POST['alltags']) ? $_POST['alltags'] : '';
if ($modx->xflickr->photos_setMeta($_POST['id'],$_POST['title'],$description) == FALSE) {
	$xferror = $modx->xflickr->getError();
	return $modx->error->failure($modx->lexicon('xflickr.error').': '.$modx->lexicon('xflickr.error_code').' '.$xferror['code'].' - '.$modx->lexicon('xflickr.error_msg').' '.$xferror['message']);
}
if ($tags) {
	$tags = explode(',', $tags);
	foreach ($tags as $tag) {
		$tag = str_replace(' ', '+', trim($tag));
	}
	$tags = implode(',',$tags);
	if ($modx->xflickr->photos_setTags($_POST['id'], $tags) == FALSE) {
		$xferror = $modx->xflickr->getError();
		return $modx->error->failure($modx->lexicon('xflickr.error').': '.$modx->lexicon('xflickr.error_code').' '.$xferror['code'].' - '.$modx->lexicon('xflickr.error_msg').' '.$xferror['message']);
	}
}

$modx->xflickr->clear_cache();
return $modx->error->success('all done');

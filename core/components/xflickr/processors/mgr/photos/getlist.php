<?php
/**
 * Fetch photostream
 *
 * @package xflickr
 * @subpackage processors.photos
 */

$user_id = $modx->xflickr->getUserId();
$per_page = (isset($_REQUEST['per_page'])) ? $_REQUEST['per_page'] : 25;
$page = (isset($_REQUEST['start'])) ? (($_REQUEST['start'] / $per_page) + 1) : 1;
$filter = (isset($_REQUEST['filter'])) ? $_REQUEST['filter'] : 'all';
$extras = 'license, date_upload, date_taken, owner_name, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o';
if ($filter == 'all') {
	$photos = $modx->xflickr->people_getPublicPhotos($user_id, $per_page, $page, 1, $extras);	
} elseif ($filter == 'notinset') {
	$photos = $modx->xflickr->photos_getNotInSet ($per_page, $page, NULL, NULL, NULL, NULL, 1, 'photos', $extras);
} else {
	$photos = $modx->xflickr->photosets_getPhotos ($filter, $per_page, $page, 'photos', $extras);
}

if (!$photos) {
	$error = $modx->xflickr->getError();
	return $modx->error->failure($modx->lexicon('xflickr.error').': '.$modx->lexicon('xflickr.error_code').' '.$error['code'].' - '.$modx->lexicon('xflickr.error_msg').' '.$error['message']);
}
$total_photos = count($photos['photo']);
//for ($i=0;$i<$total_photos;$i++) {
//	$photos['photo'][$i]['photo_id'] = $photos['photo'][$i]['id'];
//	$photos['photo'][$i]['id'] = $i;
//}
$obj = array();
$obj['success'] = true;
$obj['message'] = 'msg';
$obj['total'] = $photos['total'];
$obj['object'] = $photos['photo'];
//return $modx->error->success('testing photos',$photos['photo']);
return $obj;
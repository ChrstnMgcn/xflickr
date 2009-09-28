<?php
/**
 * Fetch photostream
 *
 * @package xflickr
 * @subpackage processors.gallery
 */

$user_id = $modx->xflickr->getUserId();
$mode = (isset($_POST['mode'])) ? $_POST['mode'] : 'set';
$per_page = (isset($_POST['perpage'])) ? $_POST['perpage'] : 25;
$page = (isset($_POST['page'])) ? $_POST['page'] : 1;
$set = (isset($_POST['set'])) ? $_POST['set'] : 'all';
$size = (isset($_POST['size'])) ? $_POST['size'] : 'Thumbnail';
$fullSize = (isset($_POST['fullSize'])) ? $_POST['fullSize'] : 'Large';

if ($set == 'all') {
	$photos = $modx->xflickr->people_getPublicPhotos($user_id, $per_page, $page);
} elseif ($set == 'notinset') {
	$photos = $modx->xflickr->photos_getNotInSet ($per_page, $page);
} else {
	$photos = $modx->xflickr->photosets_getPhotos ($set, $per_page, $page);
}

if (!$photos) {
	$xferror = $modx->xflickr->getError();
	return $modx->error->failure($modx->lexicon('xflickr.error').': '.$modx->lexicon('xflickr.error_code').' '.$xferror['code'].' - '.$modx->lexicon('xflickr.error_msg').' '.$xferror['message']);
}

foreach ($photos['photo'] as $key => $photo) {
	$photo_sizes = $this->modx->xflickr->photos_getNamedSizes($photo['id']);
	if ($photo_sizes[$fullSize]) {
		$photos['photo'][$key]['larger_width'] = $photo_sizes[$fullSize]['width'];
		$photos['photo'][$key]['larger_height'] = $photo_sizes[$fullSize]['height'];
		$photos['photo'][$key]['larger_url'] = $photo_sizes[$fullSize]['source'];
	} else {
		$photos['photo'][$key]['larger_width'] = $photo_sizes['Max']['width'];
		$photos['photo'][$key]['larger_height'] = $photo_sizes['Max']['height'];
		$photos['photo'][$key]['larger_url'] = $photo_sizes['Max']['source'];
	}
	$photos['photo'][$key]['width'] = $photo_sizes[$size]['width'];
	$photos['photo'][$key]['height'] = $photo_sizes[$size]['height'];
	$photos['photo'][$key]['url'] = $photo_sizes[$size]['source'];
	$photos['photo'][$key]['title'] = htmlspecialchars($photo['title'], ENT_QUOTES);
}

		////$chunk = $this->xflickr->getChunk('xflickrgalleryphoto', $this->xflickr->config['tpl']);
		//foreach($photos['photo'] as $photo) {
		//	$chunk_props = array();
		//	$photo_sizes = $this->xflickr->photos_getNamedSizes($photo['id']);
		//	$chunk_props['title'] = $photo['title'];
		//	$chunk_props['width'] = $photo_sizes[$size]['width'];
		//	$chunk_props['height'] = $photo_sizes[$size]['height'];
		//	$chunk_props['url'] = $photo_sizes[$size]['source'];
		//	if ($photo_sizes[$largerSize]) {
		//		$chunk_props['larger_width'] = $photo_sizes[$largerSize]['width'];
		//		$chunk_props['larger_height'] = $photo_sizes[$largerSize]['height'];
		//		$chunk_props['larger_url'] = $photo_sizes[$largerSize]['source'];
		//	} else {
		//		$chunk_props['larger_width'] = $photo_sizes['Max']['width'];
		//		$chunk_props['larger_height'] = $photo_sizes['Max']['height'];
		//		$chunk_props['larger_url'] = $photo_sizes['Max']['source'];
		//	}
		//	$chunk_props['rel'] = 'xgallery_'.$this->xflickr->config['set'].'[gallery]';
		//	$chunk = $this->xflickr->getChunk('xflickrgalleryphoto', $this->xflickr->config['tpl']);
		//	//$chunk->setCacheable(FALSE);
		//	$output .= $chunk->process($chunk_props);
		//}

$total_photos = count($photos['photo']);

$obj = array();
$obj['success'] = true;
$obj['message'] = 'msg';
$obj['total'] = $photos['total'];
$obj['object'] = $photos['photo'];
//return $modx->error->success('testing photos',$photos['photo']);
return $obj;
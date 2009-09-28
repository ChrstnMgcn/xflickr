<?php
/**
 * Fetch photostream
 *
 * @package xflickr
 * @subpackage processors.photos
 */

$user_id = $modx->xflickr->getUserId();
$sets = $modx->xflickr->photosets_getList($user_id);
$sets_count = count($sets);

$filter = array();
$filter[0] = array('set_id' => 'all', 'title' => $modx->lexicon('xflickr.all_photos'));
$filter[1] = array('set_id' => 'notinset', 'title' => $modx->lexicon('xflickr.notinset_photos'));
foreach ($sets as $set) {
	$filter[] = array('set_id' => $set['id'], 'title' => $set['title']['_content']);
}

$obj = array();
$obj['success'] = true;
$obj['message'] = '';
$obj['total'] = $sets_count;
$obj['object'] = $filter;
return $obj;

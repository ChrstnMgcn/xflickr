<?php
/**
 * Fetch summary info
 *
 * @package xflickr
 * @subpackage processors.summary
 */

$user_id = $modx->xflickr->getUserId();
$user_info = $modx->xflickr->people_getInfo($user_id);
$favs = $modx->xflickr->favorites_getList();
$contacts = $modx->xflickr->contacts_getList();

$sum_data = array();
$sum_data['user_id'] = $user_id;
$sum_data['username'] = $user_info['username']['_content'];
$sum_data['realname'] = $user_info['realname']['_content'];
$sum_data['acc_type'] = ($user_info['ispro']) ? 'Pro' : 'Basic';
$sum_data['ispro'] = $user_info['ispro'];
$sum_data['buddyicon'] = $modx->xflickr->getBuddyicon($user_id);
$sum_data['profileurl'] = $user_info['profileurl']['_content'];
$sum_data['photosurl'] = $user_info['photosurl']['_content'];
$sum_data['location'] = ($user_info['location']['_content']) ? $user_info['location']['_content'] : false;
$sum_data['photos_count'] = ($user_info['photos']['count']['_content']) ? $user_info['photos']['count']['_content'] : 0;
$sum_data['views_count'] = ($user_info['photos']['views']['_content']) ? $user_info['photos']['views']['_content'] : 0;
$sum_data['favorites_count'] = $favs['total'];
$sum_data['contacts_count'] = $contacts['total'];
if ($user_info['ispro'] == 0) {
	$upload_status = $modx->xflickr->people_getUploadStatus();
	$sum_data['photo_max'] = $upload_status['filesize']['maxmb'];
	$sum_data['video_max'] = $upload_status['videosize']['maxmb'];
	$sum_data['bw_max'] = intval($upload_status['bandwidth']['maxkb']/1024);
	$sum_data['bw_used'] = intval($upload_status['bandwidth']['usedkb']/1024);
	$sum_data['bw_remaining'] = intval($upload_status['bandwidth']['remainingkb']/1024);
	$sum_data['sets_created'] = $upload_status['sets']['created'];
	$sum_data['sets_remaining'] = $upload_status['sets']['remaining'];
}

return $modx->error->success('',$sum_data);

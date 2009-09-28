<?php
/**
 * Generate flickr authentification url
 *
 * @package xflickr
 * @subpackage processors.auth
 */

if (!$modx->hasPermission('settings')) {
	return $modx->error->failure($modx->lexicon('permission_denied'));
}
if ($_POST['key']) {
	$key = $_POST['key'];
} else {
	return $modx->error->failure('Key field can not be empty');
}
if ($_POST['secret']) {
	$secret = $_POST['secret'];
} else {
	return $modx->error->failure('Secret field can not be empty');
}
$ss_key = $modx->getObject('modSystemSetting', array('key' => 'xflickr.api_key'));
$ss_key->set('value',$key);
if ($ss_key->save() == false) { echo 'Can not save new api_key';}
$ss_secret = $modx->getObject('modSystemSetting', array('key' => 'xflickr.api_secret'));
$ss_secret->set('value',$secret);
if ($ss_secret->save() == false) { echo 'Can not save new api_secret';}
$modx->reloadConfig();

//set key and secret for current frob request
$modx->xflickr->setKey($key);
$modx->xflickr->setSecret($secret);
$frob = $modx->xflickr->requestFrob();
$link = $modx->xflickr->buildAuthUrl($frob, $key, $secret);

$sysdata = array();
$sysdata['api_key'] = $key;
$sysdata['api_secret'] = $secret;
$sysdata['frob'] = $frob;
$sysdata['auth_link'] = $link;

return $modx->error->success('',$sysdata);

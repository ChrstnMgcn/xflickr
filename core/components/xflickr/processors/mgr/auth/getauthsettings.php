<?php
/**
 * Get a flickr authentification settings
 *
 * @package xflickr
 * @subpackage processors.auth
 */

if (!$modx->hasPermission('settings')) {
	return $modx->error->failure($modx->lexicon('permission_denied'));
}
$frob = $modx->xflickr->requestFrob();
$link = $modx->xflickr->buildAuthUrl($frob);
//$key = $modx->getObject('modSystemSetting', array('key' => 'xflickr.api_key'));
$key = $modx->getOption('xflickr.api_key');
$secret = $modx->getOption('xflickr.api_secret');

$sysdata = array();
$sysdata['api_key'] = $key;
$sysdata['api_secret'] = $secret;
$sysdata['frob'] = $frob;
$sysdata['auth_link'] = $link;

return $modx->error->success('',$sysdata);
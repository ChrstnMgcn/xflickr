<?php
/**
 * Save flickr token
 *
 * @package xflickr
 * @subpackage processors.auth
 */

if (!$modx->hasPermission('settings')) {
	return $modx->error->failure($modx->lexicon('permission_denied'));
}
if ($_POST['frob']) {
	$frob = $_POST['frob'];
} else {
	return $modx->error->failure('Something wrong with parameters. Check values and request new auth link');
}
$token = $modx->xflickr->getTokenFromFrob($frob);
if (!$token) {
	return $modx->error->failure('Please be sure that you have confirmed author on flickr');
} else {
	$ss_token = $modx->getObject('modSystemSetting', array('key' => 'xflickr.token'));
	$ss_token->set('value',$token);
	if ($ss_token->save() == false) { echo 'Can not save new api_secret';}
	$modx->reloadConfig();
}
return $modx->error->success('ok');

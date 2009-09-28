<?php
/**
 * @package xflickr
 * @subpackage build
 */
$settings = array();
$settings['xflickr.api_key']= $modx->newObject('modSystemSetting');
$settings['xflickr.api_key']->fromArray(array(
    'key' => 'xflickr.api_key',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'xflickr',
    'area' => 'XFlickr',
),'',true,true);

$settings['xflickr.api_secret']= $modx->newObject('modSystemSetting');
$settings['xflickr.api_secret']->fromArray(array(
    'key' => 'xflickr.api_secret',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'xflickr',
    'area' => 'XFlickr',
),'',true,true);

$settings['xflickr.token']= $modx->newObject('modSystemSetting');
$settings['xflickr.token']->fromArray(array(
    'key' => 'xflickr.token',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'xflickr',
    'area' => 'XFlickr',
),'',true,true);

return $settings;
<?php
/**
 * XFlickr Connector
 *
 * @package xflickr
 */
/* Load custom XFlickr defines and modx object */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

require_once MODX_CORE_PATH.'components/xflickr/model/xflickr/xflickr.class.php';
$modx->xflickr = new XFlickr($modx);

if (isset($_REQUEST['resource'])) {
    $modx->resource = $modx->getObject('modResource',$_REQUEST['resource']);
}

/* handle request */
$path = $modx->getOption('processors_path',$modx->xflickr->config,$modx->getOption('core_path').'components/xflickr/processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));
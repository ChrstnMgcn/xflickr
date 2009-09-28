<?php
/**
 * Loads the photos page.
 *
 * @package xflickr
 * @subpackage controllers
 */
$modx->regClientStartupScript($xflickr->config['js_url'].'widgets/navigation.menu.js');
$modx->regClientStartupScript($xflickr->config['js_url'].'widgets/photos.panel.js');
$modx->regClientStartupScript($xflickr->config['js_url'].'sections/photos.js');

$output .= '<div id="xflickr-panel"></div>';

return $output;

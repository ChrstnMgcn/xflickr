<?php
/**
 * Loads the summary page.
 *
 * @package xflickr
 * @subpackage controllers
 */
$modx->regClientStartupScript($xflickr->config['js_url'].'widgets/navigation.menu.js');
$modx->regClientStartupScript($xflickr->config['js_url'].'widgets/summary.panel.js');
$modx->regClientStartupScript($xflickr->config['js_url'].'sections/summary.js');

$output .= '<div id="xflickr-panel"></div>';

return $output;

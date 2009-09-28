<?php
/**
 * Loads the authentication page.
 *
 * @package xflickr
 * @subpackage controllers
 */
$modx->regClientStartupScript($xflickr->config['js_url'].'widgets/auth.panel.js');
$modx->regClientStartupScript($xflickr->config['js_url'].'sections/auth.js');

$output .= '<div id="xflickr-panel"></div>';

return $output;

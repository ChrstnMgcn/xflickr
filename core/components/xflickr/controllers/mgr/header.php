<?php
/**
 * Loads the header for mgr pages.
 *
 * @package xflickr
 * @subpackage controllers
 */
$modx->regClientCSS($xflickr->config['css_url'].'mgr.css');
$modx->regClientStartupScript($xflickr->config['js_url'].'xflickr.js');
$modx->regClientStartupHTMLBlock('<script type="text/javascript">
Ext.onReady(function() {
    XFlickr.config = '.$modx->toJSON($xflickr->config).';
    XFlickr.config.connector_url = "'.$xflickr->config['connector_url'].'";
    XFlickr.request = '.$modx->toJSON($_GET).';
});
</script>');

return '';
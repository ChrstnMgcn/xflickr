<?php
/**
 * Build the setup options form.
 *
 * @package xflickr
 * @subpackage build
 */
/* set some default values */
$values = array(
    'api_key' => '3e7f9744764765f6c44188a31f0ee739', //predefined flickr API key
    'api_secret' => '164c3b16ed31df1e', //predefined key secret
);
switch ($options[XPDO_TRANSPORT_PACKAGE_ACTION]) {
    case XPDO_TRANSPORT_ACTION_INSTALL:
    case XPDO_TRANSPORT_ACTION_UPGRADE:
        $setting = $modx->getObject('modSystemSetting',array('key' => 'xflickr.api_key'));
        if ($setting != null) { $values['api_key'] = $setting->get('value'); }
        unset($setting);

        $setting = $modx->getObject('modSystemSetting',array('key' => 'xflickr.api_secret'));
        if ($setting != null) { $values['api_secret'] = $setting->get('value'); }
        unset($setting);

    break;
    case XPDO_TRANSPORT_ACTION_UNINSTALL: break;
}
$output = '<p>For normal use leave fields unchanged (recommended).
But you can apply your own <a href="http://www.flickr.com/services/api/keys/" target="_blank">API key</a> (can be changed at any time) if you want to analize key usage (qps, auth users etc.)</p>';
$output .= '<label for="xflickr-api_key">Flickr API key:</label>
<input type="text" name="api_key" id="xflickr-api_key" width="300" value="'.$values['api_key'].'" />
<br /><br />

<label for="xflickr-api_secret">Flickr shared secret:</label>
<input type="text" name="api_secret" id="xflickr-api_secret" width="300" value="'.$values['api_secret'].'" />
<br /><br />';

return $output;
<?php
/**
 * Resolves setup-options settings by setting flickr api key options.
 *
 * @package xflickr
 * @subpackage build
 */
$success= false;
switch ($options[XPDO_TRANSPORT_PACKAGE_ACTION]) {
    case XPDO_TRANSPORT_ACTION_INSTALL:
    case XPDO_TRANSPORT_ACTION_UPGRADE:
        /* api_key */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'xflickr.api_key'));
        if ($setting != null) {
            $setting->set('value',$options['api_key']);
            $setting->save();
        } else {
            $object->xpdo->log(XPDO_LOG_LEVEL_ERROR,'[XFlickr] api_key setting could not be found, so the setting could not be changed.');
        }

        /* api_secret */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'xflickr.api_secret'));
        if ($setting != null) {
            $setting->set('value',$options['api_secret']);
            $setting->save();
        } else {
            $object->xpdo->log(XPDO_LOG_LEVEL_ERROR,'[XFlickr] api_secret setting could not be found, so the setting could not be changed.');
        }

        /* token */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'xflickr.token'));
        if ($setting != null) {
            $setting->set('value',$options['token']);
            $setting->save();
        } else {
            $object->xpdo->log(XPDO_LOG_LEVEL_ERROR,'[XFlickr] token setting could not be found, so the setting could not be changed.');
        }

        $success= true;
        break;
    case XPDO_TRANSPORT_ACTION_UNINSTALL:
        $success= true;
        break;
}
return $success;
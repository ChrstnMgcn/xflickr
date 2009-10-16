<?php
/**
 * Adds modActions and modMenus into package
 *
 * @package xflickr
 * @subpackage build
 */
$action= $modx->newObject('modAction');
$action->fromArray(array(
    'id' => 1,
    'namespace' => 'xflickr',
    'parent' => 0,
    'controller' => 'index',
    'haslayout' => 1,
    'lang_topics' => 'xflickr:default',
    'assets' => '',
),'',true,true);

/* load menu into action */
$menu= $modx->newObject('modMenu');
$menu->fromArray(array(
    'parent' => 'components',
    'text' => 'xflickr',
    'description' => 'xflickr.desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => '0',
    'params' => '',
    'handler' => '',
),'',true,true);
$menu->addOne($action);

return $menu;
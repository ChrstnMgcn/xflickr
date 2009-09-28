<?php
/**
 * XFlickr build script
 *
 * @package xflickr
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

$root = dirname(dirname(__FILE__)).'/';
$sources= array (
    'root' => $root,
    'build' => $root .'_build/',
    'lexicon' => $root . '_build/lexicon/',
    'resolvers' => $root . '_build/resolvers/',
    'data' => $root . '_build/data/',
    'source_core' => $root.'core/components/xflickr',
    'source_assets' => $root.'assets/components/xflickr',
    'docs' => $root.'core/components/xflickr/docs/',
);
unset($root);

/* override with your own defines here (see build.config.sample.php) */
require_once dirname(__FILE__) . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx= new modX();
$modx->initialize('mgr');
$modx->setLogLevel(MODX_LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage('xflickr','0.1','alpha1');
$builder->registerNamespace('xflickr',false,true,'{core_path}components/xflickr/');

/* load action/menu */
$action = include $sources['data'].'transport.action.php';

$vehicle= $builder->createVehicle($action,array (
    XPDO_TRANSPORT_PRESERVE_KEYS => false,
    XPDO_TRANSPORT_UPDATE_OBJECT => true,
    XPDO_TRANSPORT_UNIQUE_KEY => array ('namespace','controller'),
    XPDO_TRANSPORT_RELATED_OBJECTS => true,
    XPDO_TRANSPORT_RELATED_OBJECT_ATTRIBUTES => array (
        'Menus' => array (
            XPDO_TRANSPORT_PRESERVE_KEYS => false,
            XPDO_TRANSPORT_UPDATE_OBJECT => true,
            XPDO_TRANSPORT_UNIQUE_KEY => array ('action', 'text'),
        ),
    ),
));
$builder->putVehicle($vehicle);
unset($vehicle,$action);

/* load system settings */
$settings = array();
include_once $sources['data'].'transport.settings.php';

$attributes= array(
    XPDO_TRANSPORT_UNIQUE_KEY => 'key',
    XPDO_TRANSPORT_PRESERVE_KEYS => true,
    XPDO_TRANSPORT_UPDATE_OBJECT => false,
);
foreach ($settings as $setting) {
    $vehicle = $builder->createVehicle($setting,$attributes);
    $builder->putVehicle($vehicle);
}
unset($settings,$setting,$attributes);


/* create category */
$category= $modx->newObject('modCategory');
$category->set('id',1);
$category->set('category','XFlickr');


/* create XGallery snippet */
$xg_snippet= $modx->newObject('modSnippet');
$xg_snippet->set('id',0);
$xg_snippet->set('name', 'XGallery');
$xg_snippet->set('description', 'Display flickr photos as gallery.');
$xg_snippet->set('snippet',file_get_contents($sources['source_core'].'/snippet.xgallery.php'));

$properties = include $sources['data'].'properties.xgallery.inc.php';
$xg_snippet->setProperties($properties);
$category->addMany($xg_snippet);

/* create XPhoto snippet */
$xph_snippet= $modx->newObject('modSnippet');
$xph_snippet->set('id',1);
$xph_snippet->set('name', 'XPhoto');
$xph_snippet->set('description', 'Display single flickr photo with additional information.');
$xph_snippet->set('snippet',file_get_contents($sources['source_core'].'/snippet.xphoto.php'));

$properties = include $sources['data'].'properties.xphoto.inc.php';
$xph_snippet->setProperties($properties);
$category->addMany($xph_snippet);

/* create category vehicle */
$attr = array(
    XPDO_TRANSPORT_UNIQUE_KEY => 'category',
    XPDO_TRANSPORT_PRESERVE_KEYS => false,
    XPDO_TRANSPORT_UPDATE_OBJECT => true,
    XPDO_TRANSPORT_RELATED_OBJECTS => true,
    XPDO_TRANSPORT_RELATED_OBJECT_ATTRIBUTES => array (
        'modSnippet' => array(
            XPDO_TRANSPORT_PRESERVE_KEYS => false,
            XPDO_TRANSPORT_UPDATE_OBJECT => true,
            XPDO_TRANSPORT_UNIQUE_KEY => 'name',
        ),
    )
);
$vehicle = $builder->createVehicle($category,$attr);
$vehicle->resolve('file',array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));
$vehicle->resolve('file',array(
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH . 'components/';",
));
$vehicle->resolve('php',array(
    'source' => $sources['resolvers'] . 'setupoptions.resolver.php',
));
$builder->putVehicle($vehicle);

/* load lexicon strings */
$builder->buildLexicon($sources['lexicon']);

/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'setup-options' => array(
        'source' => $sources['build'].'setup.options.php',
    ),
));

$builder->pack();

$mtime= microtime();
$mtime= explode(" ", $mtime);
$mtime= $mtime[1] + $mtime[0];
$tend= $mtime;
$totalTime= ($tend - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);

$modx->log(MODX_LOG_LEVEL_INFO,"\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");

exit ();
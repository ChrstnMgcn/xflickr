<?php
/**
 * Default snippet properties
 *
 * @package xflickr
 * @subpackage build
 */
$properties = array(
    array(
        'name' => 'mode',
        'desc' => 'The mode of fetching photos: set, list, tag, localset',
        'type' => 'textfield',
        'options' => '',
        'value' => 'set',
    ),
	array(
        'name' => 'pagination',
        'desc' => 'If set to true, gallery will be paginated.',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => true,
    ),
    array(
        'name' => 'perpage',
        'desc' => 'Number of photos to display per page if paginated.',
        'type' => 'textfield',
        'options' => '',
        'value' => 25,
    ),
    array(
        'name' => 'set',
        'desc' => 'The ID of flickr photoset to display.',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
    ),
    array(
        'name' => 'size',
        'desc' => 'Thumbnails size',
        'type' => 'textfield',
        'options' => '',
        'value' => 'thumbnail',
    ),
    array(
        'name' => 'largerSize',
        'desc' => 'Size of the zoomed photo (square, thumbnail, small, medium, large and original if you have PRO account).',
        'type' => 'textfield',
        'options' => '',
        'value' => 'large'
    ),
	array(
        'name' => 'zooming',
        'desc' => 'If set to true, lightbox (prettyPhoto) script will be added.',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => true
    ),
	array(
        'name' => 'registerCSS',
        'desc' => 'Register default xflickr css file or not. Be careful, once registered it will be applied to all photos on the page.',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => true
    ),
    array(
        'name' => 'theme',
        'desc' => 'Theme used for lightbox (colorbox). Possible values are: minimal, dark',
        'type' => 'textfield',
        'options' => '',
        'value' => 'grey_square'
    )
);
return $properties;
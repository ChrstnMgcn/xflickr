<?php
/**
 * Default snippet properties
 *
 * @package xflickr
 * @subpackage build
 */
$properties = array(
    array(
        'name' => 'id',
        'desc' => 'ID of the photo to display.',
        'type' => 'textfield',
        'options' => '',
        'value' => ''
    ),
    array(
        'name' => 'tpl',
        'desc' => 'Chunk name.',
        'type' => 'textfield',
        'options' => '',
        'value' => ''
    ),
    array(
        'name' => 'position',
        'desc' => 'Orientation of the processed chunk.',
        'type' => 'textfield',
        'options' => '',
        'value' => 'left'
    ),
    array(
        'name' => 'registerCSS',
        'desc' => 'Register default xflickr css file or not. Be careful, once registered it will be applied to all photos on the page.',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => true
    ),
    array(
        'name' => 'zooming',
        'desc' => 'If set to true, lightbox (colorbox) script will be added.',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false
    ),
    array(
        'name' => 'size',
        'desc' => 'Size of photo to display (square, thumbnail, small, medium, large)',
        'type' => 'textfield',
        'options' => '',
        'value' => 'medium'
    ),
    array(
        'name' => 'largerSize',
        'desc' => 'Size of the zoomed photo (square, thumbnail, small, medium, large and original if you have PRO account).',
        'type' => 'textfield',
        'options' => '',
        'value' => 'large'
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
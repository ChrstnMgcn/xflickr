<?php
/**
 * Clear xflickr cache
 *
 * @package xflickr
 * @subpackage processors.utils
 */

if ($modx->xflickr->clear_cache() == TRUE) {
	return $modx->error->success('Cache successfully cleared');
} else {
	return $modx->error->failure('Cache can not be cleared');
}

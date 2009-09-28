<?php
require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';
/**
 * Encapsulates the interaction of MODx manager with an HTTP request.
 *
 * {@inheritdoc}
 *
 * @package xflickr
 * @extends modRequest
 */
class XFlickrControllerRequest extends modRequest {
	var $xflickr = NULL;
	var $actionVar = 'action';
	var $defaultAction = 'summary';


	function __construct(XFlickr &$xflickr) {
		parent :: __construct($xflickr->modx);
		$this->xflickr =& $xflickr;
	}

	/**
	 * Extends modRequest::handleRequest and loads the proper error handler and
	 * actionVar value.
	 *
	 * {@inheritdoc}
	 */
	public function handleRequest() {
		$this->loadErrorHandler();

		/* save page to manager object. allow custom actionVar choice for extending classes. */
		$this->action = isset($_REQUEST[$this->actionVar]) ? $_REQUEST[$this->actionVar] : $this->defaultAction;

		return $this->_prepareResponse();
	}

	/**
	 * Prepares the MODx response to a mgr request that is being handled.
	 *
	 * @access public
	 * @return boolean True if the response is properly prepared.
	 */
	function _prepareResponse() {
		$modx =& $this->modx;
		$xflickr =& $this->xflickr;
		$viewHeader = include $this->xflickr->config['core_path'].'controllers/mgr/header.php';
		if ($xflickr->isAuthValid()) {
			$f = $this->xflickr->config['core_path'].'controllers/mgr/'.$this->action.'.php';
			if (file_exists($f)) {
				$viewOutput = include $f;
			} else {
				$viewOutput = 'Action not found: '.$f;
			}
		} else {
			$f = $this->xflickr->config['core_path'].'controllers/mgr/auth.php';
			$viewOutput = include $f;
		}
		return $viewHeader.$viewOutput;
	}
}
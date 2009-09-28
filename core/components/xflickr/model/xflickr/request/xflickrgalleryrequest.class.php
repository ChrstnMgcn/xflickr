<?php
/**
 * Runs the flickr gallery front-end system
 *
 * @package xflickr
 */
require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';

class XFlickrGalleryRequest extends modRequest {
	public $xflickr = null;

	/**
	 * Constructor for the XFlickrGalleryRequest class.
	 *
	 * Calls modRequest::__construct as well.
	 *
	 * {@inheritdoc}
	 *
	 * @param XFlickr &$xflickr Reference to the XFlickr instance.
	 * @return XFlickrGalleryRequest A unique class instance of this class.
	 */
	function __construct(XFlickr &$xflickr) {
		parent :: __construct($xflickr->modx);
		$this->xflickr =& $xflickr;
	}

	/**
	 * Handles the request and loads the front-end.
	 *
	 * @access public
	 * @return string The rendered content.
	 */
	public function handle() {
		if ($this->xflickr->config['registerCSS']) {
			$this->modx->regClientCSS($this->xflickr->config['css_url'].'web.css');
		}
		if ($this->xflickr->config['zooming']) {
			$this->modx->regClientCSS($this->xflickr->config['css_url'].'cb-'.$this->xflickr->config['theme'].'.css');
			$this->modx->regClientStartupHTMLBlock('
<!--[if IE]>
<link type="text/css" media="screen" rel="stylesheet" href="'.$this->xflickr->config['css_url'].'cb-'.$this->xflickr->config['theme'].'-ie.css'.'" />
<![endif]-->
			');
			$this->modx->regClientStartupScript($this->xflickr->config['js_url'].'web/jquery.xgallery.js');
			$this->modx->regClientStartupScript($this->xflickr->config['js_url'].'web/jquery.colorbox-min.js');
		}
		$this->modx->regClientStartupHTMLBlock('
<script type="text/javascript">
	var XGalleryBaseAjax = {
		url: "'.$this->xflickr->config['connector_url'].'",
		type: "POST",
		dataType: "json"
	};
</script>
		');

		$mode = ($this->xflickr->config['mode']) ? $this->xflickr->config['mode'] : 'set';
		//$page = ($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$perpage = ($this->xflickr->config['perpage'] > 0 && $this->xflickr->config['perpage'] < 100) ? $this->xflickr->config['perpage'] : 25;
		$size = ucfirst(strtolower($this->xflickr->config['size']));
		$fullSize = ucfirst(strtolower($this->xflickr->config['largerSize']));

		switch ($mode) {
			case 'list':

				break;

			case 'set':
			default:
				if (($this->xflickr->config['set'] == 'all') || ($this->xflickr->config['set'] == '')) {
					$user_id = $this->xflickr->getUserId();
					$ckeckset = $this->xflickr->people_getPublicPhotos($user_id, 1, 1);
					if ($ckeckset) {
						$total = $ckeckset['total'];
					} else {
						return false;
					}
					$this->modx->regClientHTMLBlock('
<script type="text/javascript">
	$(document).ready(function(){
		$("#xgallery-'.$mode.'-'.$this->xflickr->config['set'].'").XGallery({
			mode: "'.$mode.'",
			set: "all",
			total: '.$total.',
			page: 1,
			perpage: '.$perpage.',
			size: "'. $size.'",
			fullSize: "'.$fullSize.'",
			pagination: '.$this->xflickr->config['pagination'].'
		});
	});
</script>
					');
				} elseif ($this->xflickr->config['set'] == 'notinset') {
					$ckeckset = $this->xflickr->photos_getNotInSet(1, 1);
					if ($ckeckset) {
						$total = $ckeckset['total'];
					} else {
						return false;
					}
					$this->modx->regClientHTMLBlock('
<script type="text/javascript">
	$(document).ready(function(){
		$("#xgallery-'.$mode.'-'.$this->xflickr->config['set'].'").XGallery({
			mode: "'.$mode.'",
			set: "notinset",
			total: '.$total.',
			page: 1,
			perpage: '.$perpage.',
			size: "'. $size.'",
			fullSize: "'.$fullSize.'",
			pagination: '.$this->xflickr->config['pagination'].'
		});
	});
</script>
					');
				} else {
					$ckeckset = $this->xflickr->photosets_getPhotos($this->xflickr->config['set'], 1, 1);
					if ($ckeckset) {
						$total = $ckeckset['total'];
					} else {
						return false;
					}
					$this->modx->regClientHTMLBlock('
<script type="text/javascript">
	$(document).ready(function(){
		$("#xgallery-'.$mode.'-'.$this->xflickr->config['set'].'").XGallery({
			mode: "'.$mode.'",
			set: "'.$this->xflickr->config['set'].'",
			total: '.$total.',
			page: 1,
			perpage: '.$perpage.',
			size: "'. $size.'",
			fullSize: "'.$fullSize.'",
			pagination: '.$this->xflickr->config['pagination'].'
		});
	});
</script>
					');
				}
				break;
		}

		return '<div class="xgallery" id="xgallery-'.$mode.'-'.$this->xflickr->config['set'].'"></div>';
	}
}
<?php
/**
 * Runs the flickr photo front-end system
 *
 * @package xflickr
 */
require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';

class XFlickrPhotoRequest extends modRequest {
	public $xflickr = null;

	/**
	 * Constructor for the XFlickrPhotoRequest class.
	 *
	 * Calls modRequest::__construct as well.
	 *
	 * {@inheritdoc}
	 *
	 * @param XFlickr &$xflickr Reference to the XFlickr instance.
	 * @return XFlickrViewRequest A unique class instance of this class.
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
		$chunk_props = array();
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
			$this->modx->regClientStartupScript($this->xflickr->config['js_url'].'web/jquery.colorbox-min.js');
			$this->modx->regClientStartupHTMLBlock('
<script type="text/javascript">
	$(document).ready(function(){
		$(".'.$this->xflickr->config['id'].'").colorbox({maxWidth: "95%", maxHeight: "95%"});
	});
</script>
		');
		}
		$photo = $this->xflickr->photos_getInfo($this->xflickr->config['id']);
		$photo_sizes = $this->xflickr->photos_getNamedSizes($this->xflickr->config['id']);
		$size = ucfirst(strtolower($this->xflickr->config['size']));
		$largerSize = ucfirst(strtolower($this->xflickr->config['largerSize']));
		
		$chunk_props['id'] = $this->xflickr->config['id'];
		$chunk_props['title'] = $photo['title']['_content'];
		$chunk_props['description'] = $photo['description']['_content'];
		$chunk_props['position'] = $this->xflickr->config['position'];
		$chunk_props['flickr_url'] = $photo['urls']['url'][0]['_content'];
		$chunk_props['width'] = $photo_sizes[$size]['width'];
		$chunk_props['height'] = $photo_sizes[$size]['height'];
		$chunk_props['url'] = $photo_sizes[$size]['source'];
		if ($photo_sizes[$largerSize]) {
			$chunk_props['larger_width'] = $photo_sizes[$largerSize]['width'];
			$chunk_props['larger_height'] = $photo_sizes[$largerSize]['height'];
			$chunk_props['larger_url'] = $photo_sizes[$largerSize]['source'];
		} else {
			$chunk_props['larger_width'] = $photo_sizes['Max']['width'];
			$chunk_props['larger_height'] = $photo_sizes['Max']['height'];
			$chunk_props['larger_url'] = $photo_sizes['Max']['source'];
		}
		$chunk_props['outer_width'] = $photo_sizes[$size]['width'].'px';
		
		if ($this->xflickr->config['zooming']) {
			$chunk_props['photo_tpl'] = '
			<a class="'.$chunk_props['id'].'" href="'.$chunk_props['larger_url'].'" title="<b>'.$chunk_props['title'].'</b> '.$chunk_props['description'].'" >
				<img src="'.$chunk_props['url'].'" width="'.$chunk_props['width'].'" height="'.$chunk_props['height'].'" alt="'.$chunk_props['title'].'" />
			</a>	
			';
		} else {
			$chunk_props['photo_tpl'] = '<img src="'.$chunk_props['url'].'" width="'.$chunk_props['width'].'" height="'.$chunk_props['height'].'" alt="'.$chunk_props['title'].'" />';
		}
		$chunk_props['flickr_icon'] = $this->xflickr->config['css_url'].'images/flickr.png';
		$chunk = $this->xflickr->getChunk('xflickrsinglephoto', $this->xflickr->config['tpl']);
		return $chunk->process($chunk_props);
	}
}
<?php
/**
 * This file is the main class file for XFlickr.
 *
 * @copyright Copyright (C) 2009, atma <atma@atmaworks.com>
 * @author atma <atma@atmaworks.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package xflickr
 */
class XFlickr {
	/**
	 * @access protected
	 * @var array A collection of preprocessed chunk values.
	 */
	protected $chunks = array();
	/**
	 * @access public
	 * @var modX A reference to the modX object.
	 */
	public $modx = NULL;
	/**
	 * @access public
	 * @var array A collection of properties to adjust XFlickr behaviour.
	 */
	public $config = array();
	/**
	 *@access protected
	 *@var string A flickr API key
	 */
	protected $api_key = NULL;
	/**
	 *@access protected
	 *@var string Secret of API key
	 */
	protected $api_secret = NULL;
	/**
	 *@access protected
	 *@var string A token
	 */
	protected $token = NULL;
	/**
	 *@access public
	 *@var string flickr rest API url
	 */
	public $rest_url = 'http://flickr.com/services/rest/';
	/**
	 *@access public
	 *@var string flickr error message
	 */
	public $err_message = NULL;
	/**
	 *@access public
	 *@var int flickr error code
	 */
	public $err_code = NULL;
	/**
	 *@access public
	 *@var boolean cache flag
	 */
	public $use_cache = TRUE;

	/**
	 * The XFlickr Constructor.
	 *
	 * This method is used to create a new XFlickr object.
	 *
	 * @param modX &$modx A reference to the modX object.
	 * @param array $config A collection of properties that modify XFlickr
	 * behaviour.
	 * @return XFlickr A unique XFlickr instance.
	 */
	function __construct(modX &$modx,array $config = array()) {
		$this->modx =& $modx;

		$core = $this->modx->getOption('core_path').'components/xflickr/';
		$assets_url = $this->modx->getOption('assets_url').'components/xflickr/';
		$assets_path = $this->modx->getOption('assets_path').'components/xflickr/';
		$this->api_key = $this->modx->getOption('xflickr.api_key');
		$this->api_secret = $this->modx->getOption('xflickr.api_secret');
		$this->token = $this->modx->getOption('xflickr.token');
		$this->config = array_merge(array(
            'core_path' => $core,
            'model_path' => $core.'model/',
            'processors_path' => $core.'processors/',
            'controllers_path' => $core.'controllers/',
            'chunks_path' => $core.'chunks/',
            'base_url' => $assets_url,
            'css_url' => $assets_url.'css/',
            'js_url' => $assets_url.'js/',
            'connector_url' => $assets_url.'connector.php',

		),$config);

		$this->modx->addPackage('xflickr',$this->config['model_path']);
		if ($this->modx->lexicon) {
			$this->modx->lexicon->load('xflickr:default');
		}

		/* load debugging settings */
		if ($this->modx->getOption('debug',$this->config,false)) {
			error_reporting(E_ALL); ini_set('display_errors',true);
			$this->modx->setLogTarget('HTML');
			$this->modx->setLogLevel(MODX_LOG_LEVEL_ERROR);

			$debugUser = $this->config['debugUser'] == '' ? $this->modx->user->get('username') : 'anonymous';
			$user = $this->modx->getObject('modUser',array('username' => $debugUser));
			if ($user == null) {
				$this->modx->user->set('id',$this->modx->getOption('debugUserId',$this->config,1));
				$this->modx->user->set('username',$debugUser);
			} else {
				$this->modx->user = $user;
			}
		}
	}

	/**
	 * Initializes XFlickr based on a specific context.
	 *
	 * @access public
	 * @param string $ctx The context to initialize in.
	 * @return string The processed content.
	 */
	public function initialize($ctx = 'mgr', $mode = NULL) {
		$output = '';
		switch ($ctx) {
			case 'mgr':
				if (!$this->modx->loadClass('xflickr.request.XFlickrControllerRequest',$this->config['model_path'],true,true)) {
					return 'Could not load controller request handler.';
				}
				$this->request = new XFlickrControllerRequest($this);
				$output = $this->request->handleRequest();
				break;
			default:
				if ($mode == 'gallery') {
					if (!$this->modx->loadClass('xflickr.request.XFlickrGalleryRequest',$this->config['model_path'],true,true)) {
						return 'Could not load gallery request handler.';
					}
					$this->request = new XFlickrGalleryRequest($this);
					$output = $this->request->handle();
				} elseif ($mode == 'photo') {
					if (!$this->modx->loadClass('xflickr.request.XFlickrPhotoRequest',$this->config['model_path'],true,true)) {
						return 'Could not load photo request handler.';
					}
					$this->request = new XFlickrPhotoRequest($this);
					$output = $this->request->handle();
				}

				break;
		}
		return $output;
	}

	/**
	 * Return a flickr API key
	 *
	 * @access public
	 * @return string flickr API key.
	 */
	public function getKey() {
		if (!empty($this->api_key)) {
			return $this->api_key;
		} else {
			return FALSE;
		}
	}

	/**
	 * Set a flickr API key
	 *
	 * @access public
	 * @return boolean result.
	 */
	public function setKey($key) {
		if (!empty($key)) {
			$this->api_key = $key;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Return a flickr API shared secret
	 *
	 * @access public
	 * @return string flickr API shared secret.
	 */
	public function getSecret() {
		if (!empty($this->api_secret)) {
			return $this->api_secret;
		} else {
			return FALSE;
		}
	}

	/**
	 * Set a flickr API key
	 *
	 * @access public
	 * @return boolean result.
	 */
	public function setSecret($secret) {
		if (!empty($secret)) {
			$this->api_secret = $secret;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Return authenticated token
	 *
	 * @access public
	 * @return string flickr authenticated token.
	 */
	public function getToken() {
		if (!empty($this->token)) {
			return $this->token;
		} else {
			return FALSE;
		}
	}

	/**
	 * Set a flickr error
	 *
	 * @access public
	 * @return boolean result.
	 */
	public function setError($message, $code = 0) {
		if (!empty($message)) {
			$this->err_message = $message;
			$this->err_code = $code;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Get a flickr error
	 *
	 * @access public
	 * @return array error
	 */
	public function getError() {
		$error = array();
		$error['message'] = $this->err_message;
		$error['code'] = $this->err_code;
		return $error;
	}
	
    /**
     * Submit a POST request with to the specified URL with given parameters.
     *
     * @param   string $url
     * @param   array $params An optional array of parameter name/value
     *          pairs to include in the POST.
     * @param   integer $timeout The total number of seconds, including the
     *          wait for the initial connection, wait for a request to complete.
     * @return  string
     * @uses    TIMEOUT_CONNECTION to determine how long to wait for a
     *          for a connection.
     * @uses    TIMEOUT_TOTAL to determine how long to wait for a request
     *          to complete.
     * @uses    set_time_limit() to ensure that PHP's script timer is five
     *          seconds longer than the sum of $timeout and TIMEOUT_CONNECTION.
     */
    static function submitHttpPost($url, $postParams = null, $timeout = 50) {
        $ch = curl_init();

        // set up the request
        curl_setopt($ch, CURLOPT_URL, $url);
        // make sure we submit this as a post
        curl_setopt($ch, CURLOPT_POST, true);
        if (isset($postParams)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        }else{
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");        	
        }
        // make sure problems are caught
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        // return the output
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // set the timeouts
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
        // set the PHP script's timeout to be greater than CURL's
        set_time_limit($timeout + 25);

        $result = curl_exec($ch);
        // check for errors
        if (0 == curl_errno($ch)) {
            curl_close($ch);
            return $result;
        } else {
			$this->modx->log(MODX_LOG_LEVEL_ERROR,'XFlickr Request failed. '.curl_errno($ch).': '. curl_error($ch).' URL:'.$url);
            curl_close($ch);
        }
    }

    /**
     * Create a signed signature of the parameters.
     * Return a parameter string that can be tacked onto the end of a URL.
     *
     * @param   array   $params
     * @return  string
     * @link    http://flickr.com/services/api/auth.spec.html
     */
    public function signParams($params) {
        $signing = '';
        $values = array();
		$secret = $this->getSecret();
        ksort($params);
        foreach($params as $key => $value) {
			$signing .= $key . $value;
			$values[] = $key . '=' . urlencode($value);
        }
        $values[] = 'api_sig=' . md5($secret . $signing);
        return implode('&', $values);
    }

    /**
     * Return an array of the API's parameters for use with a flickr request.
     *
     * @return  array
     * @see     XFlickr::buildUrl()
     */
    public function getParamsForRequest() {
        $params['api_key'] = $this->api_key;
        if ($this->token) {
            $params['auth_token'] = $this->token;
        }
        return $params;
    }

    /**
     * Build a signed URL for flickr request.
     *
     * @return  string
     * @uses    signParams() to create a signed URL.
     */
    public function buildUrl(array $params) {
        $params = array_merge(
            $this->getParamsForRequest(),
            $params
        );
		if (!isset($params['format']))
			$params['format'] = 'php_serial';
        return $this->rest_url . '?' . $this->signParams($params);
    }

    /**
     * Build a URL to request a token.
     *
     * @param   string $perms The desired permissions 'read', 'write', or
     *          'delete'.
     * @param   string $frob optional Frob
     * @param   string $key optional API key
     * @param   string $secret optional shared secret
     * @return  string auth url
     * @see     requestFrob()
     * @uses    signParams() to create a signed URL.
     */
    function buildAuthUrl($frob ='', $key = '', $secret = '', $perms = 'delete') {
		$params = array();
        if ($frob != '') {
            $params['frob'] = $frob;
        }
		if ($key == '') {
			$key = $this->modx->getOption('xflickr.api_key');
		}
		if ($secret == '') {
			$secret = $this->modx->getOption('xflickr.api_secret');
		}
		$params['api_key'] =  $key;
		$params['perms'] =  $perms;
        //return 'http://flickr.com/services/auth/?'.$this->signParams($params);
        $signing = '';
        $values = array();
        ksort($params);
        foreach($params as $key => $value) {
			$signing .= $key . $value;
			$values[] = $key . '=' . urlencode($value);
        }
        $values[] = 'api_sig=' . md5($secret . $signing);
        return 'http://flickr.com/services/auth/?'.implode('&', $values);
    }

	/**
     * Execute a Flickr API method.
     *
     * All requests are cached using md5($url) as key.
     *
     * @params   array Array of params for flickr request, such as method, perms etc.
     * @return  mixed Flickr API response.
     * @uses    buildUrl() to get wellformed signed url.
     * @uses    submitHttpPost() to submit the request.
     */
    public function execute(array $params, $lifetime = 7200)
    {
        $url = $this->buildUrl($params);
		$cache = $this->modx->getCacheManager();
		$cache_key = md5($url);
		if ($this->use_cache == TRUE) {
			if ($cache->get('xflickr/'.$cache_key)) {
				$result = $cache->get('xflickr/'.$cache_key);
			} else {
				$result = $this->submitHttpPost($url);
				$cache->set('xflickr/'.$cache_key, $result, $lifetime);
			}
		} else {
			$result = $this->submitHttpPost($url);
			$cache_result = $cache->replace('xflickr/'.$cache_key, $result, $lifetime);
			if($cache_result == FALSE) {
				$cache->set('xflickr/'.$cache_key, $result, $lifetime);
			}
		}
        return $result;
    }

    /**
     * Clear the cache for xflickr
     *
     * @return boolean
     */
    public function clear_cache() {
		$cache = $this->modx->getCacheManager();
        if ($cache->clearCache(array('xflickr/'))) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Check if current authentication info is valid.
     *
     * @return  boolean
     * @uses    getUserId() To determine if the authentication is valid.
     */
    public function isAuthValid() {
        if (is_null($this->getUserId())) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Check if current authentication info is valid.
     *
     * @return  boolean
     * @uses    getUserId() To determine if the authentication is valid.
     */
    public function isPro($user_id = NULL) {
		if (is_null($user_id)) {
			$user_id = $this->getUserId();
		}
        $user_info = $this->people_getInfo($user_id);
		if ($user_info['ispro'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Return the Flickr user id of the current authenticated user.
     *
     * If the authentication info is incorrect, NULL will be returned.
     *
     * @return  string
     * @uses    execute() to call flickr.auth.checkToken
     */
    public function getUserId() {
        $response = $this->execute(array('method' => 'flickr.auth.checkToken'), 60);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['auth']['user']['nsid'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
    }

    /**
     * Request a frob used to get a token.
     *
     * @return  string
     * @link    http://flickr.com/services/api/flickr.auth.getFrob.html
     * @uses    execute() with flickr.auth.getFrob method
     */
    function requestFrob() {
        $response = $this->execute(array('method' => 'flickr.auth.getFrob', 'perms' => 'delete'), 60);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['frob']['_content'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
    }

    /**
     * Set the auth token from a frob.
     *
     * The user needed to authenticate the frob.
     *
     * @param   string $frob
     * @return  string The new token
     * @uses    execute() to call flickr.auth.getToken
     */
    public function getTokenFromFrob($frob) {
        $response = $this->execute(array('method' => 'flickr.auth.getToken', 'frob' => $frob), 5);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['auth']['token']['_content'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
    }
	
	/* Contacts methods */
	
	/**
     * Get a list of contacts for the calling user.
     *
     * @param   string $filter An optional filter of the results. The following values are valid: friends, family, both, neither
     * @param   int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 1000.
     * @param   int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.contacts.getList.html
     */
	function contacts_getList ($per_page = 25, $page = 1, $filter = NULL) {
        $response = $this->execute(array('method' => 'flickr.contacts.getList', 'per_page' => $per_page, 'page' => $page, 'filter' => $filter));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['contacts'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}
	
	
	
	/* Favorites methods */
	
	/**
     * Adds a photo to a user's favorites list.
     *
     * @param   int $photo_id The id of the photo to add to the user's favorites.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.favorites.add.html
     */
	function favorites_add ($photo_id) {
        $response = $this->execute(array('method' => 'flickr.favorites.add', 'photo_id' => $photo_id), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}
	
	/**
     * Returns a list of the user's favorite photos. Only photos which the calling user has permission to see are returned.
     *
     * @param   string $user_id The NSID of the user to fetch the favorites list for. If this argument is omitted, the favorites list for the calling user is returned.
     * @param int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @return  array List of favorited photos
     * @link    http://www.flickr.com/services/api/flickr.favorites.getList.html
     */
	function favorites_getList ($user_id = NULL, $per_page = 25, $page = 1, $extras = NULL) {
		$params = array();
		$params['method'] = 'flickr.favorites.getList';
		if ($user_id) $params['user_id'] = $user_id;
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if ($extras) $params['extras'] = $extras;
        $response = $this->execute($params, 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}
	
	/**
     * Returns a list of favorite public photos for the given user.
     *
     * @param   string $user_id The NSID of the user to fetch the favorites list for. If this argument is omitted, the favorites list for the calling user is returned.
     * @param int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @return  array List of favorited photos
     * @link    http://www.flickr.com/services/api/flickr.favorites.getPublicList.html
     */
	function favorites_getPublicList ($user_id = NULL, $per_page = 25, $page = 1, $extras = NULL) {
		$user_id = ($user_id) ? $user_id : $this->getUserId();
		$params = array();
		$params['method'] = 'flickr.favorites.getPublicList';
		$params['user_id'] = $user_id;
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if ($extras) $params['extras'] = $extras;
        $response = $this->execute($params, 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}
	
	/**
     * Removes a photo from a user's favorites list.
     *
     * @param   int $photo_id The id of the photo to remove from the user's favorites.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.favorites.remove.html
     */
	function favorites_remove ($photo_id) {
        $response = $this->execute(array('method' => 'flickr.favorites.remove', 'photo_id' => $photo_id), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}
	
	/* People methods */
	
    /**
     * Return a user's NSID, given their email address
     *
     * @param   string $find_email
     * @return  string user's NSID
     * @link    http://www.flickr.com/services/api/flickr.people.findByEmail.html
     */
	function people_findByEmail($find_email) {
        $response = $this->execute(array('method' => 'flickr.people.findByEmail', 'find_email' => $find_email));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['user']['nsid'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Return a user's NSID, given their username.
     *
     * @param   string $username
     * @return  string user's NSID
     * @link    http://www.flickr.com/services/api/flickr.people.findByUsername.html
     */
	function people_findByUsername ($username) {
        $response = $this->execute(array('method' => 'flickr.people.findByUsername', 'username' => $username));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['user']['nsid'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Get information about a user.
     *
     * @param   string $user_id
     * @return  array user information
     * @link    http://www.flickr.com/services/api/flickr.people.getInfo.html
     */
	function people_getInfo ($user_id) {
        $response = $this->execute(array('method' => 'flickr.people.getInfo', 'user_id' => $user_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['person'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns the list of public groups a user is a member of.
     *
     * @param   string The NSID of the user to fetch groups for.
     * @return  array list of public groups
     * @link    http://www.flickr.com/services/api/flickr.people.getPublicGroups.html
     */
	function people_getPublicGroups ($user_id) {
        $response = $this->execute(array('method' => 'flickr.people.getPublicGroups', 'user_id' => $user_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['groups']['group'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Get a list of public photos for the given user.
     *
     * @param   string $user_id The NSID of the user to fetch groups for.
     * @return  array list of public photos
     * @link    http://www.flickr.com/services/api/flickr.people.getPublicPhotos.html
     */
	function people_getPublicPhotos ($user_id, $per_page = 25, $page = 1, $safe_search = 1, $extras = NULL) {
		$params = array();
		$params['method'] = 'flickr.people.getPublicPhotos';
		$params['user_id'] = $user_id;
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		$params['safe_search'] = $safe_search;
		if ($extras) $params['extras'] = $extras;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns information for the calling user related to photo uploads.
     *
     * @return  array account info for authenticated user
     * @link    http://www.flickr.com/services/api/flickr.people.getUploadStatus.html
     */
	function people_getUploadStatus () {
        $response = $this->execute(array('method' => 'flickr.people.getUploadStatus'));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['user'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

	/* Photos methods */

    /**
     * Add tags to a photo.
     *
     * @param   string $photo_id The id of the photo to add tags to.
     * @param	string $tags The tags to add to the photo.
     * @return  boolean
     * @link    http://www.flickr.com/services/api/flickr.photos.addTags.html
     */
	function photos_addTags ($photo_id, $tags) {
        $response = $this->execute(array('method' => 'flickr.photos.addTags', 'photo_id' => $photo_id, 'tags' => $tags));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Delete a photo from flickr.
     *
     * @param   string $photo_id The id of the photo to delete.
     * @return  array list of public groups
     * @link    http://www.flickr.com/services/api/flickr.photos.delete.html
     */
	function photos_delete ($photo_id) {
        $response = $this->execute(array('method' => 'flickr.photos.delete', 'photo_id' => $photo_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Returns all visible sets and pools the photo belongs to.
     *
     * @param   string $photo_id The photo to return information for.
     * @return  array list of public groups
     * @link    http://www.flickr.com/services/api/flickr.photos.getAllContexts.html
     */
	function photos_getAllContexts ($photo_id) {
        $response = $this->execute(array('method' => 'flickr.photos.getAllContexts', 'photo_id' => $photo_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			unset($object['stat']);
			return $object;
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Fetch a list of recent photos from the calling users' contacts.
     *
     * @param   int $count Number of photos to return. Defaults to 10, maximum 50. This is only used if single_photo is not passed.
     * @param   int $just_friends set as 1 to only show photos from friends and family (excluding regular contacts).
     * @param   int $single_photo Only fetch one photo (the latest) per contact, instead of all photos in chronological order.
     * @param   int $include_self Set to 1 to include photos from the calling user.
     * @param   string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update.
     * @return  array list of public photos
     * @link    http://www.flickr.com/services/api/flickr.photos.getContactsPhotos.html
     */
	function photos_getContactsPhotos ($count = 10, $just_friends = NULL, $single_photo = NULL, $include_self = NULL, $extras = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getContactsPhotos';
		$params['count'] = $count;
		if ($just_friends) $params['just_friends'] = $just_friends;
		if ($single_photo) $params['single_photo'] = $single_photo;
		if ($include_self) $params['include_self'] = $include_self;
		if ($extras) $params['extras'] = $extras;
        $response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Fetch a list of recent public photos from a users' contacts.
     *
     * @param	string $user_id The NSID of the user to fetch photos for.
     * @param   int $count Number of photos to return. Defaults to 10, maximum 50. This is only used if single_photo is not passed.
     * @param   int $just_friends set as 1 to only show photos from friends and family (excluding regular contacts).
     * @param   int $single_photo Only fetch one photo (the latest) per contact, instead of all photos in chronological order.
     * @param   int $include_self Set to 1 to include photos from the calling user.
     * @param   string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update.
     * @return  array list of public photos for contact
     * @link    http://www.flickr.com/services/api/flickr.photos.getContactsPublicPhotos.html
     */
	function photos_getContactsPublicPhotos ($user_id, $count = 10, $just_friends = NULL, $single_photo = NULL, $include_self = NULL, $extras = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getContactsPublicPhotos';
		$params['user_id'] = $user_id;
		$params['count'] = $count;
		if ($just_friends) $params['just_friends'] = $just_friends;
		if ($single_photo) $params['single_photo'] = $single_photo;
		if ($include_self) $params['include_self'] = $include_self;
		if ($extras) $params['extras'] = $extras;
        $response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns next and previous photos for a photo in a photostream.
     *
     * @param	string $photo_id The id of the photo to fetch the context for.
     * @return  array prevphoto & nextphoto for requested photo
     * @link    http://www.flickr.com/services/api/flickr.photos.getContext.html
     */
	function photos_getContext ($photo_id) {
        $response = $this->execute(array('method' => 'flickr.photos.getContext', 'photo_id' => $photo_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			unset($object['stat']);
			return $object;
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Gets a list of photo counts for the given date ranges for the calling user.
     *
     * @param	string $dates A comma delimited list of unix timestamps, denoting the periods to return counts for. They should be specified smallest first.
     * @param	string $taken_dates A comma delimited list of mysql datetimes, denoting the periods to return counts for. They should be specified smallest first.
     * @return  array counts
     * @link    http://www.flickr.com/services/api/flickr.photos.getCounts.html
     */
	function photos_getCounts ($dates = NULL, $taken_dates = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getCounts';
		if (!$dates) {
			$dates = time()-31536000 .','. time();
			$params['dates'] = $dates;
		}
		if ($taken_dates) $params['taken_dates'] = $taken_dates;
        $response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photocounts']['photocount'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Retrieves a list of EXIF/TIFF/GPS tags for a given photo. The calling user must have permission to view the photo.
     *
     * @param	string $photo_id The id of the photo to fetch information for.
     * @param	string $secret The secret for the photo. If the correct secret is passed then permissions checking is skipped. This enables the 'sharing' of individual photos by passing around the id and secret.
     * @return  array EXIF data
     * @link    http://www.flickr.com/services/api/flickr.photos.getExif.html
     */
	function photos_getExif ($photo_id, $secret = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getExif';
		$params['photo_id'] = $photo_id;
		if ($secret) $params['secret'] = $secret;
        $response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photo'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns the list of people who have favorited a given photo.
     *
     * @param	string $photo_id The ID of the photo to fetch the favoriters list for.
     * @param	int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param	int $per_page Number of usres to return per page. If this argument is omitted, it defaults to 20. The maximum allowed value is 50.
     * @return  array list of persons
     * @link    http://www.flickr.com/services/api/flickr.photos.getFavorites.html
     */
	function photos_getFavorites ($photo_id, $page = 1, $per_page = 20) {
        $response = $this->execute(array('method' => 'flickr.photos.getFavorites', 'photo_id' => $photo_id, 'page' => $page, 'per_page' => $per_page));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photo']['person'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Get information about a photo.
     *
     * @param   string $photo_id The id of the photo to get information for
     * @param   string $secret The secret for the photo. If the correct secret is passed then permissions checking is skipped. This enables the 'sharing' of individual photos by passing around the id and secret.
     * @return  array information about a photo
     * @link    http://www.flickr.com/services/api/flickr.photos.getInfo.html
     */
	function photos_getInfo ($photo_id, $secret = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getInfo';
		$params['photo_id'] = $photo_id;
		if ($secret) $params['secret'] = $secret;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photo'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns a list of your photos that are not part of any sets.
     *
     * @param   int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param   int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param   int $min_upload_date unix timestamp
     * @param   int $max_upload_date unix timestamp
     * @param   int $min_taken_date mysql datetime
     * @param   int $max_taken_date mysql datetime
     * @param   int $privacy_filter Return photos only matching a certain privacy level. Valid values are: 1,2,3,4,5
     * @param   string $media Filter results by media type. Possible values are all (default), photos or videos
     * @param	string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @return  array list of photos that are not part of any sets
     * @link    http://www.flickr.com/services/api/flickr.photos.getNotInSet.html
     */
	function photos_getNotInSet ($per_page = 25, $page = 1, $min_upload_date = NULL, $max_upload_date = NULL, $min_taken_date = NULL, $max_taken_date = NULL, $privacy_filter = 1, $media = 'all', $extras = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getNotInSet';
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if ($min_upload_date) $params['min_upload_date'] = $min_upload_date;
		if ($max_upload_date) $params['max_upload_date'] = $max_upload_date;
		if ($min_taken_date) $params['min_taken_date'] = $min_taken_date;
		if ($max_taken_date) $params['max_taken_date'] = $max_taken_date;
		if ($privacy_filter) $params['privacy_filter'] = $privacy_filter;
		if ($media) $params['media'] = $media;
		if ($extras) $params['extras'] = $extras;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Get permissions for a photo.
     *
     * @param   string $photo_id The id of the photo to get permissions for.
     * @return  array permissions for a photo
     * @link    http://www.flickr.com/services/api/flickr.photos.getPerms.html
     */
	function photos_getPerms ($photo_id) {
		$response = $this->execute(array('method' => 'flickr.photos.getPerms', 'photo_id' => $photo_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['perms'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns a list of the latest public photos uploaded to flickr.
     *
     * @param   string $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param   string $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param	string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @return  array list of public photos
     * @link    http://www.flickr.com/services/api/flickr.photos.getRecent.html
     */
	function photos_getRecent ($per_page = 25, $page = 1, $extras = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getRecent';
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if ($extras) $params['extras'] = $extras;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns the available sizes for a photo. The calling user must have permission to view the photo.
     *
     * @param   string $photo_id The id of the photo to fetch size information for.
     * @return  array available sizes.
     * @link    http://www.flickr.com/services/api/flickr.photos.getSizes.html
     */
	function photos_getSizes ($photo_id) {
		$response = $this->execute(array('method' => 'flickr.photos.getSizes', 'photo_id' => $photo_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['sizes']['size'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns the available sizes for a photo with max key.
     *
     * @param   string $photo_id The id of the photo to fetch size information for.
     * @return  array available sizes.
     * @uses XFlickr::photos_getSizes()
     */
	function photos_getNamedSizes ($photo_id) {
		$sizes = $this->photos_getSizes($photo_id);
		if ($sizes) {
			$namedSizes = array();
			foreach ($sizes as $size) {
				$namedSizes[$size['label']] = $size;
			}
			$namedSizes['Max'] = array_pop($sizes);
			$namedSizes['Max']['label'] = 'Max';
			return $namedSizes;
		} else {
			return NULL;
		}
	}

    /**
     * Returns a list of your photos with no tags.
     *
     * @param   int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param   int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param   int $min_upload_date unix timestamp
     * @param   int $max_upload_date unix timestamp
     * @param   int $min_taken_date mysql datetime
     * @param   int $max_taken_date mysql datetime
     * @param   int $privacy_filter Return photos only matching a certain privacy level. Valid values are: 1,2,3,4,5
     * @param   string $media Filter results by media type. Possible values are all (default), photos or videos
     * @param	string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @return  array list of your photos with no tags
     * @link    http://www.flickr.com/services/api/flickr.photos.getUntagged.html
     */
	function photos_getUntagged ($per_page = 25, $page = 1, $min_upload_date = NULL, $max_upload_date = NULL, $min_taken_date = NULL, $max_taken_date = NULL, $privacy_filter = 1, $media = all, $extras = NULL, $sort = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getUntagged';
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if ($min_upload_date) $params['min_upload_date'] = $min_upload_date;
		if ($max_upload_date) $params['max_upload_date'] = $max_upload_date;
		if ($min_taken_date) $params['min_taken_date'] = $min_taken_date;
		if ($max_taken_date) $params['max_taken_date'] = $max_taken_date;
		if ($privacy_filter) $params['privacy_filter'] = $privacy_filter;
		if ($media) $params['media'] = $media;
		if ($extras) $params['extras'] = $extras;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns a list of your geo-tagged photos.
     *
     * @param   int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param   int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param   int $min_upload_date unix timestamp
     * @param   int $max_upload_date unix timestamp
     * @param   int $min_taken_date mysql datetime
     * @param   int $max_taken_date mysql datetime
     * @param   int $privacy_filter Return photos only matching a certain privacy level. Valid values are: 1,2,3,4,5
     * @param   string $media Filter results by media type. Possible values are all (default), photos or videos
     * @param	string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @param string $sort The order in which to sort returned photos. Deafults to date-posted-desc. The possible values are: date-posted-asc, date-posted-desc, date-taken-asc, date-taken-desc, interestingness-desc, and interestingness-asc.
     * @return  array list of your geo-tagged photos
     * @link    http://www.flickr.com/services/api/flickr.photos.getWithGeoData.html
     */
	function photos_getWithGeoData ($per_page = 25, $page = 1, $min_upload_date = NULL, $max_upload_date = NULL, $min_taken_date = NULL, $max_taken_date = NULL, $privacy_filter = 1, $media = all, $extras = NULL, $sort = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getWithGeoData';
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if ($min_upload_date) $params['min_upload_date'] = $min_upload_date;
		if ($max_upload_date) $params['max_upload_date'] = $max_upload_date;
		if ($min_taken_date) $params['min_taken_date'] = $min_taken_date;
		if ($max_taken_date) $params['max_taken_date'] = $max_taken_date;
		if ($privacy_filter) $params['privacy_filter'] = $privacy_filter;
		if ($media) $params['media'] = $media;
		if ($extras) $params['extras'] = $extras;
		if ($sort) $params['sort'] = $sort;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns a list of your geo-tagged photos.
     *
     * @param   int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param   int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param   int $min_upload_date unix timestamp
     * @param   int $max_upload_date unix timestamp
     * @param   int $min_taken_date mysql datetime
     * @param   int $max_taken_date mysql datetime
     * @param   int $privacy_filter Return photos only matching a certain privacy level. Valid values are: 1,2,3,4,5
     * @param   string $media Filter results by media type. Possible values are all (default), photos or videos
     * @param	string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @param string $sort The order in which to sort returned photos. Deafults to date-posted-desc. The possible values are: date-posted-asc, date-posted-desc, date-taken-asc, date-taken-desc, interestingness-desc, and interestingness-asc.
     * @return  array list of your photos which haven't been geo-tagged
     * @link    http://www.flickr.com/services/api/flickr.photos.getWithoutGeoData.html
     */
	function photos_getWithoutGeoData ($per_page = 25, $page = 1, $min_upload_date = NULL, $max_upload_date = NULL, $min_taken_date = NULL, $max_taken_date = NULL, $privacy_filter = 1, $media = all, $extras = NULL, $sort = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.getWithoutGeoData';
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if ($min_upload_date) $params['min_upload_date'] = $min_upload_date;
		if ($max_upload_date) $params['max_upload_date'] = $max_upload_date;
		if ($min_taken_date) $params['min_taken_date'] = $min_taken_date;
		if ($max_taken_date) $params['max_taken_date'] = $max_taken_date;
		if ($privacy_filter) $params['privacy_filter'] = $privacy_filter;
		if ($media) $params['media'] = $media;
		if ($extras) $params['extras'] = $extras;
		if ($sort) $params['sort'] = $sort;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Return a list of your photos that have been recently created or which have been recently modified.
     *
     * @param   int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param   int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param   int $min_date unix timestamp
     * @param	string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @return  array list of your recently modified photos 
     * @link    http://www.flickr.com/services/api/flickr.photos.recentlyUpdated.html
     */
	function photos_recentlyUpdated ($per_page = 25, $page = 1, $min_date = NULL,  $extras = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.recentlyUpdated';
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if (!$min_date) {
			$params['min_date'] = time()-604800;;
		}
		if ($extras) $params['extras'] = $extras;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Remove a tag from a photo.
     *
     * @param   string $tag_id The tag to remove from the photo. This parameter should contain a tag id, as returned by XFlickr::photos_getInfo()
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.removeTag.html
     */
	function photos_removeTag ($tag_id) {
		$response = $this->execute(array('method' => 'flickr.photos.removeTag', 'tag_id' => $tag_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Return a list of photos matching some criteria. Only photos visible to the calling user will be returned.
     *
     * @param   int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param   int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param   string $tags A comma-delimited list of tags.
     * @param   string $text A free text search. Photos who's title, description or tags contain the text will be returned.
     * @param   int $min_upload_date unix timestamp
     * @param   int $max_upload_date unix timestamp
     * @param   int $min_taken_date mysql datetime
     * @param   int $max_taken_date mysql datetime
     * @param   int $privacy_filter Return photos only matching a certain privacy level. Valid values are: 1,2,3,4,5
     * @param   string $media Filter results by media type. Possible values are all (default), photos or videos
     * @param	string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @param string $sort The order in which to sort returned photos. Deafults to date-posted-desc. The possible values are: date-posted-asc, date-posted-desc, date-taken-asc, date-taken-desc, interestingness-desc, and interestingness-asc.
     * @return  array list of your photos matching your criteria
     * @link    http://www.flickr.com/services/api/flickr.photos.search.html
     */
	function photos_search ($per_page = 25, $page = 1, $tags = NULL, $text = NULL, $min_upload_date = NULL, $max_upload_date = NULL, $min_taken_date = NULL, $max_taken_date = NULL, $privacy_filter = 1, $media = all, $extras = NULL, $sort = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.search';
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		if ($tags) {
			$tags = explode(',', $tags);
			array_map('trim', $tags);
			$tags = implode(',', $tags);
			$params['tags'] = $tags;
		}
		if ($text) $params['text'] = trim($text);
		if ($min_upload_date) $params['min_upload_date'] = $min_upload_date;
		if ($max_upload_date) $params['max_upload_date'] = $max_upload_date;
		if ($min_taken_date) $params['min_taken_date'] = $min_taken_date;
		if ($max_taken_date) $params['max_taken_date'] = $max_taken_date;
		if ($privacy_filter) $params['privacy_filter'] = $privacy_filter;
		if ($media) $params['media'] = $media;
		if ($extras) $params['extras'] = $extras;
		if ($sort) $params['sort'] = $sort;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photos'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Set the content type of a photo.
     *
     * @param   string $photo_id The id of the photo to set the adultness of.
     * @param   int $content_type The content type of the photo. Must be one of: 1 for Photo, 2 for Screenshot, and 3 for Other.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.setContentType.html
     */
	function photos_setContentType ($photo_id, $content_type = 1) {
		$response = $this->execute(array('method' => 'flickr.photos.setContentType', 'photo_id' => $photo_id, 'content_type' => $content_type));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}
 
     /**
     * Set one or both of the dates for a photo.
     *
     * @param   string $photo_id The id of the photo to edit dates for.
     * @param   int $date_posted The date the photo was uploaded to flickr, unix timestamp
     * @param   string $date_taken The date the photo was taken, mysql datetime
     * @param	int $date_taken_granularity The granularity of the date the photo was taken (0 - Y-m-d H:i:s, 4 - Y-m, 6 - Y)
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.setDates.html
	 * @link	http://www.flickr.com/services/api/misc.dates.html
     */
	function photos_setDates ($photo_id, $date_posted = NULL, $date_taken = NULL, $date_taken_granularity = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.setDates';
		$params['photo_id'] = $photo_id;
		if ($date_posted) $params['date_posted'] = $date_posted;
		if ($date_taken) $params['date_taken'] = $date_taken;
		if ($date_taken_granularity) $params['date_taken_granularity'] = $date_taken_granularity;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

     /**
     * Set the meta information for a photo.
     *
     * @param   string $photo_id The id of the photo to set information for.
     * @param   string $title The title for the photo.
     * @param   string $description The description for the photo.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.setMeta.html
     */
	function photos_setMeta ($photo_id, $title, $description) {
		$response = $this->execute(array('method' => 'flickr.photos.setMeta', 'photo_id' => $photo_id, 'title' => $title, 'description' => $description));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Set permissions for a photo.
     *
     * @param   string $photo_id The id of the photo to set permissions for.
     * @param   int $is_public 1 to set the photo to public, 0 to set it to private.
     * @param   int $is_friend 1 to make the photo visible to friends when private, 0 to not.
     * @param   int $is_family 1 to make the photo visible to family when private, 0 to not.
     * @param   int $perm_comment who can add comments to the photo and it's notes. (0: nobody, 1: friends & family, 2: contacts, 3: everybody)
     * @param   int $perm_addmeta who can add notes and tags to the photo. (0: nobody, 1: friends & family, 2: contacts, 3: everybody)
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.setPerms.html
     */
	function photos_setPerms ($photo_id, $is_public = 1, $is_friend = 0, $is_family = 0, $perm_comment = 3, $perm_addmeta = 3) {
		$response = $this->execute(array('method' => 'flickr.photos.setPerms', 'photo_id' => $photo_id, 'is_public' => $is_public, 'is_friend' => $is_friend, 'is_family' => $is_family, 'perm_comment' => $perm_comment, 'perm_addmeta' => $perm_addmeta));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Set the safety level of a photo.
     *
     * @param   string $photo_id The id of the photo to set the adultness of.
     * @param   string $safety_level The safety level of the photo. Must be one of: 1 for Safe, 2 for Moderate, and 3 for Restricted.
     * @param   string $hidden Whether or not to additionally hide the photo from public searches. Must be either 1 for Yes or 0 for No.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.setSafetyLevel.html
     */
	function photos_setSafetyLevel ($photo_id, $safety_level = 1, $hidden = 0) {
		$response = $this->execute(array('method' => 'flickr.photos.setSafetyLevel', 'photo_id' => $photo_id, 'safety_level' => $safety_level, 'hidden' => $hidden));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Set the tags for a photo.
     *
     * @param   string $photo_id The id of the photo to set tags for.
     * @param   string $tags All tags for the photo (as a single space-delimited string).
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.setTags.html
     */
	function photos_setTags ($photo_id, $tags) {
		$response = $this->execute(array('method' => 'flickr.photos.setTags', 'photo_id' => $photo_id, 'tags' => $tags));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}
	
	/* Photos::comments methods */

    /**
     * Add comment to a photo as the currently authenticated user.
     *
     * @param   string $photo_id The id of the photo to add a comment to.
     * @param   string $comment_text Text of the comment
     * @return  string new comment id
     * @link    http://www.flickr.com/services/api/flickr.photos.comments.addComment.html
     */
	function photos_comments_addComment ($photo_id, $comment_text) {
		$response = $this->execute(array('method' => 'flickr.photos.comments.addComment', 'photo_id' => $photo_id, 'comment_text' => $comment_text), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['comment']['id'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Delete a comment as the currently authenticated user.
     *
     * @param   string $comment_id The id of the comment to delete.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.comments.deleteComment.html
     */
	function photos_comments_deleteComment ($comment_id) {
		$response = $this->execute(array('method' => 'flickr.photos.comments.deleteComment', 'comment_id' => $comment_id), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Edit the text of a comment as the currently authenticated user.
     *
     * @param   string $comment_id The id of the comment to edit.
     * @param   string $comment_text Update the comment to this text.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.comments.editComment.html
     */
	function photos_comments_editComment ($comment_id, $comment_text) {
		$response = $this->execute(array('method' => 'flickr.photos.comments.editComment', 'comment_id' => $comment_id, 'comment_text' => $comment_text), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Returns the comments for a photo
     *
     * @param   string $photo_id The id of the photo to fetch comments for.
     * @param   int $min_comment_date Unix timestamp
     * @param   int $max_comment_date Unix timestamp
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photos.comments.getList.html
     */
	function photos_comments_getList ($photo_id, $min_comment_date = NULL, $max_comment_date = NULL) {
		$params = array();
		$params['method'] = 'flickr.photos.comments.getList';
		$params['photo_id'] = $photo_id;
		if ($min_comment_date) $params['min_comment_date'] = $min_comment_date;
		if ($max_comment_date) $params['max_comment_date'] = $max_comment_date;
		$response = $this->execute($params, 300);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['comments']['comment'];
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

	/* Photosets methods */

    /**
     * Add a photo to the end of an existing photoset.
     *
     * @param   string $photoset_id The id of the photoset to add a photo to.
     * @param   string $photo_id The id of the photo to add to the set.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photosets.addPhoto.html
     */
	function photosets_addPhoto ($photoset_id, $photo_id) {
		$response = $this->execute(array('method' => 'flickr.photosets.addPhoto', 'photoset_id' => $photoset_id, 'photo_id' => $photo_id), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Create a new photoset for the calling user.
     *
     * @param   string $title A title for the photoset.
     * @param   string $primary_photo_id The id of the photo to represent this set. The photo must belong to the calling user.
     * @param   string $description A description of the photoset.
     * @return  array id and url of new photoset
     * @link    http://www.flickr.com/services/api/flickr.photosets.create.html
     */
	function photosets_create ($title, $primary_photo_id, $description = NULL) {
		$params = array();
		$params['method'] = 'flickr.photosets.create';
		$params['title'] = $title;
		$params['primary_photo_id'] = $primary_photo_id;
		if ($description) $params['description'] = $description;
		$response = $this->execute($params, 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photoset'];
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Delete a photoset.
     *
     * @param   string $photoset_id The id of the photoset to delete. It must be owned by the calling user.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photosets.delete.html
     */
	function photosets_delete ($photoset_id) {
		$response = $this->execute(array('method' => 'flickr.photosets.delete', 'photoset_id' => $photoset_id), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Modify the meta-data for a photoset.
     *
     * @param   int $photoset_id The id of the photoset to modify.
     * @param   string $title The new title for the photoset.
     * @param   string $description The new description of the photoset.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photosets.editMeta.html
     */
	function photosets_editMeta ($photoset_id, $title, $description = NULL) {
		$params = array();
		$params['method'] = 'flickr.photosets.editMeta';
		$params['photoset_id'] = $photoset_id;
		$params['title'] = $title;
		if ($description) $params['description'] = $description;
		$response = $this->execute($params, 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Modify the photos in a photoset. Use this method to add, remove and re-order photos.
     *
     * @param   int $photoset_id The id of the photoset to modify. The photoset must belong to the calling user.
     * @param   int $primary_photo_id The id of the photo to use as the 'primary' photo for the set. This id must also be passed along in photo_ids list argument.
     * @param   string $photo_ids A comma-delimited list of photo ids to include in the set. This list of photos replaces the existing list.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photosets.delete.html
     */
	function photosets_editPhotos ($photoset_id, $photo_ids, $primary_photo_id = NULL) {
		$params = array();
		$params['method'] = 'flickr.photosets.editPhotos';
		$params['photoset_id'] = $photoset_id;
		$photo_ids = explode(',', $photo_ids);
		array_map('trim', $photo_ids);
		if (!$primary_photo_id) {
			$params['primary_photo_id'] = $photo_ids[0];
		} else {
			$params['primary_photo_id'] = $primary_photo_id;
		}
		$photo_ids = implode(',', $photo_ids);
		$params['photo_ids'] = $photo_ids;
		$response = $this->execute($params, 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Returns next and previous photos for a photo in a set.
     *
     * @param   int $photoset_id The id of the photoset for which to fetch the photo's context.
     * @param   int $photo_id The id of the photo to fetch the context for.
     * @return  array prevphoto/nextphoto
     * @link    http://www.flickr.com/services/api/flickr.photosets.getContext.html
     */
	function photosets_getContext ($photoset_id, $photo_id) {
		$response = $this->execute(array('method' => 'flickr.photosets.getContext', 'photoset_id' => $photoset_id, 'photo_id' => $photo_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			unset($object['stat']);
			return $object;
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Gets information about a photoset.
     *
     * @param   int $photoset_id The ID of the photoset to fetch information for.
     * @return  array photoset info
     * @link    http://www.flickr.com/services/api/flickr.photosets.getInfo.html
     */
	function photosets_getInfo ($photoset_id) {
		$response = $this->execute(array('method' => 'flickr.photosets.getInfo', 'photoset_id' => $photoset_id));
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photoset'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Returns the photosets belonging to the specified user.
     *
     * @param   string $user_id The NSID of the user to get a photoset list for. If none is specified, the calling user is assumed.
     * @return  array photosets list
     * @link    http://www.flickr.com/services/api/flickr.photosets.getList.html
     */
	function photosets_getList ($user_id = NULL) {
		$params = array();
		$params['method'] = 'flickr.photosets.getList';
		if ($user_id) $params['user_id'] = $user_id;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photosets']['photoset'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Get the list of photos in a set.
     *
     * @param   string $photoset_id The id of the photoset to return the photos for.
     * @param	int $per_page Number of photos to return per page. If this argument is omitted, it defaults to 25. The maximum allowed value is 500.
     * @param	int $page The page of results to return. If this argument is omitted, it defaults to 1.
     * @param	string $media Filter results by media type. Possible values are all, photos (default) or videos
     * @param	string $extras A comma-delimited list of extra information to fetch for each returned record. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o
     * @return  array list of photos in photoset
     * @link    http://www.flickr.com/services/api/flickr.photosets.getPhotos.html
     */
	function photosets_getPhotos ($photoset_id, $per_page = 25, $page = 1, $media = 'photos', $extras = NULL) {
		$params = array();
		$params['method'] = 'flickr.photosets.getPhotos';
		$params['photoset_id'] = $photoset_id;
		$params['per_page'] = $per_page;
		$params['page'] = $page;
		$params['media'] = $media;
		if ($extras) $params['extras'] = $extras;
		$response = $this->execute($params);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return $object['photoset'];
		} else {
			$this->setError($object['message'], $object['code']);
			return NULL;
		}
	}

    /**
     * Set the order of photosets for the calling user.
     *
     * @param   string $photoset_ids A comma delimited list of photoset IDs, ordered with the set to show first, first in the list. Any set IDs not given in the list will be set to appear at the end of the list, ordered by their IDs.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photosets.orderSets.html
     */
	function photosets_orderSets ($photoset_ids) {
		$params['method'] = 'flickr.photosets.orderSets';
		$response = $this->execute(array('method' => 'flickr.photosets.orderSets', 'photoset_ids' => $photoset_ids), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}

    /**
     * Remove a photo from a photoset.
     *
     * @param   string $photoset_id The id of the photoset to remove a photo from.
     * @param   string $photo_id The id of the photo to remove from the set.
     * @return  boolean result
     * @link    http://www.flickr.com/services/api/flickr.photosets.removePhoto.html
     */
	function photosets_removePhoto ($photoset_id, $photo_id) {
		$params['method'] = 'flickr.photosets.orderSets';
		$response = $this->execute(array('method' => 'flickr.photosets.removePhoto', 'photoset_id' => $photoset_id, 'photo_id' => $photo_id), 10);
		$object = unserialize($response);
		if ($object['stat'] == 'ok') {
			return TRUE;
		} else {
			$this->setError($object['message'], $object['code']);
			return FALSE;
		}
	}






	/**
	 * Get buddyicon url
	 * 
	 * @param string $user_id The NSID of the user to get a buddyicon for.
	 * @return string Icon url
	 * @link http://www.flickr.com/services/api/misc.buddyicons.html
	 */
	public function getBuddyicon ($user_id = NULL) {
		if (!$user_id) {
			$user_id = $this->getUserId();
		}
		$user_info = $this->people_getInfo($user_id);
		if ($user_info['iconfarm'] == 0) {
			$icon_url = 'http://www.flickr.com/images/buddyicon.jpg';
		} else {
			$icon_url = 'http://farm'.$user_info['iconfarm'].'.static.flickr.com/'.$user_info['iconserver'].'/buddyicons/'.$user_info['nsid'].'.jpg';
		}
		return $icon_url;
	}

	/**
	 * Get direct photo url for all available sizes
	 *
	 */
	public function getPhotoUrls () {
		
	}
	/**
	 * Build direct photo url without flickr API calls
	 *
	 */
	public function buildPhotoUrl () {
		
	}

	/**
	 * Returns the chunk content
	 *
	 * @access public
	 * @param string $defaultName Default name of the chunk to process (from file)
	 * @param string $name The name of the chunk to process
	 * @return string The chunk template
	 */
	public function getChunk($defaultName, $name = NULL) {
		if ($name) {
			$chunk = $this->modx->getObject('modChunk', array('name' => $name));
			if ($chunk) {
				return $chunk;
			}
		}
		$f = file_get_contents($this->config['chunks_path'].$defaultName.'.chunk.tpl');
		$chunk = $this->modx->newObject('modChunk');
		$chunk->setContent($f);
		return $chunk;
	}

	/**
	 * Builds simple pagination markup. Not yet used.
	 *
	 * TODO: add tpl configurability to li/a tags.
	 *
	 * @access public
	 * @param integer $count The total number of records
	 * @param integer $limit The number to limit to
	 * @param integer $start The record to start on
	 * @param string $url The URL to prefix a hrefs with
	 * @return string The rendered template.
	 */
	public function buildPagination($count,$limit,$start,$url) {
		$pageCount = $count / $limit;
		$curPage = $start / $limit;
		$pages = '';
		for ($i=0;$i<$pageCount;$i++) {
			$newStart = $i*$limit;
			$u = $url.'&start='.$newStart.'&limit='.$limit;
			if ($i != $curPage) {
				$pages .= '<li class="page-number"><a href="'.$u.'">'.($i+1).'</a></li>';
			} else {
				$pages .= '<li class="page-number pgCurrent">'.($i+1).'</li>';
			}
		}
		return $this->getChunk('xflickrPagination',array(
            'xflickr.pages' => $pages,
		));
	}
}
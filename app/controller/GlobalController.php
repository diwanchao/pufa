<?php
/**
 * 全局控制器基类
 * 
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.weile.com/
 * @copyright Copyright &copy; 2014 DongHai Hsing
 * @package 20_com.weile
 * @since 0.1
 * @version 0.1 (Create: 2014-04-23, Update: 2014-04-23)
 */
class GlobalController extends CController {
	/**
	 * 是否对提示信息进行翻译
	 *
	 * @access public
	 * @var boolean
	 */
	public $is_i18n = false;

	/**
	 * 提示信息输出格式
	 * httpquery | json
	 *
	 * @access public
	 * @var string
	 */
	public $output_type = 'json';

	/**
	 * HTML提示信息的默认输出模板
	 *
	 * @access public
	 * @var string
	 */
	public $html_view = 'message';

	/**
	 * 最后一次进行敏感词检查的命中词数组
	 *
	 * @access public
	 * @var array
	 */
	public $last_badwords_check_result = array();

	/**
	 * 是否为iOS访问
	 *
	 * @access public
	 */
	public $is_ios = false;

	/**
	 * 是否为Android访问
	 *
	 * @access public
	 */
	public $is_android = false;

	/**
	 * 是否为微信访问
	 *
	 * @access public
	 */
	public $is_wechar = false;

	/**
	 * 是否为webview
	 *
	 * @access public
	 * @var bool
	 */
	public $is_webview = false;

	/**
	 * 是否要在本控制器的构造方法中检查UA
	 *
	 * @access public
	 * @var bool
	 */
	public $check_ua = true;

	/**
	 * 初始化, 载入全局app配置文件并设置游戏服务器地址
	 *
	 * @access public
	 * @param $id
	 */
	public function __construct($id) {
		parent::__construct($id);

		if ($this->check_ua) {
			$ua = strtolower(Dh::app()->getRequester()->getUserAgent());
			if (preg_match('/(iphone|ipad|ipod|ios)/', $ua))
				$this->is_ios = true;
			else
				$this->is_android = true;
			$this->is_wechar = false !== strpos($ua, 'micromessenger');

			if (preg_match('/weileapp[\s]*:[\s]*yes/', $ua))
				$this->is_webview = true;
			elseif (preg_match('/Mobile\/[0-9a-zA-Z]{6,7}$/s', $ua))
				$this->is_webview = true;
		}
	}

	/**
	 * 输出
	 *
	 * @access public
	 * @param string $msg 提示信息
	 * @param integer $state 状态
	 * @param array $data 数据数组
	 */
	public function output($msg = '', $state = 51, $data = array()) {
		if ('httpquery' == $this->output_type) {
			$arr = array_merge(array('state' => $state, 'msg' => $msg), $data);
			$this->str(http_build_query($arr));
		} else {
			$this->json(array(
				'state' => $state,
				'msg' => $msg,
				'data' => $data
			), empty($_GET['jsonp']) || !is_string($_GET['jsonp']) ? null : trim($_GET['jsonp']));
		}
	}

	/**
	 * 输出urlencode的返回结果, 主要用于与PC客户端通信
	 *
	 * @access public
	 * @param string $msg 提示信息
	 * @param integer $state 状态
	 * @param array $data 数据数组
	 */
	public function outputHttpQuery($msg = '', $state = 51, $data = array()) {
		$arr = array_merge(array('state' => $state, 'msg' => $msg), $data);
		$this->str(http_build_query($arr));
	}

	/**
	 * 输出HTML信息
	 *
	 * @access public
	 * @param string $msg 提示信息
	 * @param integer $state 状态
	 * @param string $view 视图文件
	 * @param array $data 附加至模板的变量信息数组
	 */
	public function outputHTML($msg = '', $state = 621, $view = null, $data = array()) {
		if (null == $view)
			$view = $this->html_view;
		$this->render($view, array_merge(array(
			'msg' => $msg,
			'state' => $state
		), $data));
		exit();
	}

	/**
	 * 输出成功信息
	 *
	 * @access public
	 * @param array $data 数据数组
	 * @param string $msg 提示信息
	 */
	public function outputDone($data = array(), $msg = 'done') {
		$this->output($msg, 0, $data);
	}

	/**
	 * 输出失败信息
	 *
	 * @access public
	 * @param array $data 数据数组
	 * @param string $msg 提示信息
	 */
	public function outputFailed($data = array(), $msg = 'failed') {
		$this->output($msg, 611, $data);
	}

	/**
	 * 输出错误信息
	 *
	 * @access public
	 * @param string $msg 错误信息
	 * @param array $data 数据数组
	 */
	public function outputError($msg = 'error', $data = array()) {
		$this->output($msg, 621, $data);
	}

	/**
	 * 参数错误
	 *
	 * @access public
	 * @param string $param 参数名
	 */
	public function paramErr($param) {
		$this->output(
			($this->is_i18n ? Dh::t('app', 'parameter error') : 'parameter error') . ' : ' . $param,
			501,
			array('name' => $param)
		);
	}

	/**
	 * 缺少参数
	 *
	 * @access public
	 * @param string $param 参数名
	 */
	public function paramMiss($param) {
		$this->output(
			($this->is_i18n ? Dh::t('app', 'missing parameter') : 'missing parameter') . ' : ' . $param,
			502,
			array('name' => $param)
		);
	}

	/**
	 * 返回当前域名的绝对网址
	 *
	 * @access public
	 * @return string
	 */
	public function getCurrentDomainUrl() {
		static $url = null;
		if (is_null($url))
			$url = Dh::app()->getRequester()->getHostInfo();
		return $url;
	}

	/**
	 * 返回资源文件存放王志
	 *
	 * @access public
	 * @param string $path 路径别名
	 * @return string
	 */
	public function getAssetsDomainUrl($path = '') {
		static $arr = array();
		if (!isset($arr['_' . $path]))
			$arr['_' . $path] = $this->getDomainUrl('assets') . '/' . str_replace('.', '/', ltrim($path, '.'));
		return $arr['_' . $path];
	}

	/**
	 * 返回静态文件存放网址
	 *
	 * @access public
	 * @return string
	 */
	public function getStaticDomainUrl() {
		static $url = null;
		if (is_null($url))
			$url = $this->getCurrentDomainUrl() . '/static';
		return $url;
	}

	/**
	 * 返回域名
	 *
	 * @access public
	 * @param string $domain 域名标识
	 * @return string
	 */
	public function getDomain($domain = 'main') {
		static $arr = array();
		$d = 'domain_' . $domain;
		if (isset($arr[$d]))
			return $arr[$d];
		$arr[$d] = CConfig::get($d);
		return $arr[$d];
	}

	/**
	 * 返回域名绝对网址
	 *
	 * @access public
	 * @param string $domain 域名标识
	 * @param bool $enable_ssl 是否启用HTTPS
	 * @return string
	 */
	public function getDomainUrl($domain = 'main', $enable_ssl = false) {
		static $arr = array();
		$d = 'domain_' . $domain;
		if (isset($arr[$d]))
			return $arr[$d];
		$arr[$d] = (Dh::app()->getRequester()->getIsSecureConnection() && (in_array($domain, ['assets', 'mobile', 'shop', 'img2', 'file']) || $enable_ssl) ? 'https://' : 'http://' ) . $this->getDomain($domain);
		return $arr[$d];
	}

	/**
	 * HTTP转HTTPS
	 */
	public function httpToHttps($url, $force = false) {
		if ($force) {
			$url = str_replace('https:', '', $url);
			$url = str_replace('http:', '', $url);
			return $url;
		}
		return Dh::app()->getRequester()->getIsSecureConnection() ? str_replace('http:', 'https:', $url) : $url;
	}

	/**
	 * 获取应用版本号
	 *
	 * @access public
	 */
	public function getAppVersion() {
		$version = null;
		if (null === $version) {
			if (preg_match('/WeileVersion[\s]*:[\s]*([\d\.]+)/', Dh::app()->getRequester()->getUserAgent(), $arr))
				$version = trim($arr[1]);
			else
				$version = '';
		}
		return $version;
	}

	/**
	 * 获取当前应用渠道号
	 *
	 * @access public
	 */
	public function getAppChannelByUA() {
		$channel = null;
		if (null === $channel) {
			if (preg_match('/WeileChannel[\s]*:[\s]*([\d]+)/', Dh::app()->getRequester()->getUserAgent(), $arr))
				$channel = (int) trim($arr[1]);
			else
				$channel = 0;
		}
		return $channel;
	}

	/**
	 * 检查目标字符串中是否含有敏感词
	 *
	 * @access public
	 * @param string $s
	 * @return boolean
	 */
	public function hasBadWords($s) {
		/*static $trie = null;
		if (null === $trie) {
			Dh::import('root.app.util.Trie');
			$trie = new Trie(Dh::app()->getCache()->get('global_badwords_dict'));
		}
		return $trie->match($s);*/

		static $badwords = null;
		if (null === $badwords) {
			$badwords = explode("\n", Dh::app()->getCache()->get('global_badwords_dict_string'));
			$badwords = array_combine($badwords, array_fill(0, count($badwords), '[*bad*]'));
		}
		$_s = strtr($s, $badwords);

		return $_s !== $s;
	}

	/**
	 * 判断应用的指定版本是否为在审状态
	 *
	 * @access public
	 * @param array $app
	 * @param string $version
	 * @return boolean
	 */
	public function appIsReview($app, $version) {
		//如果设置了测试版本号
		/*if (!empty($app['test_version']) && $app['test_version'] == $version) {
			if ('1' == $app['test_is_review'])
				return true;
		}*/

		//处理开关中的审核状态
		if (!empty($app['switch_version'])) {
			$ip = Dh::app()->getRequester()->getIp();
			foreach ($app['switch_version'] as $row) {
				if ('is_review' == $row['variable']) {
					if ('' != $row['version']) {
						$symbol = '=';
						if (preg_match('/^(>=|<=|>|<|=)(\d+(?:\.\d){0,2})/', $row['version'], $arr)) {
							$symbol = $arr[1];
							$row['version'] = $arr[2];
						}
						if (!version_compare($version, $row['version'], $symbol))
							continue;
					}
					if ('string' != $row['type'])
						$row['value'] = (int) $row['value'];
					if (('' == $row['ip'] || $ip == $row['ip']) && 1 == $row['value'])
						return true;
				}
			}
		}
		//正式版本
		if (isset($app['is_review']) && $app['is_review'] && (empty($app['review_version']) || $app['review_version'] == $version))
			return true;

		return false;
	}

	/**
	 * 通过ID或标识符获取游戏分类信息
	 *
	 * @param integer|string $param
	 * @param string $field 不为空时表示直接返回某字段的值
	 * @param boolean $all 是否获取全部数据
	 * @return array|string
	 */
	public function getGameType($param, $field = '', $all = false) {
		static $data = null;
		if (null === $data) {
			$data = Dh::app()->getCache()->get('game_type');
			if (null === $data)
				$data = $this->model('root.app.model.GameType')->flushCache();
		}

		if ($all)
			return $data;
		if (is_integer($param))
			$result = isset($data['id']) && isset($data['id'][$param]) ? $data['id'][$param] : array();
		else
			$result = isset($data['tag']) && isset($data['tag'][$param]) ? $data['tag'][$param] : array();
		if ('' != $field)
			return isset($result[$field]) ? $result[$field] : '';
		return $result;
	}

	/**
	 * 通过ID或标识符获取文章分类信息
	 *
	 * @param integer|string $param
	 * @param string $field 不为空时表示直接返回某字段的值
	 * @param boolean $all 是否获取全部数据
	 * @return array|string
	 */
	public function getArticleCategory($param, $field = '', $all = false) {
		static $data = null;
		if (null === $data) {
			$data = Dh::app()->getCache()->get('article_category');
			if (null === $data)
				$data = $this->model('Category')->flushCache();
		}

		if ($all)
			return $data;
		if (is_integer($param))
			$result = isset($data['id']) && isset($data['id'][$param]) ? $data['id'][$param] : array();
		else
			$result = isset($data['tag']) && isset($data['tag'][$param]) ? $data['tag'][$param] : array();
		if ('' != $field)
			return isset($result[$field]) ? $result[$field] : '';
		return $result;
	}

	/**
	 * 根据游戏id返回app信息
	 *
	 * @access public
	 * @param integer $game_id
	 * @return array
	 */
	public function getAppByGameId($game_id) {
		static $app_config = null;
		if (null === $app_config) {
			$app_config = $this->loadAppConfig();
			if (empty($app_config['app'])) {
				$app_config = array();
			} else {
				$_tmp = $app_config['app'];
				$app_config = array();
				foreach ($_tmp as $app_info) {
					if ($app_info['game_id'] > 0) {
						if (!isset($app_config[$app_info['game_id']]))
							$app_config[$app_info['game_id']] = array();
						$app_config[$app_info['game_id']][] = $app_info;
					}
				}
			}
		}
		return isset($app_config[$game_id]) ? $app_config[$game_id]['app_id'] : array();
	}

	/**
	 * 获取当前应用所用的模板路径
	 *
	 * @access protected
	 * @param integer $app_id 应用ID
	 * @return string
	 */
	protected function getAppView($app_id = 0) {
		if (!$app_id) {
			if (!isset($this->app_id))
				return false;
			$app_id = $this->app_id;
		}

		if ($app_id > 1)
			return 'mobile.v4.';
		return '';
	}

	/**
	 * 根据APP_ID载入其配置信息
	 *
	 * @access public
	 * @param integer|string $app_id
	 * @param integer $channel_id 渠道ID
	 * @param boolean $parent 当指定渠道的配置不存在时是否向上查找非渠道配置
	 * @return boolean|array
	 */
	public function getAppConfigById($app_id, $channel_id = 0, $parent = true) {
		$data = Dh::app()->getCache()->get('app.' . $app_id . ($channel_id ? '_' . $channel_id : '')); //CHCACHE CConfig::loadByFile($app_id . ($channel_id ? '_' . $channel_id : ''), 'root.app.data.app_config');
		if (empty($data) || !is_array($data)) {
			if ($channel_id) {
				$data = Dh::app()->getCache()->get('app.' . $app_id); //CHANGE CConfig::loadByFile($app_id, 'root.app.data.app_config');
				if (empty($data) || !is_array($data))
					return false;
			} else {
				return false;
			}
		}
		return $data;
	}

	/**
	 * 载入所有APP数据
	 *
	 * @access protected
	 * @param string $brand 品牌 jixiang|weile
	 * @return array
	 */
	public function loadAppConfig($brand = '') {
		static $data = array();
		static $platform = null;
		$brand = strtolower($brand);

		if (null === $platform)
			$platform = CConfig::get('define_platform');

		if (!isset($data['_' . $brand])) {
			$data['_' . $brand] = array();
			$_data = $this->model('root.app._admin.model.App')->brand($brand)->select();
			foreach ($_data as $_row) {
				$_row['platform_name'] = isset($platform[$_row['platform_id']]) ? strtolower($platform[$_row['platform_id']]) : '';
				if (!empty($_row['data'])) {
					$_other = unserialize($_row['data']);
					unset($_row['data']);
					$_row = array_merge($_other, $_row);
				}
				$data['_' . $brand][$_row['app_id'] . ($_row['channel_id'] ? '_' . $_row['channel_id'] : '')] = $_row;
			}
		}
		return $data['_' . $brand];
	}

	/**
	 * 检查访问者使用的是否为吉祥/微乐移动客户端webview
	 *
	 * @access public
	 * @return boolean
	 */
	public function isMobileClient() {
		$ua = strtolower(Dh::app()->getRequester()->getUserAgent());
		if (preg_match('/weileapp[\s]*:[\s]*yes/', $ua))
			return true;
		return false;
	}

	/**
	 * 获取channel_id对应的下载验证key
	 *
	 * @access public
	 */
	public function getChannelKey($channel_id) {
		$key = md5('channed_id_' . $channel_id . '_weile_download');
		return substr($key, 12, 5) . substr($key, 21, 5);
	}

	/**
	 * 获取短信对象
	 * @return SMS
	 */
	public function sms() {
		static $sms = null;
		if (null === $sms) {
			Dh::import('root.app.util.SMS');
			$sms = new SMS();
		}
		return $sms;
	}

	/**
	 * 解密加密请求
	 */
	public function decryption($crypt_str, $app_info) {
		return CTool::cryptStrUrl($crypt_str, 'DECODE', $app_info['app_id'] . $app_info['app_key'] . $app_info['app_id']);
	}
}

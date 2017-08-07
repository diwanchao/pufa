<?php
/**
 * 手机基类控制器
 *
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.xingdonghai.cn/
 * @copyright Copyright &copy; 2014 DongHai Hsing
 * @package 20_com.weile
 * @since 0.1
 * @version 0.1
 */
abstract class MobileBaseController extends GlobalController {
	/**
	 * 不适用GlobalController检查UA
	 */
	public $check_ua = false;

	/**
	 * 应用配置信息
	 *
	 * @access protected
	 * @var array
	 */
	protected $app_config = array();

	/**
	 * 渠道ID
	 *
	 * @access public
	 * @var int
	 */
	public $channel_id = 0;

	/**
	 * 来自哪个应用ID
	 *
	 * @access public
	 * @var int
	 */
	public $from_app_id = 0;

	/**
	 * UATool 实例
	 *
	 * @access public
	 * @var UATool
	 */
	public $uatool = null;

	public $brand = null;

	/**
	 * 初始化
	 *
	 * @access public
	 */
	public function init() {
		$this->uatool = new UATool();
		//检测GET方式传入的渠道ID
		if (CFormat::isPint([$_GET, 'channel_id'], 1))
			$channel_id = (int) $_GET['channel_id'];
		elseif (CFormat::isPint([$_GET, 'c'], 1))
			$channel_id = (int) $_GET['c'];

		if (isset($channel_id)) { //如果传入了渠道ID则刷新cookie
			CTool::cookie('channel_id', $channel_id, 600);
			$this->channel_id = $channel_id;
		} elseif (CTool::cookie('channel_id') && CFormat::isInt(CTool::cookie('channel_id')) && CTool::cookie('channel_id') > 0) { //否则, 检查cookie中是否有写入渠道ID
			$this->channel_id = (int) CTool::cookie('channel_id');
		}

		//检查来源应用, 一般会在点击分享分享后带入
		if (CFormat::isPint([$_GET, 'from_app'], 1)) {
			$this->from_app_id = (int) $_GET['from_app'];
			CTool::cookie('from_app_id', $this->from_app_id, 600); //如果有明确传入来源应用则将其保存至cookie
		} else {
			$this->from_app_id = (int) CTool::cookie('from_app_id');
		}

		$this->brand = (object) CConfig::get('brand');
	}

	/**
	 * 获取下载参数
	 *
	 * @access private
	 */
	public function getDownloadUrl($app_id) {
		static $stats_uri = '';
		static $dl_doamin = '';

		if (!isset($this->app_config[$app_id]) || empty($this->app_config[$app_id]['download_url']))
			return array('msg' => '尽请期待', 'url' => '');

		$app = $this->app_config[$app_id];

		if ($app['is_review'] && 'ios' == $app['platform_name'] && !version_compare($app['version'], '1.0.0', '>'))
			return array('msg' => '苹果版即将开放', 'url' => 'review');

		if ('' == $dl_doamin)
			$dl_doamin = $this->getDomainUrl('dl');

		if ('' == $stats_uri) {
			if ($this->from_app_id)
				$stats_uri .= '/from_app/' . $this->from_app_id;

			if ($this->channel_id) {
				$key = md5('channed_id_' . $this->channel_id . '_weile_download');
				$key = substr($key, 12, 5) . substr($key, 21, 5);
				$stats_uri .= '/channel_id/' . $this->channel_id . '/key/' . $key;
			}

			if ('' != $this->download_mode) {
				$stats_uri .= '/mode/' . $this->download_mode;
			}
		}

		return array('msg' => '立即下载', 'url' => $dl_doamin . '/game/' . $app_id . $stats_uri, 'has_wechar_download' => strpos($this->app_config[$app_id]['download_url'], '|') ? 1 : 0);
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
	 * 输出错误信息
	 *
	 * @param $str
	 * @param string $url
	 * @param int $seconds
	 */
	public function msgError($str, $url = '-1', $seconds = 5) {
		$this->render('msgError', array(
			'msg' => $str,
			'seconds' => $seconds,
			'url' => $url
		));
		exit();
	}

	/**
	 * 获取全局幻灯
	 */
	public function getGlobalSlide() {
		return $this->model(101)->setTable('#@__diy_slide')->setPk('id')->select(array(
			'where' => 'channel_id=0 AND area_code=0',
			'order' => 'id DESC'
		));
	}

	/**
	 * 获取最终处理过的幻灯数据
	 *
	 * @param $region
	 *
	 * @return array
	 */
	public function getSlide($region) {
		$data = $this->getSlideData($region);   //获取格式化后的幻灯数据
		$type = $this->getRegionType($region);

		$is_empty_channel = empty($data['channel']['global']) && empty($data['channel']['province']) && empty($data['channel']['city']) && empty($data['channel']['area']);

		$sdata = $config = array();   //用于存储幻灯数据的变量

		//有渠道ID并且渠道数据不为空
		if (0 != $this->channel_id && !$is_empty_channel) {
			$data_key = 'channel';
			if (empty($data[$data_key]['global'])) {    //如果渠道全国为空则进行相应改变  如不为空则不需要进行设置
				if (empty($data[$data_key]['province'])) {//渠道全局数据与省份数据为空 使用自有配置覆盖
					$data[$data_key]['global'] = $data['self']['global'];
					$data[$data_key]['province'] = $data['self']['province'];
					if (empty($data[$data_key]['city'])) {//渠道全局、省份、城市为空  使用自有配置覆盖
						$data[$data_key]['city'] = $data['self']['city'];
						if (empty($data[$data_key]['area']))//渠道全局、省份、城市、地区都为空  使用自有配置覆盖
							$data[$data_key]['area'] = $data['self']['area'];
					}

				}
			}

		} else {
			$data_key = 'self';
		}

		$tmp_data = $data[$data_key];

		//根据区域类型 删除多余数据
		switch ($type) {
			case 'a' :
				break;
			case 'c' :
				unset($tmp_data['area']);
				break ;
			case 'p' :
				unset($tmp_data['area'], $tmp_data['city']);
				break ;
		}

		//倒序遍历配置数据
		if (!empty($tmp_data)) {
			foreach (array_reverse($tmp_data) as $item_key => $item) {
				if ('global' == $item_key) {    //如果遍历至global选项即为最后一条数据
					if (empty($sdata)) { //遍历至global时  如果sdata为空 则使用global数据
//						if (0 != $this->channel_id && !$is_empty_channel)
						$sdata[] = $config = $item;
					} else {    //当遍历至global时  如果sdata不为空时 则存在$config 根据$config设置进行操作

						if (0 != $this->channel_id && !$is_empty_channel) {
							if ($config['is_merge_channel_global'])
								$sdata[] = $item;
						}
					}
				} else {
					if (!empty($item)) {
						$sdata[] = $item;
						$config = empty($config) ? $item : $config; //第一个命中的目标数据  即为配置项
						if (0 == $config['is_merge_parent'])
							break;
					}
				}
			}
		}


		//根据配置项判断是否需要合并全局幻灯
		if (!empty($config)  && 1 == $config['is_merge_global'])
			$sdata = array_merge($sdata, array($this->slide_global));

		return $sdata;
	}


	/**
	 * 获取幻灯数据
	 *
	 * @param $region
	 *
	 * @return array
	 */
	private function getSlideData($region) {
		$type = $this->getRegionType($region);
		$where = '';

		//根据区域编码类型组织需要匹配的查询的area_code
		if ('a' == $type) { //地区
			$tmp = array(0, $region, substr($region, 0, 4), substr($region, 0, 2));
		} else if ('c' == $type) { //城市
			$tmp = array(0, substr($region, 0, 4), substr($region, 0, 2));
		} else if ('p' == $type){    //省份
			$tmp = array(0, substr($region, 0, 2));
		}

		//组成area_code的where子句
		if (!empty($tmp)) {
			foreach ($tmp as $item) {
				$where .= ' OR area_code=' . Dh::app()->getDb()->quote((0 == $item ? '0' : str_pad($item, 6, 0)));
			}
		}

		//组成完整的where子句  包括channel_id
		$where = '(channel_id=0 OR channel_id=' . Dh::app()->getDb()->quote($this->channel_id) . ') AND (' . substr($where, 4) .')';
		$data = $this->model(111)->setTable('#@__diy_slide')->setPk('id')->select(array(
			'where' => $where
		));

		return $this->formatSlideData($data);
	}

	/**
	 * 格式化幻灯数据
	 *
	 * @param $data
	 *
	 * @return array
	 */
	private function formatSlideData($data) {
		//默认数据
		$tmp = array(
			'self' => array(
				'global' => array(),
				'province' => array(),
				'city' => array(),
				'area' => array(),
			),
			'channel' => array(
				'global' => array(),
				'province' => array(),
				'city' => array(),
				'area' => array(),
			),
		);

		//格式化数据
		if (!empty($data)) {
			foreach ($data as $item) {
				if (0 == $item['channel_id'] && 0 == $item['area_code'])
					$this->slide_global = $item;

				$tmp_key = 0 == $item['channel_id'] ? 'self' : 'channel';
				if (0 == $item['area_code']) {
					$tmp[$tmp_key]['global'] = $item;
					continue;
				}

				$type = $this->getRegionType($item['area_code']);
				switch ($type) {
					case 'p' :
						$tmp[$tmp_key]['province'] = $item;
						break;
					case 'c' :
						$tmp[$tmp_key]['city'] = $item;
						break;
					case 'a' :
						$tmp[$tmp_key]['area'] = $item;
						break;
				}
			}
		}

		return $tmp;

	}

	/**
	 * 获取地区类型
	 *
	 * @param $region
	 *
	 * @return string a:地区 c:城市 p:省份
	 */
	private function getRegionType($region) {
		if (0 != strncasecmp('00', substr($region, 4, 2), 2)) { //地区
			$type = 'a';
		} else if (0 != strncasecmp('00', substr($region, 2, 2), 2)) { //城市
			$type = 'c';
		} else {    //省份
			$type = 'p';
		}

		return $type;
	}

	/**
	 * 根据省份获取product的ID
	 *
	 * @param $province_code
	 *
	 * @return int
	 */
	public function getProvinceProduct($province_code) {
		$config = Dh::app()->getCache()->get('region_config'); //CHCACHE CConfig::loadByFile('region_config');
		if (isset($config[$province_code]))
			return $config[$province_code];

		return 0;
	}

	/**
	 * 生成URL
	 *
	 * @access public
	 * @param string $route 为空时自动使用当前路由
	 * @param array $params
	 * @param string $ampersand
	 * @return string
	 */
	/*public function createUrl($route = '', $params = array(), $ampersand = '&') {
		if (!empty($_GET)) {
			unset($_GET['platform_id'], $_GET['from'], $_GET['isappinstalled'], $_GET['time']);
			$params = array_merge($_GET, $params);
		}
		return Dh::app()->createUrl($route, $params, $ampersand);
	}*/

	/**
	 * 获取指定平台的下载地址, 异步统计方式
	 * @param array $data
	 * @param bool $is_tips_wechat 如果是微信中打开且没有微下载地址时是否提示使用浏览器打开
	 * @return array 索引0为下载地址, 索引1为统计地址
	 */
	public function getDownloadUrlByConfigSyncStats($data, $is_tips_wechat = false, $wechat_tips_url = 'javascript:noWechatUrl(\'#\');') {
		if ($this->uatool->is_wechar) {
			$download = $this->uatool->is_ios ? $data['wechat_ios'] : $data['wechat_android'];
			if (empty($download)) {
				if ($is_tips_wechat) {
					$download = $this->uatool->is_ios ? $data['url_ios'] : $data['url_android'];
					if (!empty($download))
						return [$wechat_tips_url, ''];
				}
				return ['', ''];
			}
			return [$download, $this->getDomainUrl('stats') . '/download/click/' . (isset($data['config_id']) ? $data['config_id'] . '-' . $data['game_id'] : $data['id']) . $this->getOtherDownloadParams()];
		} else {
			if ($this->uatool->is_ios) {
				if (empty($data['url_ios']))
					return ['', ''];
				return [preg_match('/^http[s]?:\/\//', $data['url_ios']) ? $data['url_ios'] : 'https://itunes.apple.com/cn/app/id' . $data['url_ios'] . '?mt=8', $this->getDomainUrl('stats') . '/download/click/' . (isset($data['config_id']) ? $data['config_id'] . '-' . $data['game_id'] : $data['id']) . $this->getOtherDownloadParams()];
			} else {
				if (empty($data['url_android']))
					return ['', ''];
				return [preg_match('/^http[s]?:\/\//', $data['url_android']) ? $data['url_android'] : $this->getDomainUrl('file') . '/game/android/' . preg_replace('/\.apk/', '', $data['url_android']), $this->getDomainUrl('stats') . '/download/click/' . (isset($data['config_id']) ? $data['config_id'] . '-' . $data['game_id'] : $data['id']) . $this->getOtherDownloadParams()];
			}
		}
	}

	/**
	 * 获取指定平台的下载地址
	 * @param array $data
	 * @param bool $is_tips_wechat 如果是微信中打开且没有微下载地址时是否提示使用浏览器打开
	 * @return string
	 */
	public function getDownloadUrlByConfig($data, $is_tips_wechat = false, $wechat_tips_url = 'javascript:noWechatUrl(\'#ios#\', \'#android#\');') {
		if (empty($data))
			return '';
	
		if ($this->uatool->is_wechar) {
			$download = $this->uatool->is_ios ? $data['wechat_ios'] : $data['wechat_android'];
			if (empty($download)) {
				if ($is_tips_wechat) {
					$download = $this->uatool->is_ios ? $data['url_ios'] : $data['url_android'];
					if (!empty($download))
						return $wechat_tips_url;
				}
				return '';
			} elseif ('#' == $download) {
				$url = 'https://download' . $this->getDomain('root') . '/config/' . (isset($data['config_id']) ? $data['config_id'] . '-' . $data['game_id'] : $data['id']) . $this->getOtherDownloadParams();
				return str_replace('#ios#', empty($data['url_ios']) ? '' : $url, str_replace('#android#', empty($data['url_android']) ? '' : $url, $wechat_tips_url));
			}
		} else {
			$download = $this->uatool->is_ios ? $data['url_ios'] : $data['url_android'];
			if (empty($download))
				return '';
		}
		return 'https://download' . $this->getDomain('root') . '/config/' . (isset($data['config_id']) ? $data['config_id'] . '-' . $data['game_id'] : $data['id']) . $this->getOtherDownloadParams();
	}

	/**
	 * 忽略微信 获取下载地址
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public function getDownloadUrlByConfigIgnoreWechat($data) {
		$download = $this->uatool->is_ios ? (isset($data['url_ios']) ? $data['url_ios'] : '') : (isset($data['url_android']) ? $data['url_android'] : '');
		if (empty($download))
			return '';
		return 'https://dl' . $this->getDomain('root') . '/config/' . (isset($data['config_id']) ? $data['config_id'] . '-' . $data['game_id'] : $data['id']) . $this->getOtherDownloadParams();
	}

	/**
	 * 生成下载地址中其他GET参数
	 */
	public function getOtherDownloadParams() {
		$str = '';
		if (CFormat::isPint([$_GET, 'channel_id'], 1))
			$str .= '/channel_id/' . $_GET['channel_id'];
		if (CFormat::isPint([$_GET, 'region'], 1))
			$str .= '/from_region/' . $_GET['region'];
		if (CFormat::isPint([$_GET, 'from_app'], 1))
			$str .= '/from_app/' . $_GET['from_app'];
		if (CFormat::isPint([$_GET, 'platform_id'], 1))
			$str .= '/platform_id/' . $_GET['platform_id'];
		return $str;
	}

	/**
	 * 获取地区
	 * @return string
	 */
	public function getQueryRegion() {
		//获取地区
		$region = CFormat::isPint([$_GET, 'region'], 1) && 6 == strlen($_GET['region']) ? trim($_GET['region']) : '';
		if ('' == $region) {
			$region = CTool::cookie('region');
			if (!preg_match('/^[\d]{6}$/', $region))
				$region = $this->uatool->getRegionByIp();
			if (!$region)
				$region = '';
			$_GET['region'] = $region;
		} else {
			CTool::cookie('region', $region, 600);
		}
		return $region;
	}

	/**
	 * 生成URL
	 *
	 * @access public
	 * @param string $route 为空时自动使用当前路由
	 * @param array $params
	 * @param string $ampersand
	 * @return string
	 */
	public function createUrl($route = '', $params = array(), $ampersand = '&') {
		if (!empty($_GET)) {
			foreach ($_GET as $key => $value) {
				if (strpos('/', $key)) {
					unset($_GET);
				}
			}
			//unset($_GET['platform_id'], $_GET['from'], $_GET['isappinstalled'], $_GET['time']);
			$params = array_merge($_GET, $params);
		}
		return Dh::app()->createUrl($route, $params, $ampersand);
	}

	/**
	 * 获取config_id
	 *
	 * @param $channel_id
	 * @param $code
	 *
	 * @return int
	 */
	public function getConfig($channel_id = null, $code = null) {
		if (is_null($channel_id) && is_null($code)) {
			$channel_id = isset($_GET['channel_id']) ? (int) $_GET['channel_id'] : 0;
			$code = isset($_GET['region']) ? (int) $_GET['region'] : 0;
		}
		$code = str_pad(substr($code, 0, 2), 6, 0);
		$data = Dh::app()->getCache()->get('channel-region-downloadurl');
		if (empty($data))
			$data = $this->cacheDownloadUrl();

		$name = $channel_id . '-' . $code;
		$result = isset($data[$name]) ? $data[$name] : array();
		//如果数据为空则检查渠道为0 是否为空
		if (empty($result))
			$result = isset($data['0-' . $code]) ? $data['0-' . $code] : false;

		//如果数据为空  则使用默认数据
		if (empty($result))
			$result = !empty($data['default']) ? $data['default'] : array();

		return $result;
	}

	/**
	 * 获取ConfigId
	 *
	 * @param null $channel_id
	 * @param null $code
	 *
	 * @return bool
	 */
	public function getConfigId($channel_id = null, $code = null) {
		if (is_null($channel_id) && is_null($code)) {
			$channel_id = isset($_GET['channel_id']) ? (int) $_GET['channel_id'] : 0;
			$code = isset($_GET['region']) ? (int) $_GET['region'] : 0;
		}

		$code = str_pad(substr($code, 0, 2), 6, 0);
		$data = $this->getConfig($channel_id, $code);
		return false == $data ? array() : $data['config_id'];
	}

	/**
	 * 缓存配置地区的下载地址
	 */
	public function cacheDownloadUrl() {
		$data = $this->model('MobilePage')->select(array(
			'select' => 'channel_id, region, url_android, url_ios, wechat_android, wechat_ios, id, type'
		));
		$tmp = array();
		if (!empty($data)) {
			foreach ($data as $item) {
				$channels = explode(',', $item['channel_id']);
				foreach ($channels as $channel) {
					$name = $channel . '-' . $item['region'];
					$tmp[$name] = array(
						'config_id' => $item['id'],
						'url_android' => $item['url_android'],
						'url_ios' => $item['url_ios'],
						'wechat_android' => $item['wechat_android'],
						'wechat_ios' => $item['wechat_ios'],
					);

					if (1 == $item['type'])
						$tmp['default'] = array(
							'config_id' => $item['id'],
							'url_android' => $item['url_android'],
							'url_ios' => $item['url_ios'],
							'wechat_android' => $item['wechat_android'],
							'wechat_ios' => $item['wechat_ios'],
						);
				}
			}
		}

		Dh::app()->getCache()->set('channel-region-downloadurl', $tmp);
		return $tmp;
	}
}
<?php
/**
 * UA工具类, 用来分享用户客户端信息等数据
 * 
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.xingdonghai.cn/
 * @copyright Copyright &copy; 2015 Weile Inc
 * @package 20_com.weile
 * @since 0.1
 * @version 0.1 (Create: 2015-03-25, Update: 2015-03-25)
 */
class UATool extends CBase {
	/**
	 * UA字符串
	 *
	 * @var string
	 */
	public $ua = '';

	/**
	 * IP地址
	 *
	 * @var int
	 */
	public $ip = 0;

	/**
	 * 字符串类型IP地址
	 *
	 * @var string
	 */
	public $ip_str = '';

	/**
	 * 省份名称
	 *
	 * @var string
	 */
	public $province = '';

	/**
	 * 城市名称
	 *
	 * @var string
	 */
	public $city = '';

	/**
	 * 省份ID
	 *
	 * @var int
	 */
	public $province_id = 0;

	/**
	 * 城市ID
	 *
	 * @var int
	 */
	public $city_id = 0;

	/**
	 * 系统名称
	 *
	 * @var string
	 */
	public $os = '';

	/**
	 * 系统版本
	 *
	 * @var string
	 */
	public $os_version = '';

	/**
	 * 系统ID
	 *
	 * @var int
	 */
	public $os_id = 0;

	/**
	 * 系统版本ID
	 *
	 * @var int
	 */
	public $os_version_id = 0;

	/**
	 * 平台ID
	 *
	 * @var int
	 */
	public $platform_id = 0;

	/**
	 * 设备名称
	 *
	 * @var int
	 */
	public $device = '';

	/**
	 * 设备ID
	 *
	 * @var int
	 */
	public $device_id = 0;

	/**
	 * 是否为微信浏览
	 *
	 * @var int
	 */
	public $is_wechar = 0;

	/**
	 * 是否为IOS设备
	 *
	 * @var int
	 */
	public $is_ios = 0;

	/**
	 * 是否为Android设备
	 *
	 * @var int
	 */
	public $is_android = 0;

	/**
	 * UA中是否含有微乐应用标识
	 *
	 * @var int
	 */
	public $ua_is_client_app = 0; //UA中是否含有微乐应用标识

	/**
	 * UA中是否含有IOS企业签名标识
	 *
	 * @var int
	 */
	public $ua_is_sign_enterprize = 0;


	/**
	 * 构造函数
	 *
	 * @access public
	 * @param bool $connect_db 是否连接数据库获取某些ID信息
	 * @param bool $get_city_info 是否获取城市省份信息
	 */
	public function __construct($connect_db = false, $get_city_info = false) {
		$this->ua = Dh::app()->getRequester()->getUserAgent();
		$ua = strtolower($this->ua);

		//获取设备及系统名称
		if (strpos($this->ua, 'iPhone')) {
			$this->os = 'iOS';
			$this->device = 'iPhone';
		} elseif (strpos($this->ua, 'iPad')) {
			$this->os = 'iOS';
			$this->device = 'iPad';
		} elseif (strpos($this->ua, 'iPod')) {
			$this->os = 'iOS';
			$this->device = 'iPod';
		} elseif (strpos($this->ua, 'iOS')) {
			$this->os = 'iOS';
			$this->device = 'iPhone';
		} elseif (strpos($this->ua, 'Android')) {
			$this->os = 'Android';
			$this->device = 'AndroidPhone';
		} elseif (strpos($this->ua, 'Linux')) {
			$this->os = 'Linux';
			$this->device = 'PC';
		} elseif (strpos($this->ua, 'Windows NT')) {
			$this->os = 'Windows';
			$this->device = 'PC';
		} elseif (strpos($this->ua, 'Windows 98')) {
			$this->os = 'Windows 98';
			$this->device = 'PC';
		} elseif (strpos($this->ua, 'ZTE')) {
			$this->os = 'ZTE';
		} elseif (strpos($this->ua, 'Macintosh')) {
			$this->os = 'Mac';
			$this->device = 'Mac';
		} else {
			$this->os = '';
			$this->device = '';
		}

		//处理系统版本
		switch ($this->os) {
			case 'Windows':
				$this->os_version = substr($this->ua, stripos($this->ua, 'Windows NT') + 11, 3);
				//if (' Di' == $this->os_version) //记录未知UA
				//	Dh::log($this->ua, 'unknown_ua');
				break;
			case 'Windows 98':
				$this->os = 'Windows';
				$this->os_version = '98';
				break;
			case 'Mac':
				if (preg_match('/Mac OS X ([\w\.]+);/', $this->ua, $arr))
					$this->os_version = str_replace('_', '.', $arr[1]);
				break;
			case 'iOS':
				if (preg_match('/OS ([\w\.]+) like/i', $this->ua, $arr))
					$this->os_version = str_replace('_', '.', $arr[1]);
				$this->is_ios = 1;
				break;
			case 'Android':
				$pos = stripos($this->ua, 'Android') + 8;
				$this->os_version = substr($this->ua, $pos, strpos($this->ua, ';', $pos) - $pos);
				$this->is_android = 1;
				break;
			default:
				$this->os_version = '';
		}

		//目前平台标识仅通过系统来确定, 当出现web应用时需要改动
		$this->platform_id = $this->getPlatformId($this->os);
		$this->device_id = $this->getDeviceId($this->device);
		$this->os_id = $this->getOsId($this->os);

		//判断是否微信浏览
		$this->is_wechar = false !== strpos($ua, 'micromessenger') ? 1 : 0;

		if (preg_match('/weileapp[\s]*:[\s]*yes/', $ua))
			$this->ua_is_client_app = 1;
		if (preg_match('/weilesign[\s]*:[\s]*enterprise/', $ua))
			$this->ua_is_sign_enterprize = 1;

		$this->ip_str = Dh::app()->getRequester()->getUserHostAddress();
		$this->ip = CFormat::ip2long($this->ip_str);

		if ($get_city_info) { //获取城市省份信息
			//$this->ip_str = '222.161.243.74';
			$region = ltrim(CTool::convertIp($this->ip_str, true), '-');

			if (' LAN' !=  $region) {
				if (preg_match('/(内蒙古|西藏|新疆|广西|宁夏)/', $region, $province)) {
					$province = $province[1];
					$_region = explode($province, $region);
					$city = isset($_region[1]) ? trim(substr($_region[1], 0, strrpos($_region[1], ' '))) : '';
				} else {
					$_region = explode('省', $region);
					$province = isset($_region[1]) ? substr($_region[0], strrpos($_region[0], ' ')) . '省' : '';
					$_region = explode('市', $region);
					$city = isset($_region[1]) ? substr($_region[0], ('' == $province ? strrpos($_region[0], ' ') : strrpos($_region[0], '省') + 3)) . '市' : '';
				}

				//初始化常规参数
				$this->province = trim($province);
				$this->city = trim($city);
				$this->province_id = $this->getProvinceId($this->province);
				$this->city_id = $this->model('root.app.model.City')->getIdByName($this->city);;
			}
		}
		$this->_models = array();
	}

	/**
	 * 通过省份名称获取省份ID
	 *
	 * @param string $province_name
	 * @return int
	 */
	public function getProvinceId($province_name) {
		return (int) array_search($province_name, CConfig::get('define_province'));
	}

	/**
	 * 通过系统名称获取系统ID
	 *
	 * @param string $os_name
	 * @return int
	 */
	public function getOsId($os_name) {
		return (int) array_search($os_name, CConfig::get('define_os'));
	}

	/**
	 * 通过平台名称获取平台ID
	 *
	 * @param string $platform_name
	 * @return int
	 */
	public function getPlatformId($platform_name) {
		return (int) array_search($platform_name, CConfig::get('define_platform'));
	}

	/**
	 * 通过设备名称获取设备ID
	 *
	 * @param string $device_name
	 * @return int
	 */
	public function getDeviceId($device_name) {
		return (int) array_search($device_name, CConfig::get('define_device'));
	}

	/**
	 * 获取下载方式ID
	 *
	 * @param string $mode_name
	 * @return int
	 */
	public function getModeId($mode_name) {
		return (int) array_search($mode_name, CConfig::get('define_mode'));
	}

	/**
	 * 通过系统名称及系统版本名称获取系统版本ID
	 *
	 * @param string $os_name 系统名称
	 * @param string $version_name 系统版本名称
	 * @return int
	 */
	public function getOsVersionName($os_name, $version_name) {
		$version_name = CFormat::filterStr($version_name); //过滤字符串防止注入
		$_os_version_arr = CConfig::get('define_os_version');
		if (isset($_os_version_arr[$os_name]) && isset($_os_version_arr[$os_name][$version_name]))
			return $_os_version_arr[$os_name][$version_name];
		return $version_name;
	}

	/**
	 * 获取平台配置数组
	 *
	 * @return array
	 */
	public function getPlatformArr() {
		return CConfig::get('define_platform');
	}

	/**
	 * 获取设备配置数组
	 *
	 * @return array
	 */
	public function getDeviceArr() {
		return CConfig::get('define_device');
	}

	/**
	 * 通过IP获取地区代码
	 */
	public function getRegionByIp() {
		$ip = Dh::app()->getRequester()->getUserHostAddress();

		$region = ltrim(CTool::convertIp($ip, true), '-');
		if (' LAN' ==  $region) {
			$province = $city = '';
		} else {
			if (preg_match('/(内蒙古|西藏|新疆|广西|宁夏)/', $region, $province)) {
				$province = $province[1];
				$_region = explode($province, $region);
				$city = isset($_region[1]) ? trim(substr($_region[1], 0, strrpos($_region[1], ' '))) : '';
			} else {
				$_region = explode('省', $region);
				$province = isset($_region[1]) ? substr($_region[0], strrpos($_region[0], ' ')) : '';
				$_region = explode('市', $region);
				$city = isset($_region[1]) ? substr($_region[0], ('' == $province ? strrpos($_region[0], ' ') : strrpos($_region[0], '省') + 3)) : '';
			}
		}

		$region = 0;
		if ('' != $city) {
			$region = (int) $this->model('City')->_select([
				'field' => 'city_id',
				'select' => 'city_id',
				'where'  => 'name like \'' . $city . '%\'',
				'limit'  => 1
			]);
			if ($region)
				return $region;
		}

		if ('' != $province) {
			$region = (int) $this->model('Province')->_select([
				'field' => 'province_id',
				'select' => 'province_id',
				'where'  => 'name like \'' . $city . '%\'',
				'limit'  => 1
			]);
			if ($region)
				return $region;
		}

		return $region;
	}

	/**
	 * 获取浏览器类型
	 *
	 * @return string
	 */
	public function getBrowser(){
		if(strpos($this->ua,'MSIE')!==false || strpos($this->ua,'rv:11.0')) //ie11判断
			return "ie";
		else if(strpos($this->ua,'Firefox')!==false)
			return "firefox";
		else if(strpos($this->ua,'Chrome')!==false)
			return "chrome";
		else if(strpos($this->ua,'Opera')!==false)
			return 'opera';
		else if((strpos($this->ua,'Chrome')==false)&&strpos($this->ua,'Safari')!==false)
			return 'safari';
		else
			return 'unknown';
	}

	/**
	 * 获取浏览器版本
	 *
	 * @return string
	 */
	public function getBrowserVersion(){
			if (empty($this->ua))
				return 'unknow';

			if (preg_match('/MSIE\s(\d+)\..*/i', $this->ua, $result))
				return $result[1];
//			elseif (strpos($this->ua,'rv:11.0'))
//				return 11;
			elseif (preg_match('/FireFox\/(\d+)\..*/i', $this->ua, $result))
				return $result[1];
			elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $this->ua, $result))
				return $result[1];
			elseif (preg_match('/Chrome\/(\d+)\..*/i', $this->ua, $result))
				return $result[1];
			elseif ((strpos($this->ua,'Chrome')==false)&&preg_match('/Safari\/(\d+)\..*$/i', $this->ua, $result))
				return $result[1];
			else
				return 'unknow';
	}
}
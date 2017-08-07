<?php
namespace UC;

/**
 * 用户中心客户端组件
 *
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.weile.com/
 * @copyright Copyright &copy; 2017 DongHai Hsing
 * @package UC
 */
class Client {
	/**
	 * 登录接口
	 */
	const API_LOGIN = '/api/login';

	/**
	 * 授权登录接口
	 */
	const API_TOKEN_LOGIN = '/api/tokenLogin';

	/**
	 * 修改密码接口
	 */
	const API_PASSWORD = '/api/password';

	/**
	 * 获取指定ID的用户信息接口
	 */
	const API_USERS = '/api/users';

	/**
	 * 登录用户数据对象
	 *
	 * @var User
	 */
	public $user = null;

	/**
	 * 错误提示信息
	 *
	 * @var string
	 */
	public $msg = '';

	/**
	 * 登录用户数据对象
	 *
	 * @var API
	 */
	private $_api = null;

	/**
	 * 构造方法
	 *
	 * @param array $config 与Config相对应的参数数组
	 *      必须传入的参数如下:
	 *          tag: 应用标识符
	 *          key: 应用秘钥
	 *          version: 应用版本
	 *          server_url: UC服务器地址
	 * @param array $user_data 用户数据数组, 一般用在登陆后反复使用用户数据的场景, 登陆后请自行将用户数据缓存(调用User::getArray可获得所有用户数据)
	 */
	public function __construct($config, $user_data = []) {
		if (!empty($config) && is_array($config)) {
			foreach ($config as $k => $v) {
				if (property_exists('\UC\Config', $k))
					Config::$$k = $v;
			}
		}

		if (!empty($user_data) && is_array($user_data))
			$this->getUserByData($user_data);
		$this->_api = new API();
	}

	/**
	 * 通过数组生成用户对象
	 */
	public function getUserByData($data) {
		$this->user = new User($data);
		return $this->user;
	}

	/**
	 * 使用账号密码登录
	 *
	 * @param string $username 用户名
	 * @param string $password 密码(小写md5值, 即:将用户输入的密码进行一次md5)
	 * @return false|User
	 */
	public function login($username, $password) {
		if (!preg_match('/^[a-f0-9]{32}$/', $password))
			return $this->err('invalid password');
		$this->_api->assignParam('username', $username);
		$this->_api->assignParam('password', $password);
		$this->_api->assignParam('ip', $this->getIP());
		if (!$this->_api->query(self::API_LOGIN))
			return $this->err($this->_api->msg);
		$this->user = new User($this->_api->result['data']);
		return $this->user;
	}

	/**
	 * 获取第三方应用授权访问地址
	 *
	 * @param string $url 第三方应用访问地址
	 * @return bool|string
	 */
	public function tokenLogin($url) {
		if (!$this->user instanceof User || !$this->user->id)
			return $this->err('not login');
		if ('' == $this->user->token || date('Y-m-d H:i:s') > $this->user->token_expire)
			return $this->err('invalid token');
		$symbol = false === strpos($url, '?') ? '?' : '&';
		$param = '_app=' . Config::$tag . '&_token=' . $this->user->token . '&_ip=' . $this->getIP();
		return $url . $symbol . $param . '&_sign=' . md5($param . '&key=' . Config::$key);
	}

	/**
	 * 验证授权登录
	 *
	 * @param string $app_tag 应用标识符
	 * @param string $token 登录凭证
	 * @param string $sign 签名
	 * @return false|User
	 */
	public function verifyTokenLogin($app_tag, $token, $sign) {
		$this->_api->assignParam('app', $app_tag);
		$this->_api->assignParam('token', $token);
		$this->_api->assignParam('sign', $sign);
		if (!$this->_api->query(self::API_TOKEN_LOGIN))
			return $this->err($this->_api->msg);
		$this->user = new User($this->_api->result['data']);
		return $this->user;
	}

	/**
	 * 修改登录密码
	 *
	 * @param string $username 用户名
	 * @param string $ori_password 原密码(小写md5值, 即:将用户输入的密码进行一次md5)
	 * @param string $new_password 新密码(小写md5值, 即:将用户输入的密码进行一次md5)
	 * @return bool
	 */
	public function password($username, $ori_password, $new_password) {
		if (!preg_match('/^[a-f0-9]{32}$/', $ori_password))
			return $this->err('invalid original password');
		if (!preg_match('/^[a-f0-9]{32}$/', $new_password))
			return $this->err('invalid new password');
		$this->_api->assignParam('username', $username);
		$this->_api->assignParam('ori_password', $ori_password);
		$this->_api->assignParam('new_password', $new_password);
		if (!$this->_api->query(self::API_PASSWORD))
			return $this->err($this->_api->msg);
		return true;
	}

	/**
	 * 根据ID获取用户信息
	 *
	 * @param mixed $params 用户ID列表, 整数表示单个用户ID, 也可以传入一个以ID为数组的列表, 或者用半角逗号,分隔的ID字符串
	 * @return array|false
	 *      如果调用成功将会返回多个用户ID与用户信息关联的数组(注: 被封停的用户不会返回), 字段包括但不限于以下内容:
	 *          [
	 *              ID => [
	 *                  'username' => '用户名',
	 *                  'addtime' => '用户创建时间',
	 *                  'jointime' => '入职时间',
	 *                  'positivetime' => '转正时间',
	 *                  'lastlogin' => '最后登录时间',
	 *                  'lastloginip' => '最后登录IP',
	 *                  'realname' => '真实姓名',
	 *                  'sex' => 性别,1:男,0:女,
	 *                  'email' => 'Email地址',
	 *                  'qq' => 'QQ号',
	 *                  'phone' => '手机号',
	 *                  'section_id' => '所属部门ID',
	 *                  'section_name' => '部门名称',
	 *                  'job_id' => '岗位ID',
	 *                  'job_name' => '岗位名称'
	 *              ]
	 *              ...
	 *          ]
	 */
	public function users($params) {
		$ids = [];
		if (is_int($params)) {
			$ids[] = $params;
		} elseif (is_string($params)) {
			if (preg_match('/[^\d,]/', $params))
				return $this->err('id list string error');
			$ids = explode(',', $params);
		} elseif (is_array($params)) {
			$ids = $params;
		}
		$ids = array_filter($ids, function($v) {
			if (empty($v))
				return false;
			if (0 !== strcmp($v, (int) $v))
				return false;
			return true;
		});
		if (empty($ids))
			return $this->err('id list empty');
		$ids = implode(',', $ids);

		$this->_api->assignParam('id', $ids);
		if (!$this->_api->query(self::API_USERS))
			return $this->err($this->_api->msg);
		return $this->_api->result['data'];
	}

	/**
	 * 获取权限配置列表
	 *
	 * @param string $api_url 目标应用获取权限列表的接口地址
	 * @param string $call_app_tag 发起此请求的应用标识符
	 * @return false|array
	 */
	public function purviews($api_url, $call_app_tag) {
		$this->_api->assignParam('call_app', $call_app_tag);
		if (!$this->_api->query($api_url, true))
			return $this->err($this->_api->msg);
		return $this->_api->result['data'];
	}

	/**
	 * 设置错误信息并返回false
	 *
	 * @param string $msg 提示信息
	 * @return false
	 */
	public function err($msg) {
		$this->msg = $msg;
		return false;
	}

	/**
	 * 返回客户端IP
	 *
	 * @return string
	 */
	public function getIP() {
		if (!empty($_SERVER["HTTP_CLIENT_IP"]))
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		elseif (!empty($_SERVER["REMOTE_ADDR"]))
			$cip = $_SERVER["REMOTE_ADDR"];
		else
			$cip = '';

		preg_match('/[\d\.]{7,15}/', $cip, $cips);
		$cip = isset($cips[0]) ? $cips[0] : 'unknown';
		return $cip;
	}
}

/**
 * 用户中心服务端接口组件
 */
class Server {
	/**
	 * 错误提示信息
	 *
	 * @var string
	 */
	public $msg = '';

	/**
	 * 构造方法
	 *
	 * @param array $config 与Config相对应的参数数组
	 *      必须传入的参数如下:
	 *          tag: 应用标识符
	 *          key: 应用秘钥
	 *          version: 应用版本
	 *          server_url: UC服务器地址
	 * @param array $user_data 用户数据数组, 一般用在登陆后反复使用用户数据的场景, 登陆后请自行将用户数据缓存(调用User::getArray可获得所有用户数据)
	 */
	public function __construct($config, $user_data = []) {
		if (!empty($config) && is_array($config)) {
			foreach ($config as $k => $v) {
				if (property_exists('\UC\Config', $k))
					Config::$$k = $v;
			}
		}
	}

	/**
	 * 验证接口请求, 调用前请手动删除请求数据中的路由键
	 */
	public function verify($data) {
		//验证请求参数
		if (empty($data['app']) || preg_match('/[^\w]/', $data['app']))
			return $this->err('app tag error');
		if (empty($_GET['sign']))
			return $this->err('sign does not empty');
		if (!preg_match('/^[0-9a-f]{32}$/', $data['sign']))
			return $this->err('sign error');
		$sign = trim($data['sign']);
		unset($data['sign']);
		ksort($data);
		$str = http_build_query($data);
		if ($sign != md5($str . '&key=' . Config::$key))
			return $this->err('sign error');
		return true;
	}

	/**
	 * 输出结果
	 *
	 * @param string $msg 消息内容
	 * @param int $status 状态码, 0:执行成功, 非零表示出错
	 * @param array $data 附加数据数组
	 */
	public function output($msg, $status = 500, $data = []) {
		$this->json([
			'status' => $status,
			'msg' => $msg,
			'data' => $data
		]);
	}

	/**
	 * 为服务器返回权限配置数据
	 *
	 * @param array $data 权限配置数组, 格式如下:
	 *      [
	 *			'USER' => '用户管理',
	 *			'USER-VIEW' => '用户查看',
	 *			'USERGROUP' => '用户组管理',
	 *			'USERGROUP-ISSUE-VIEW' => '用户组查看',
	 *		]
	 */
	public function json($data) {
		die(json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}

	/**
	 * 为服务器返回权限配置数据
	 *
	 * @param array $data 权限配置数组, 格式如下:
	 *      [
	 *			'USER' => '用户管理',
	 *			'USER-VIEW' => '用户查看',
	 *			'USERGROUP' => '用户组管理',
	 *			'USERGROUP-ISSUE-VIEW' => '用户组查看',
	 *		]
	 */
	public function responsePurviews($data) {
		$this->output(API::MSG_REPONSE_DONE, 0, $data);
	}

	/**
	 * 设置错误信息并返回false
	 *
	 * @param string $msg 提示信息
	 * @return false
	 */
	public function err($msg) {
		$this->msg = $msg;
		return false;
	}
}

/**
 * 接口控制器
 */
class API {
	/**
	 * 执行成功时返回的提示信息
	 */
	const MSG_REPONSE_DONE = 'done';

	/**
	 * 服务器接口响应代码
	 *
	 * @var int
	 */
	public $status = 0;

	/**
	 * 服务器接口响应信息
	 *
	 * @var string
	 */
	public $msg = '';

	/**
	 * 接口返回值
	 *
	 * @var array
	 */
	public $result = [];

	/**
	 * 请求参数数组
	 *
	 * @var array
	 */
	private $_params = [];

	/**
	 * 添加请求参数
	 *
	 * @param string $key 参数名
	 * @param mixed $value 参数值
	 */
	public function assignParam($key, $value) {
		$this->_params[$key] = $value;
	}

	/**
	 * 移除请求参数
	 *
	 * @param string $key 参数名
	 */
	public function removeParam($key) {
		unset($this->_params[$key]);
	}

	/**
	 * 清空请求参数
	 */
	public function cleanParam() {
		$this->_params = [];
	}

	/**
	 * 获取参数签名
	 *
	 * @return string
	 */
	private function getSign() {
		ksort($this->_params);
		$str = http_build_query($this->_params);
		return md5($str . '&key=' . Config::$key);
	}

	/**
	 * 构建请求参数字符串
	 */
	private function buildQueryString() {
		return http_build_query(array_merge($this->_params, ['sign' => $this->getSign()]));
	}

	/**
	 * 发起API请求
	 *
	 * @param string $api 接口地址
	 * @param bool $with_domain 接口地址中是否已经携带了请求域名
	 * @return bool
	 */
	public function query($api, $with_domain = false) {
		if (empty($this->_params))
			return $this->err('request params does not empty', 1);
		$this->assignParam('app', Config::$tag);
		$this->assignParam('timestamp', time());

		if ($with_domain)
			$url = $api . (false === strpos($api, '?') ? '?' : '&') . $this->buildQueryString();
		else
			$url = rtrim(Config::$server_url, '/') . '/' . ltrim($api, '/') . '?' . $this->buildQueryString();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if (0 === strpos($url, 'https://')) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, Config::$api_timeout); //设置超时
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //强制使用IPv4
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回请求结果
		$data = curl_exec($ch);

		//返回结果
		if (empty($data)) {
			$err = curl_errno($ch);
			curl_close($ch);
			return $this->err('curl request error [status:' . $err . ']', 2);
		} else {
			curl_close($ch);
			$this->result = $this->parseResult($data);
			if ($this->result['status'])
				return $this->err($this->result['msg'], $this->result['status']);
			return true;
		}
	}

	/**
	 * 处理请求结果
	 *
	 * @param string $data API结果字符串
	 * @return array
	 */
	private function parseResult($data) {
		$data = json_decode($data, true);
		if (empty($data))
			return ['status' => 3, 'msg' => 'api result not is a json string'];
		if (!isset($data['status']) || 0 !== strcmp($data['status'], (int) $data['status']) || !isset($data['msg']))
			return ['status' => 4, 'msg' => 'invalid api result string'];
		$data['status'] = (int) $data['status'];
		return $data;
	}

	/**
	 * 设置错误信息并返回false
	 *
	 * @param string $msg 错误信息
	 * @param int $code 错误码
	 * @return false
	 */
	private function err($msg, $code = 500) {
		$this->msg = $msg;
		$this->status = $code;
		return false;
	}
}

/**
 * 配置类
 */
class Config {
	/**
	 * 应用标识符
	 * @var string
	 */
	public static $tag = '';

	/**
	 * 应用秘钥
	 * @var string
	 */
	public static $key = '';

	/**
	 * 应用版本
	 * @var string
	 */
	public static $version = '1.0';

	/**
	 * UC服务器地址
	 * @var string
	 */
	public static $server_url = 'http://uc.jiaxianghudong.com';

	/**
	 * UC服务器地址
	 * @var string
	 */
	public static $api_timeout = 10;
}

/**
 * 用户模型
 */
class User {
	/**
	 * 用户ID
	 * @var int
	 */
	public $id = 0;

	/**
	 * 状态 0:正常, -1:禁用
	 * @var int
	 */
	public $status = 0;

	/**
	 * 是否为团队负责人, 0:否, 1:是
	 * @var int
	 */
	public $is_leader = 0;

	/**
	 * 团队负责人ID
	 * @var int
	 */
	public $leader_id = 0;

	/**
	 * 部门ID
	 * @var int
	 */
	public $section_id = 0;

	/**
	 * 部门名称
	 * @var string
	 */
	public $section_name = '';

	/**
	 * 岗位ID
	 * @var int
	 */
	public $job_id = 0;

	/**
	 * 岗位名称
	 * @var string
	 */
	public $job_name = '';

	/**
	 * 用户名
	 * @var string
	 */
	public $username = '';

	/**
	 * 性别, 1:男, 0:女
	 * @var int
	 */
	public $sex = 1;

	/**
	 * 注册时间
	 * @var string
	 */
	public $addtime = '';

	/**
	 * 真实姓名
	 * @var string
	 */
	public $realname = '';

	/**
	 * Email
	 * @var string
	 */
	public $email = '';

	/**
	 * QQ
	 * @var string
	 */
	public $qq = '';

	/**
	 * 手机号
	 * @var string
	 */
	public $phone = '';

	/**
	 * 权限列表
	 * @var string
	 */
	public $purviews = '';

	/**
	 * 登录凭证
	 * @var string
	 */
	public $token = '';

	/**
	 * 登录凭证过期时间
	 * @var string
	 */
	public $token_expire = '';

	/**
	 * 构造方法
	 */
	public function __construct($data = []) {
		if (!empty($data) && is_array($data)) {
			foreach ($data as $k => $v) {
				if (property_exists($this, $k)) {
					if (is_int($this->$k)) {
						if (is_int($v))
							$this->$k = $v;
						if (is_string($v) && 0 === strcmp($v, (int) $v))
							$this->$k = (int) $v;
					} elseif (is_string($this->$k) && is_string($v)) {
						$this->$k = $v;
					} elseif (is_array($this->$k) && is_array($v)) {
						$this->$k = $v;
					}
				}
			}
		}
	}

	/**
	 * 将所有属性放入数组返回
	 *
	 * @return array
	 */
	public function getArray() {
		return get_object_vars($this);
	}

	/**
	 * 用户权限检查
	 *
	 * 用户具有的每个权限后都要附加一个半角逗号",", 例: AAA-BBB,CCC,DDD-EEE-FFF,GGG,
	 *
	 * @param string $p 需要的权限. |分隔的多个权限满足其一即为真, &分隔的权限必须全部满足, 不支持逻辑嵌套
	 * @param boolean $parent 当权限不满足时是否检查用户具有比指定权限更高级的权限; 父子关系用半角减号"-"依次分隔
	 * @param string $purviews , 如果为空则对当前用户的权限进行检测
	 * @return bool
	 */
	public function P($p, $parent = true, $purviews = null) {
		if (empty($p))
			return false;

		$has_purviews = is_null($purviews) ? $this->purviews : $purviews;

		// 如果是创始人
		if (false !== strpos($has_purviews, 'ROOT,'))
			return true;
		// 如果是超级管理员
		if (!preg_match('/[\|&]?ROOT[\|&]?/', $p) && false !== strpos($has_purviews, 'SUPERADMIN,'))
			return true;
		// 如果拥有全部权限
		if (!preg_match('/[\|&]?(ROOT|SUPERADMIN)[\|&]?/', $p) && false !== strpos($has_purviews, 'ALL,'))
			return true;

		// 指定权限检查
		if (false !== strpos($p, '|')) { // 如果是多个 或关系 的权限组
			$p = explode('|', $p);
			foreach ($p as $p_name) {
				if ($this->PP($p_name, $has_purviews, $parent))
					return true;
			}
			return false;
		} elseif (false !== strpos($p, '&')) { // 如果是多个 与关系 的权限组
			$p = explode('&', $p);
			foreach ($p as $p_name) {
				if (!$this->PP($p_name, $has_purviews, $parent))
					return false;
			}
			return true;
		} else {
			return $this->PP($p, $has_purviews, $parent);
		}
	}

	/**
	 * 用户权限逻辑检查函数, 支援P()功能
	 *
	 * @param string $target 目标权限
	 * @param string $has 具有的权限
	 * @param boolean $parent 当权限不满足时是否检查用户具有比指定权限更高级的权限; 父子关系用半角减号"-"依次分隔
	 * @return bool
	 */
	private function PP($target, $has, $parent = true) {
		if (!preg_match('/(?<!\w)' . $target . ',/', $has)) {
			if ($parent) {
				$target = explode('-', $target);
				if (1 == ($c = count($target))) // 仅有一个权限时直接返回false
					return false;
				array_pop($target); // 删除最后一个权限, 因为已经检查过并不具备
				$p = $target[0];
				if (preg_match('/(?<!\w)' . $p . ',/', $has))
					return true;
				$i = 1;
				while (isset($target[$i])) {
					$p .= '-' . $target[$i];
					if (preg_match('/(?<!\w)' . $p . ',/', $has))
						return true;
					$i++;
				}
				return false;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
}
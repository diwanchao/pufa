<?php
/**
 * Gsdk客户端
 *
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.weile.com/
 * @copyright Copyright &copy; 2017 DongHai Hsing
 * @package usdk
 */
namespace Gsdk;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Hprose' . DIRECTORY_SEPARATOR . 'Hprose.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'user.php';

class Client {
	/**
	 * 应用标识符
	 *
	 * @var string
	 */
	public $appName = '';

	/**
	 * 应用秘钥
	 *
	 * @var string
	 */
	public $appKey = '';

	/**
	 * RPC服务器地址
	 *
	 * @var string
	 */
	public $rpcAddr = '';

	/**
	 * RPC是否为异步调用
	 *
	 * @var bool
	 */
	public $async = false;

	/**
	 * 最后一次操作的错误信息
	 *
	 * @var string
	 */
	public $msg = '';

	/**
	 * rpc客户端实例
	 *
	 * @var \Hprose\Client
	 */
	private $client = null;

	/**
	 * 构造方法
	 *
	 * @param string $appName 应用标识符
	 * @param string $appKey 应用秘钥
	 * @param string $rpcAddr RPC服务器地址
	 * @param bool $async RPC是否为异步调用
	 */
	public function __construct(string $appName, string $appKey, string $rpcAddr, bool $async = false) {
		$this->appName = $appName;
		$this->appKey = $appKey;
		$this->rpcAddr = $rpcAddr;
		$this->async = $async;

		$this->client = new \Hprose\Socket\Client($this->rpcAddr, $this->async);
		$this->client->addInvokeHandler([$this, 'authInvokeHandler']);
	}

	/**
	 * 验证字符串返回值
	 */
	public function getStringResult($ret, $checkVal = 'ok') {
		$this->msg = $ret;
		return $checkVal == $this->msg;
	}

	/**
	 * 将全部数据中的全部参数转为字符串类型
	 *
	 * @param array $arr 一位数组
	 * @return array
	 */
	public function arrayValueToString($arr) {
		return array_map(function($v) {return (string) $v;}, $arr);
	}

	/**
	 * 将全部数据中的全部参数转为整形类型
	 *
	 * @param array $arr 一位数组
	 * @return array
	 */
	public function arrayValueToInt($arr) {
		return array_map(function($v) {return (int) $v;}, $arr);
	}

	/**
	 * RPC认证中间件
	 */
	public function authInvokeHandler(string $name, array &$args, \stdClass $context, \Closure $next) {
		$time = time();
		array_unshift($args, [
			'app' => $this->appName,
			'ts' => $time,
			'sign' => md5($this->appName . '&' . $name . '&' . $time . '&' . $this->appKey)
		]);
		return $next($name, $args, $context);
	}

	/**
	 * 获取用户信息
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @return \Gsdk\User
	 */
	public function userInfo($userIDorToken) : User {
		$json = $this->client->UserInfo($userIDorToken);
		$user = new User($this);
		if (!empty($json)) {
			$data = json_decode($json, true);
			if (!empty($data['id'])) {
				$user->id = (int) $data['id'];
				$user->brand_id = (int) $data['brandid'];
				$user->db_brand_id = (int) $data['db_brand_id'];
				$user->username = $data['username'];
				$user->nickname = $data['nick'];
				$user->has_bank_password = 1 == $data['hasbankpass'];
				$user->money = (int) $data['money'];
				$user->bankmoney = (int) $data['bankmoney'];
				$user->xzmoney = (int) $data['xzmoney'];
				$user->ingot = (int) $data['lottery'];
				$user->prestige = (int) $data['prestige'];
				$user->sex = (int) $data['gender'];
				$user->user_type = (int) $data['usertype'];
				$user->avatar_id = (int) $data['avatar'];
				$user->user_state = $data['userstate'];
				$user->last_login_ip = $data['lastloginip'];
				$user->last_loginout_time = $data['lastloginouttime'];
				$user->userfrom = (int) $data['from'];
				$user->avatar_img = $data['customface'];
				$user->devicecode = $data['devicecode'];
				$user->reg_platform = $data['regplatform'];
				$user->reg_game = $data['reggame'];
				$user->closure_cause = $data['closure_cause'];
				$user->closure_time = $data['closure_time'];
				$user->closure_expire = $data['closure_expire'];
				$user->db_brand_id = (int) $data['db_brand_id'];
				$user->app_id = (int) $data['appid'];
				$user->channel_id = (int) $data['channelid'];
				$user->reg_time = substr($data['regdate'], 0, 19);
				$user->reg_ip = $data['regip'];
				$user->idcard = $data['idcard'];
				$user->mobile_phone = $data['mobilephone'];
				$user->realname = $data['realname'];
				$user->first_mobile_phone = $data['firstmobilephone'];
				$user->vip_value = $data['vipvalue'];
				$user->vip_endtime = $data['vipendtime'];
				$user->bindmac = $data['bindmac'];
				$user->city = $data['city'];

				$user->brand_id = 2 == $user->db_brand_id ? 1 : 2;
				$user->ok = true;

				if (!empty($data['LoginData']))
					$user->logindata = new LoginData($data['LoginData']);
			}
		}
		return $user;
	}

	/**
	 * 设置用户信息
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param array $params 需要修改的参数, 可以是一个或多个
	 *      nickname (string): 用户昵称
	 *      sex (int): 性别, 0:女, 1:男
	 *      city (int): 4位城市代码
	 *      avatarid (int): 头像ID, -1:自定义头像, 0:默认女头像, 1:默认男头像
	 *      avatarurl (string): 自定义头像地址, 当avatarid为-1时有效
	 * @return bool
	 */
	public function setUserInfo($userIDorToken, $params) {
		$this->msg = $this->client->SetUserInfo($userIDorToken, $params);
		if ('ok' == $this->msg)
			return true;
		switch ($this->msg) {
			case 'user data not update':
				$this->msg = '用户信息更新失败';
				break;
			case 'city not update':
				$this->msg = '城市编码更新失败';
				break;
			case 'has bad word':
				$this->msg = '昵称中含有敏感词';
				break;
			default:
				$this->msg = '更新失败(' . $this->msg . ')';
		}
		return false;
	}

	/**
	 * 根据用户名查询用户信息
	 *
	 * @param string $username 用户名
	 * @param int $userfrom 限定用户来源, 2:微乐自运营, 7:吉祥自运营
	 * @return bool|array
	 */
	public function findUserByUserName($username, $userfrom = null) {
		return $this->procedure('p_web_GetUserInfo', $username, 'username', $userfrom);
	}

	/**
	 * 获取用户成就信息
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param array|string $effortIDs 成就ID列表, 可以是半角逗号分隔的字符串, 也可以是数字数组
	 * @param bool $daily 是否获取日常成就, false为获取终身成就
	 * @return false|array|int 如果仅获取某一个道具的数量, 则会直接返回整形数值, 否则返回一个道具ID与数量的关联数组, false为出错
	 */
	public function getEffortEx($userIDorToken, $effortIDs, $daily = false) {
		if (is_string($effortIDs))
			$effortIDs = array_unique( explode( ',', trim(str_replace(' ', '', preg_replace('/,{2,}/', ',', $effortIDs)), ',') ) );
		if (is_int($effortIDs))
			$effortIDs = [$effortIDs];

		$ret = $this->client->GetEffortEx($userIDorToken, $effortIDs, $daily);
		if (empty($ret) || !is_array($ret) || 2 != count($ret)) {
			$this->msg = 'Gsdk接口结果错误';
			return false;
		}
		if ('ok' != $ret[0]) {
			$this->msg = $ret[0];
			return false;
		}
		$ret = $ret[1];
		return 1 == count($effortIDs) && isset($ret[$effortIDs[0]]) ? $ret[$effortIDs[0]] : $ret;
	}

	/**
	 * 获取用户道具信息
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param array|string $propIDs 道具列表, 可以是半角逗号分隔的字符串, 也可以是数字数组
	 * @return array|int 如果仅获取某一个道具的数量, 则会直接返回整形数值, 否则返回一个道具ID与数量的关联数组
	 */
	public function getProp($userIDorToken, $propIDs) {
		if (is_string($propIDs))
			$propIDs = array_unique( explode( ',', trim(str_replace(' ', '', preg_replace('/,{2,}/', ',', $propIDs)), ',') ) );
		if (is_int($propIDs))
			$propIDs = [$propIDs];

		$ret = $this->client->GetProp($userIDorToken, $propIDs);
		return 1 == count($propIDs) && isset($ret[$propIDs[0]]) ? $ret[$propIDs[0]] : $ret;
	}

	/**
	 * 操作用户道具信息
	 *
	 * @param string $opType 操作类型, 可以自行定义字符串, 如:fangka
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param array $dataMap 要操作的道具列表, 键名为道具ID, 键值为操作数量(负数为扣除)
	 * @param bool $mustLogined 是否要求用户必须在线才能操作
	 * @param int $actionDataID 执行此操作的数据ID(任务ID,充值订单ID等等)
	 * @return bool
	 *      返回true时表示操作成功
	 *      返回false时, 表示出错, Client::$msg 中可查看到错误原因
	 */
	public function opData(string $opType, $userIDorToken, array $dataMap, bool $mustLogined = false, int $actionDataID = 0) : bool {
		return $this->getStringResult($this->client->OpData($opType, $userIDorToken, $dataMap, $mustLogined, $actionDataID));
	}

	/**
	 * 调用主库存储过程
	 *
	 * @param string $procName 存储过程名称
	 * @param mixed $args 不定参数列表
	 * @return bool|array
	 */
	public function procedure(string $procName, ...$args) {
		$ret = $this->client->Procedure($procName, ...$args);
		if (!is_string($ret))
			return $ret;
		$this->msg = $ret;
		return false;
	}

	/**
	 * 调用日志库存储过程
	 *
	 * @param string $procName 存储过程名称
	 * @param mixed $args 不定参数列表
	 * @return bool|array
	 */
	public function procedureLog(string $procName, ...$args) {
		$ret = $this->client->ProcedureLog($procName, ...$args);
		if (!is_string($ret))
			return $ret;
		$this->msg = $ret;
		return false;
	}

	/**
	 * 获取指定一个或多个用户3天内的开房记录
	 *      !!! 注意: 这是一个测试功能, 不保证完全可靠, at: 2017-04-30
	 *
	 * @param array|int $userids 用户ID数组,
	 * @return array
	 */
	public function logCreateRoom($userids) {
		if (is_int($userids))
			$userids = [$userids];
		$userids = $this->arrayValueToInt($userids);
		return $this->client->LogCreateRoom(...$userids);
	}

	/**
	 * 获取用户朋友场对局记录
	 *
	 * @param int $userid 用户ID
	 * @param string $roomIDs 朋友场房间ID列表, 半角逗号分隔的字符串ID列表, 如: 1,2,3, 如果要获取全部房间的记录则需要传 0
	 * @param int $dayLimit 要获取几天内的数据
	 * @return array, 每个子项都是一个 \stdClass 对象
	 */
	public function logScoreList(int $userid, string $roomIDs, $dayLimit = 3) {
		return $this->client->LogScoreList($userid, $roomIDs, $dayLimit);
	}

	/**
	 * 发送短信验证码
	 *
	 * @param string $phone 手机号码
	 * @param string $code 验证码
	 * @return bool
	 */
	public function smsCaptcha($phone, $code) {
		$ret = $this->client->SmsCaptcha($phone, $code);
		if ($ret['success'])
			return true;
		$json = json_decode($ret['msg'], true);
		if (isset($json['error_response']['msg']))
			$this->msg = $json['error_response']['msg'] . (isset($json['error_response']['sub_msg']) ? ' (' . $json['error_response']['sub_msg'] . ')' : '');
		return false;
	}

	/**
	 * 激活账号
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param array $params 激活参数数组
	 *      id: 用户ID
	 *      username: 用户名
	 *      password: 密码,
	 *      idcard: 身份证号码
	 *      phone: 手机号码
	 *      regplatform: 激活的设备平台, 值可以是: ios, android, windows
	 *      userfrom: 用户来源
	 * @return bool
	 */
	public function activate($userIDorToken, $params) {
		$this->msg = $this->client->Activate($userIDorToken, $params);
		if ('ok' == $this->msg)
			return true;
		switch ($this->msg) {
			case 'activated':
				$this->msg = '您的账号已激活，无需重复激活';
				break;
			case 'username was exist':
				$this->msg = '该账号已存在，请您重新输入一个账号';
				break;
			default:
				$this->msg = '激活失败';
		}
		return false;
	}

	/**
	 * 敏感词检查
	 *
	 * @param string $str 要检查的字符串
	 * @return bool
	 *      返回 true 表示没有发现敏感词
	 *      返回 false 表示传入的字符串中有敏感词, 这次可以在msg中看到具体是哪个词
	 */
	public function hasBadWord($str) {
		return $this->getStringResult($this->client->HasBadWord($str), '');
	}

	/**
	 * 修改登录密码(需要原密码)
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param string $oldPass 原密码(3次MD5之后的值, 每次MD5皆需大写)
	 * @param string $newPass 新密码(3次MD5之后的值, 每次MD5皆需大写)
	 * @return bool
	 */
	public function passwordByOld($userIDorToken, $oldPass, $newPass) {
		return $this->getStringResult($this->client->PasswordByOld($userIDorToken, $oldPass, $newPass));
	}

	/**
	 * 修改背包密码(需要原密码)
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param string $oldPass 原密码(2次MD5之后的值, 每次MD5皆需大写)
	 * @param string $newPass 新密码(2次MD5之后的值, 每次MD5皆需大写)
	 * @return bool
	 */
	public function secondPasswordByOld($userIDorToken, $oldPass, $newPass) {
		return $this->getStringResult($this->client->SecondPasswordByOld($userIDorToken, $oldPass, $newPass));
	}

	/**
	 * 设置登录密码
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param string $password 新密码(3次MD5之后的值, 每次MD5皆需大写)
	 * @return bool
	 */
	public function password($userIDorToken, $password) {
		return $this->getStringResult($this->client->Password($userIDorToken, $password));
	}

	/**
	 * 设置背包密码
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param string $password 新密码(2次MD5之后的值, 每次MD5皆需大写)
	 * @return bool
	 */
	public function secondPassword($userIDorToken, $password) {
		return $this->getStringResult($this->client->SecondPassword($userIDorToken, $password));
	}

	/**
	 * 修改账号
	 *
	 * @param int|string $userIDorToken 用户ID或用户登录Token
	 * @param string $username 新账号
	 * @return bool
	 */
	public function username($userIDorToken, $username) {
		return $this->getStringResult($this->client->Username($userIDorToken, $username));
	}

	/**
	 * 检查指定账号是否已被注册
	 *
	 * @param string $username 账号
	 * @param int $userfrom 用户来源ID, 必须
	 * @return bool
	 */
	public function isRegistered($username, $userfrom) {
		return $this->client->IsRegistered($username, $userfrom);
	}

	/**
	 * 账号注册
	 *
	 * @param array $params 注册信息数组
	 *      可用键值
	 *            username nvarchar(36), --用户名
	 *            nickname nvarchar(32), --昵称
	 *            password char(32), --密码
	 *            ip int, --注册IP
	 *            userfrom tinyint = 2, --平台来源, 0:未知; 1:游客; 2:微乐; 7:吉祥...
	 *            is_mobile_reg tinyint = 0, --是否为手机号注册
	 *            is_weile_user tinyint = 0, --是否为微乐用户
	 *            regplatform varchar(20) = 'windows', --注册时所使用的系统
	 *            reggame varchar(50) = NULL, --注册时所玩游戏
	 *            idcard varchar(20) = NULL, --身份证号
	 *            realname nvarchar(20) = NULL, --真实姓名
	 *            devicecode varchar(42) = NULL, --设备ID
	 *            sex tinyint = 1, --性别,1男,0女
	 *            spread_id int = NULL, --推荐人ID
	 *            flysheetid varchar(36) = NULL, --传单ID
	 *            brand_id int = NULL, --品牌ID,1吉祥,2微乐
	 *            app_id int = NULL, --网站应用ID
	 *            channel_id int = NULL --渠道ID
	 *
	 * @return int // 注册成功将返回新注册用户的ID, 失败返回0
	 */
	public function register($params) {
		$ret = $this->client->Register($params);
		if (empty($ret) || !is_array($ret) || 2 != count($ret)) {
			$this->msg = 'Gsdk接口结果错误';
			return 0;
		}
		if (!$ret[0]) {
			$this->msg = $ret[1];
			return 0;
		}
		return $ret[0];
	}

	/**
	 * 第三方登录
	 *
	 * @param string $openid 第三方用户标识符(在游戏数据库中作为账号字段)
	 * @param int $from 平台来源, 0:未知; 1:游客; 2:微乐; 7:吉祥...
	 * @param int $client 客户端类型ID, 或运算方式取得. 例: 32768 | 4 | 8 | 16 表示微乐安卓客户端登录
	 *      0x0     0       未知
	 *      0x1     1       PC登录
	 *      0x2     2       网页登录
	 *      0x4     4       安卓登录
	 *      0x8     8       安卓平板
	 *      0x10    16      安卓电视
	 *      0x20    32      iphone登录
	 *      0x40    64      ipad登录
	 *      0x80    128     ios电视
	 *      1<<14   16384   吉祥
	 *      1<<15   32768   微乐
	 *              65535   全平台, 不传client参数也为全平台
	 * @param string $ip 客户端登录IP字符串, 如: 192.168.0.01
	 * @param int $weile 是否为微乐用户, 1:是, 0:否
	 * @param int $app_id 网站通信AppID
	 * @param int $channel_id 渠道ID
	 * @param string $version 登录版本号
	 * @return \stdClass, 出错时返回空对象
	 *      例:
	 *          object(stdClass)#16 (8) {
	 *				["result"]=>
	 *				int(0)
	 *				["msg"]=>
	 *				string(2) "ok"
	 *				["id"]=>
	 *				int(147749)
	 *				["hallID"]=>
	 *				int(0)
	 *				["url"]=>
	 *				string(14) "101.37.186.228"
	 *				["ip"]=>
	 *				int(1696971492)
	 *				["port"]=>
	 *				int(6349)
	 *				["code"]=>
	 *				string(32) "F85B9089F917114FB00A629DC4103548"
	 *			}
	 */
	public function thirdpartyLogin($openid, $from, $client, $ip, $weile, $app_id, $channel_id, $version) {
		$ret = $this->client->ThirdpartyLogin($this->arrayValueToString([
			'openid' => $openid,
			'from' => $from,
			'client' => $client,
			'ip' => $ip,
			'weile' => $weile,
			'app' => $app_id,
			'channel' => $channel_id,
			'version' => $version
		]));
		if (empty($ret) || !is_array($ret) || 2 != count($ret)) {
			$this->msg = 'Gsdk接口结果错误';
			return new \stdClass();
		}
		if ('ok' != $ret[1]) {
			$this->msg = $ret[1];
			return new \stdClass();
		}
		return $ret[0];
	}

	/**
	 * 踢用户下线
	 *
	 * @param int $userID 用户ID
	 * @return bool
	 */
	public function kick($userID) {
		$this->msg = $this->client->Kick($userID);
		return 'ok' == $this->msg;
	}

	/**
	 * 解散桌子
	 *
	 * @param int $userID 用户ID
	 * @return bool
	 */
	public function dissolve($userID) {
		$this->msg = $this->client->Dissolve($userID);
		return 'ok' == $this->msg;
	}

	/**
	 * 向客户端发送通知(警告)消息
	 *
	 * @param array $params 参数列表
	 * type
	 *      0 	        保留
	 *	    1 	        玩家与玩家
	 *	    2	        系统消息
	 *	    4 	        门派内部群聊
	 *	    8 	        门派系统消息
	 *	    16	        GM与玩家（单独聊天）
	 *	    32	        小喇叭
	 *	    64	        赠送物品附言
	 *	    128	        使用道具
	 *	    256	        大厅消息(强制提示)
	 *	    512	        后台命令消息（特殊）
	 *	    1024	    游戏喜报（游戏中获得元宝之类奖励）
	 *	    -2147483648 回写数据库（请通过参数outlinesave设置)
	 *
	 * client
	 *      0x0     0       未知
	 *      0x1     1       PC登录
	 *      0x2     2       网页登录
	 *      0x4     4       安卓登录
	 *      0x8     8       安卓平板
	 *      0x10    16      安卓电视
	 *      0x20    32      iphone登录
	 *      0x40    64      ipad登录
	 *      0x80    128     ios电视
	 *      1<<14   16384   吉祥
	 *      1<<15   32768   微乐
	 *              65535   全平台, 不传client参数也为全平台
	 * @return bool
	 */
	public function notification(array $params = []) {
		$params = array_merge([
			'type' => 2, // 消息类型
			'to' => 0, // 接受者ID, 默认为0, 0表示发送给所有在线玩家, !!注意, 尽量不要设置0
			'msg' => '', // 消息内容
			'client' => 65535, // 目标用户的客户端类型
			'outlinesave' => 1, // 如果玩家不在线, 是否将消息存入数据库以供下次登录时查看, !!注意, 此参数尚未支持
			'from' => 0, // 发送者ID,默认为0
			'nick' => '', // 发送者昵称
		], $params);
		$params['msg'] = urlencode($params['msg']);
		$params = $this->arrayValueToString($params);
		$this->msg = $this->client->Notification($params);
		return 'ok' == $this->msg;
	}

	/**
	 * 向指定用户发命令消息(用户需登录)
	 *
	 * @param int $userID 用户ID
	 * @param string $cmd 命令消息字符串(如果含非ascii字符, 需进行urlencode)
	 * @return bool
	 */
	public function cmdMsg(int $userID, string $cmd) {
		$this->msg = $this->client->CmdMsg($userID, $cmd);
		return 'ok' == $this->msg;
	}
}
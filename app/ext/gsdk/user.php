<?php
/**
 * 用户模型类
 *
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.weile.com/
 * @copyright Copyright &copy; 2017 DongHai Hsing
 * @package gsdk
 */
namespace Gsdk;

class User {
	public $ok = false; //用户信息是否成功拉取

	public $brand_id = 0; //品牌ID, 1:吉祥, 2:微乐, 3:心悦
	public $id = 0; //用户ID
	public $username = '';
	public $nickname = '';
	public $has_bank_password = false; //是否设置背包密码
	public $money = 0; //身上携带豆数
	public $bankmoney = 0; //背包豆数
	public $xzmoney = 0; //虚拟货币
	public $ingot = 0; //元宝
	public $prestige = 0; //声望
	public $sex = 1; //性别, 1:男, 0:女
	public $user_type = 0; //用户类型
	public $avatar_id = 1; //用户头像索引ID, -1表示使用自定义头像
	public $user_state = 0; //用户状态, 负数为封停
	public $last_login_ip = ''; //最后登录ID
	public $last_loginout_time = ''; //最后退出时间
	public $userfrom = 0; //用户来源
	public $avatar_img = ''; //用户头像图片地址
	public $devicecode = ''; //机器码
	public $reg_platform = ''; //注册平台
	public $reg_game = ''; //注册时的游戏标识符(注意这个值一般都应该是空的, 如果不为空则会将用户锁定在该游戏, 不能在其他地方登陆)
	public $closure_cause = ''; //封停原因
	public $closure_time = ''; //封停时间
	public $closure_expire = ''; //封停解封日期
	public $db_brand_id = 0; //游戏数据库中的品牌ID, 1:微乐, 2:吉祥
	public $product_id = 0; //注册产品ID
	public $app_id = 0; //注册应用ID
	public $channel_id = 0; //渠道ID
	public $reg_time = ''; //注册时间
	public $reg_ip = ''; //注册IP
	public $idcard = ''; //身份证号码
	public $mobile_phone = ''; //手机号
	public $realname = ''; //真实姓名
	public $first_mobile_phone = ''; //首次绑定的手机号
	public $vip_value = 0; //VIP经验值
	public $vip_endtime = ''; //VIP到期时间
	public $bindmac = ''; //用户绑定的机器码(仅针对吉祥PC客户端有效)
	public $city = ''; //注册/实名认证时所填写的身份证号码前四位(身份证前4位为城市代码), 此参数不可靠, 不要用此来作为判断依据

	/**
	 * 登录数据实例, null表示未登录
	 * @var LoginData
	 */
	public $logindata = null;

	/**
	 * Gsdk客户端实例
	 *
	 * @var Client
	 */
	private $gsdk = null;

	/**
	 * 构造方法
	 */
	public function __construct($gsdk = null) {
		if (null != $gsdk && $gsdk instanceof Client)
			$this->gsdk = $gsdk;
	}

	/**
	 * 返回数组格式数据
	 */
	public function toArray() {
		return get_object_vars($this);
	}
}

/**
 * 登录数据类, 由第三方服务器登录返回
 *
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.weile.com/
 * @copyright Copyright &copy; 2017 DongHai Hsing
 */
class LoginData {
	public $result = -1;
	public $msg = '';
	public $id = 0;
	public $ipvalue = 0; //登录IP, 整数
	public $ip = ''; //登录IP, 字符串
	public $loginserver = 0; //登录服务器ID
	public $hallserver = 0; //大厅服务器ID
	public $session = ''; //Token
	public $version = '';
	public $app = 0; //AppID
	public $channel = 0; //渠道ID
	public $client = 0;

	/**
	 * 构造方法
	 */
	public function __construct($data = []) {
		foreach ($data as $k => $v) {
			if (property_exists($this, $k))
				$this->$k = is_int($this->$k) ? (int) $v : $v;
		}
	}
}
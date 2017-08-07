<?php
@ini_set('display_errors', 'On');
error_reporting(2047);
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'gsdk.php';

// 实例化SDK
$gsdk = new \Gsdk\Client('test', '098f6bcd4621d373cade4e832627b4f6', 'tcp://192.168.100.1:4321');

// 获取用户信息, 参数可以是登录令牌(Token)也可以是用户ID
/*
 var_dump($gsdk->userInfo(147749));
exit()
;*/

// 获取用户道具信息, 第1个参数可以是登录令牌(Token)也可以是用户ID
/*
$ret = $gsdk->getProp(147749, 258);
if (!$ret)
	die($gsdk->msg);
var_dump($ret);
exit();
*/

// 获取用户成就数据, 第1个参数可以是登录令牌(Token)也可以是用户ID
/*
$ret = $gsdk->getEffortEx(147749, '15,16,17,18');
if (!$ret)
	die($gsdk->msg);
var_dump($ret);
exit();
*/

// 操作用户道具, 第2个参数可以是登录令牌(Token)也可以是用户ID
/*
if (!$gsdk->opData("fangka", 147749, [258 => 10], 1))
	die($gsdk->msg);
die('ok');
*/


// 调用主库存储过程
/*
$ret = $gsdk->procedure("p_web_GetUserInfo", 108820, 'userid');
if (false === $ret)
	die($gsdk->msg);
var_dump($ret);
*/

// 调用日志库存储过程
/*
$ret = $gsdk->procedureLog("p_web_Log_Game", 25764776, '2017-04-29', '2017-04-30');
if (false === $ret)
	die($gsdk->msg);
var_dump($ret);
*/

// 发送短信验证码
/*
var_dump($gsdk->smsCaptcha('17790000521', '1234'));
var_dump($gsdk->msg);
*/

// 获取指定一个或多个用户3天内的开房记录
/*
var_dump($gsdk->logCreateRoom(108820));
*/

// 激活账号
/*
if (!$gsdk->activate(27618180, ['username' => 'xdh1231233', 'password' => 'A43F3606633FE7B3127774F5A73F4CFF', 'regplatform' => 'ios', 'userfrom' => 2]))
	die($gsdk->msg);
die('ok');
*/

// 修改用户信息
/*
if (!$gsdk->setUserInfo(27618180, ['nickname' => '邢东海123', 'sex' => 1, 'avatarid' => -1, 'avatarurl' => 'https://cloudavatar2.jixiang.cn/uploads/8137/27628137-0.jpg']))
	die($gsdk->msg);
die('ok');
*/

// 敏感词检查
/*
if (!$gsdk->hasBadWord('你和他占中间'))
	die($gsdk->msg);
die('[没有敏感词]');
*/

// 修改登录密码(通过原密码)
/*
if (!$gsdk->passwordByOld(108820, 'B2A42024E9282500431BBA385C7708B3', 'A43F3606633FE7B3127774F5A73F4CFF'))
	die($gsdk->msg);
die('ok');
*/

// 修改背包密码(通过原密码)
/*
if (!$gsdk->secondPasswordByOld(108820, 'B2A42024E9282500431BBA385C7708B3', 'A43F3606633FE7B3127774F5A73F4CFF'))
	die($gsdk->msg);
die('ok');
*/

// 设置登录密码
/*
if (!$gsdk->password(108820, 'A43F3606633FE7B3127774F5A73F4CFF'))
	die($gsdk->msg);
die('ok');
*/

// 设置背包密码
/*
if (!$gsdk->secondPassword(108820, 'A43F3606633FE7B3127774F5A73F4CFF'))
	die($gsdk->msg);
die('ok');
*/

// 修改用户名
/*
if (!$gsdk->username(108820, 'xdh123123'))
	die($gsdk->msg);
die('ok');
*/

// 检查账号是否被注册
/*
var_dump($gsdk->isRegistered('xdh240408', 2));
*/

// 注册账号
/*
$userid = $gsdk->register([
	'username' => 'xdh123123456',
    'nickname' => '东海东',
    'password' => 'A43F3606633FE7B3127774F5A73F4CFF',
	'ip' => 0,
    'userfrom' => 2,
    'is_mobile_reg' => 0,
    'is_weile_user' => 1,
    'regplatform' => 'android',
    'idcard' => '220202198805065719',
    'realname' => '邢东海',
    'brand_id' => 2,
    'app_id' => 122,
    'channel_id' => 200
]);
if (!$userid)
	die($gsdk->msg);
var_dump($userid);
*/

// 使用用户名查询账号
/*
var_dump($gsdk->findUserByUserName('xdh123123'));
*/

// 获取某用户朋友场对局记录
/*
var_dump($gsdk->logScoreList(29735334, '448,189,364', 3));
*/

// 第三方登录
/*
$login_data = $gsdk->thirdpartyLogin('xdh123123', 7, 16384 | 4 | 8 | 16, '192.168.0.1', 0);
if (empty($login_data))
	die($gsdk->msg);
var_dump($login_data);
*/

// 踢用户下线
/*
$ret = $gsdk->kick(147749);
if (false === $ret)
	die($gsdk->msg);
var_dump($ret);
*/

// 解散桌子
/*
$ret = $gsdk->dissolve(147749);
if (false === $ret)
	die($gsdk->msg);
var_dump($ret);
*/

// 发送警告消息
/*
$ret = $gsdk->notification([
	'to' => 147749,
    'type' => 2,
    'msg' => '测试消息'
]);
if (false === $ret)
	die($gsdk->msg);
var_dump($ret);
*/

// 发送命令消息
/*
$ret = $gsdk->cmdMsg(147749, 'type=firstcharge&state=0&msg=done');
if (false === $ret)
	die($gsdk->msg);
var_dump($ret);
*/
<?php
/**
 * Created by PhpStorm.
 * User: CuiShiqi
 * Mail: a@t-6.cn
 * Date: 2017/5/1
 * Time: 下午5:54
 */
class BaseController extends CController {
	/**
	 * 初始化方法
	 */
	public function init() {
		
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
		$this->json(array(
			'state' => $state,
			'msg' => $msg,
			'data' => $data
		));
	}

	/**
	 * 输出Json提示信息
	 *
	 * @access public
	 * @param string $msg 提示信息
	 * @param integer $state 状态
	 * @param array $data 数据数组
	 */
	public function outputJson($msg = '', $state = 621, $data = array()) {
		$this->json(array(
			'state' => $state,
			'msg' => $msg,
			'data' => $data
		));
	}

	/**
	 * 输出成功信息
	 *
	 * @access public
	 * @param array $data 数据数组
	 * @param string $msg 提示信息
	 */
	public function outputDone($data = array(), $msg = 'done') {
		$this->outputJson($msg, 0, $data);
	}

	/**
	 * 输出失败信息
	 *
	 * @access public
	 * @param array $data 数据数组
	 * @param string $msg 提示信息
	 */
	public function outputFailed($data = array(), $msg = 'failed') {
		$this->outputJson($msg, 611, $data);
	}

	/**
	 * 输出错误信息
	 *
	 * @access public
	 * @param string $msg 错误信息
	 * @param array $data 数据数组
	 */
	public function outputError($msg = 'error', $data = array()) {
		$this->json(array(
			'state' => 621,
			'msg' => $msg,
			'data' => $data
		));
	}

	/**
	 * 参数错误
	 *
	 * @access public
	 * @param string $param 参数名
	 * @param string $msg 提示信息
	 */
	public function paramErr($param, $msg = 'parameter error') {
		$this->outputJson($msg . ' [' . $param . ']', 501, array('name' => $param));
	}

	/**
	 * 缺少参数
	 *
	 * @access public
	 * @param string $param 参数名
	 * @param string $msg 提示信息
	 */
	public function paramMiss($param, $msg = 'missing parameter') {
		$this->outputJson($msg . ' [' . $param . ']', 502, array('name' => $param));
	}
}
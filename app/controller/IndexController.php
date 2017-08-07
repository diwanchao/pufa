<?php
/**
 * 首页控制器
 *
 */
class IndexController extends BaseController {


	public function init() {
		parent::init();
	}

	/**
	 * 显示首页
	 *
	 * @access public
	 */
	public function actionIndex() {
			$this->render('index');
	}


	/**
	*手机列表页
	*/
	public function actionList(){
		$this->render('list');
	}
	/**
	*手机合约
	*/
	public function actionContract(){
		$this->render('contract');
	}
	/**
	*手机颜色
	*/
	public function actionColour(){
		$this->render('colour');
	}
	/**
	*手机号码
	*/
	public function actionNumber(){
		$this->render('number');
	}
	/**
	*手机订单信息
	*/
	public function actionOrder(){
		$this->render('order');
	}
}
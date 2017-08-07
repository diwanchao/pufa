<?php
/**
 * 游戏控制器
 *
 * Created by PhpStorm.
 * User: CuiShiqi
 * Mail: a@t-6.cn
 * Date: 2017/5/1
 * Time: 下午5:53
 */
class GameController extends MobileBaseController {

	public function actionIndex() {
		$this->render('game');
	}
}
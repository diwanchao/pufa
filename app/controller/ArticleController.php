<?php
/**
 * 公告控制器
 *
 * Created by PhpStorm.
 * User: CuiShiqi
 * Mail: a@t-6.cn
 * Date: 2017/5/1
 * Time: 下午5:53
 */
class NoticeController extends MobileBaseController {

	public function actionIndex() {
		$list = array();
		$activeId=$this->model('Category')->selectRow(array(
			'select' =>'id',
			'where' => 'name=活动',
			));
		if (!empty($activeId)) {
			$lis = $this->model('Article')->_select(array(
					'where'=>'cid='.$activeId['id'],
				));
		}
		$this->render('active',array(
			'data'=>$list,
			));
	}
}
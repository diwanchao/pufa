<?php
/**
 * 活动控制器
 *
 * Created by PhpStorm.
 * User: CuiShiqi
 * Mail: a@t-6.cn
 * Date: 2017/5/1
 * Time: 下午5:53
 */
class ActiveController extends MobileBaseController {

	public $cid = 0;

	public function init(){
		$this->cid = $this->model('Category')->selectRow(array(
			'select' =>'id',
			'where' => 'name="活动"',
			));
	}

	public function actionIndex() {
		$list = array();
		if (!empty($this->cid)) {
			$list = $this->model('Article')->_select(array(
					'where'=>'cid='.$this->cid['id'],
				));
		}
		$this->render('active',array(
			'list'=>$list,
			));
	}

	public function actionDetail(){
		$lastArr = array('title'=>'没有了','url'=>'javascript:void(0)');
		$nextArr = array('title'=>'没有了','url'=>'javascript:void(0)');
		if (empty($_REQUEST['id']) || !CFormat::isInt($_REQUEST['id']))
			$this->str('参数错误');
		$id = (int) $_REQUEST['id'];
		$data = $this->model('Article')->selectRow(array('where' => 'id=' . $id));
		if (empty($data))
			throw new CHttpException(404, '页面不存在');
		$last = $this->model('Article')->selectRow(array('where' =>'id<'.$id.' and cid='.$this->cid['id'],'order'=>'id desc'));
		$next = $this->model('Article')->selectRow(array('where' =>'id>'.$id.' and cid='.$this->cid['id'],'order'=>'id asc'));
		if (!empty($last)) {
			$lastArr['title'] 	= $last['title'];
			$lastArr['url'] 	= $this->createUrl('active/detail',array('id'=>$last['id']));
		}
		if (!empty($next)) {
			$nextArr['title'] 	= $next['title'];
			$nextArr['url'] 	= $this->createUrl('active/detail',array('id'=>$next['id']));
		}
		$old = $this->model('Article')->getOldActive();

		$this->render('article',array(
			'tag'=>'active',
			'data'=>$data,
			'last'=>$lastArr,
			'next'=>$nextArr,
			'old' =>$old,
			));
	}
}
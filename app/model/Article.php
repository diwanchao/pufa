<?php
/**
 * 游戏模型
 *
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.xingdonghai.cn/
 * @copyright Copyright &copy; 2013 WeiLe Inc.
 * @package com.weile
 * @since 0.1
 * @version 0.1
 */
class Article extends CModel {
	protected $table = 'article';
	protected $pk = 'id';





	public function getOldActive(){
		$list = array();
		$active=$this->model('Category')->selectRow(array(
			'select' =>'id',
			'where' => 'name="活动"',
			));
		if (!empty($active)) {
			$list = $this->model('Article')->_select(array(
					'where'=>'cid='.$active['id'].' and createtime<'.time(),
					'limit'=>4,
			));
		}
		return $list;

	}
}
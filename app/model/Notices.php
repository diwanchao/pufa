<?php
/**
 * 公告模型
 *
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.xingdonghai.cn/
 * @copyright Copyright &copy; 2013 WeiLe Inc.
 * @package com.weile
 * @since 0.1
 * @version 0.1
 */
class Notices extends CModel {
	//protected $table = 'notices';
	protected $pk = 'id';
	public $categoryId = 0;


	public function __construct($id) {
		$category=$this->model('Category')->selectRow(array(
				'select' =>'id',
				'where' => 'name="公告"',
			));
		$this->categoryId = isset($category['id']) ? $category['id'] : 0;
	}
	/*
	*获取首页展示的公告
	*/
	public function getNotice(){
		return $this->model('Article')->_select(array(
				'where'	=>'cid='.$this->categoryId,
				'order' =>'createtime desc',
				'limit' =>3,
				));

	}
}
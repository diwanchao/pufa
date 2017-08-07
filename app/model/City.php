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
class City extends CModel {
	protected $table = 'city';
	protected $pk = 'id';
	public function __construct($id) {
		$this->_id = $id;
		$config = CConfig::get('area_db_config');
		$this->db = new CDb($config);
	}
}
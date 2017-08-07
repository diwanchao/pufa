<?php
/**
 * Created by PhpStorm.
 * User: CuiShiqi
 * Mail: a@t-6.cn
 * Date: 2017/7/6
 * Time: 下午11:16
 */
class MobilePageItem extends CModel{
	protected $table = 'mobile_page_item';
	protected $pk = 'config_id';

	protected $game_table = 'article';
	protected $meta_table = 'meta';

	/**
	 * 获取首页内容
	 *
	 * @return array
	 */
	public function getIndex() {
		return $this->bindData($this->select([
			'select' => 'mpt.*, g.title, g.pic, mp.channel_id',
			'from' => $this->table . ' AS mpt LEFT JOIN ' . $this->game_table . ' AS g ON mpt.game_id=g.id LEFT JOIN mobile_page as mp ON mpt.config_id=mp.id',
			'where' => 'top=1 AND mp.channel_id=0'
		]));
	}


	/**
	 * 绑定数据
	 * @param $data
	 * @param string $id_field
	 *
	 * @return array
	 */
	public function bindData($data, $id_field = 'game_id') {
		if (empty($data))
			return $data;
		if (!is_array(current($data)))
			$data = [$data];

		if (!empty($data)) {
			$ids = array_column($data, $id_field);
			$metas = $this->getMetaDatas($ids);
			if (!empty($data)) {
				foreach ($data as $key => $item) {
					$data[$key]['meta'] = $this->getMetaDataById($item[$id_field], $metas);
				}
			}
		}
		return $data;
	}

	/**
	 * 获取所有的元标记
	 *
	 * @param $ids
	 *
	 * @return mixed
	 */
	public function getMetaDatas($ids) {
		$data = $this->select([
			'from' => $this->meta_table,
			'where' => 'meta_data_id IN(' . implode(',', $ids) . ')'
		]);
		$tmp = [];
		if (!empty($data)) {
			foreach ($data as $item) {
				$tmp[$item['meta_data_id']][$item['meta_name']] = $item['meta_value'];
			}
		}
		return $tmp;
	}

	/**
	 * 获取项目袁标记元素
	 *
	 * @param $id
	 * @param $metaDatas
	 *
	 * @return array
	 */
	public function getMetaDataById($id, $metaDatas) {
		if (isset($metaDatas[$id]))
			return $metaDatas[$id];
		return [];
	}

}
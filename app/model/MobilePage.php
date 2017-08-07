<?php
/**
 * 手机落地页配置模型
 *
 * @author DongHai Hsing <xingdonghai@gmail.com>
 * @link http://www.xingdonghai.com/
 * @copyright Copyright &copy; 2016 WeiLe Inc.
 * @package 20_com.weile
 * @since 1.0
 * @version 0.1 (Create: 2016-02-25, Update: 2016-02-25)
 */
class MobilePage extends CModel {
	protected $table = '#@__mobile_page';
	protected $pk = 'id';
	protected $table_item = '#@__mobile_page_item';
	protected $page_table = '#@__mobile_page AS m LEFT JOIN [#@__stats].#@__channel AS c ON m.channel_id=c.id';
	protected $page_fields = 'm.*, c.name AS channel_name';

	/**
	 * 获取单条记录
	 * @param int $config_id
	 * @return array
	 */
	public function get($config_id) {
		$data = $this->selectRowByPk((int) $config_id);
		if (empty($data))
			return [];
		$data['channel_id'] = explode(',', $data['channel_id']);
		$data['data'] = json_decode($data['data'], true);
		$data['items'] = $this->select([
			'from' => $this->table_item,
			'where' => 'config_id=' . $config_id,
			'order' => 'top DESC, sort DESC',
		]);
		return $data;
	}

	/**
	 * 获取首页记录
	 * @return array
	 */
	public function getIndex() {
		$data = $this->selectRow(['where' => 'type=1']);
		if (empty($data))
			return [];
		$data['data'] = json_decode($data['data'], true);
		$data['items'] = $this->select([
			'from' => $this->table_item,
			'where' => 'config_id=' . $data['id'],
			'order' => 'top DESC, sort DESC',
		]);
		return $data;
	}

	/**
	 * 获取配置的Banner
	 * @return array
	 */
	public function getBanner($config_id) {
		if ($config_id)
			$data = $this->selectRow(['select' => 'data', 'field' => 'data', 'where' => 'id=' . $config_id]);
		if (empty($data))
			$data = $this->selectRow(['select' => 'data', 'field' => 'data', 'where' => 'type=1']);
		return empty($data) ? [] : json_decode($data, true);
	}

	/**
	 * 获取与子项关联的数据数组
	 * @return array
	 */
	public function getASSData() {
		$source = $this->select(['order' => 'id ASC']);
		$data = [];
		foreach ($source as $row) {
			$data[$row['id']] = [
				'channel_id' => $row['channel_id'],
				'region' => $row['region'],
				'region_name' => $row['region_name'],
				'type' => $row['type'],
			];
		}
		$source = $this->select([
			'from' => $this->table_item,
			'order' => 'config_id DESC, top DESC, sort DESC',
		]);
		foreach ($source as $item) {
			if (isset($data[$item['config_id']])) {
				if (!isset($data[$item['config_id']]['items']))
					$data[$item['config_id']]['items'] = [];
				$data[$item['config_id']]['items'][] = [
					'game_id' => $item['game_id'],
				    'name' => $item['name']
				];
			}
		}
		return $data;
	}
}
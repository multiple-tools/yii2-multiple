<?php

	namespace umono\multiple\tools\page;

	use Yii;
	use yii\db\ActiveRecord;

	/**
	 * 用于自定义tableData 数据的基础数据格式化
	 */
	trait FormatTableData
	{
		/**
		 * @return mixed 格式化自定义列
		 */
		public function formatLabel()
		{
			$cache = Yii::$app->cache;
			$key   = static::class . '-attributeLabels';
			$data  = $cache->get($key);
			if ($data == false) {
				/**
				 * @var $this ActiveRecord
				 */
				$arr = $this->attributeLabels();
				if (count($arr) <= 10) {
					$width = '';
				} else {
					$width = 180;
				}
				unset($arr['is_del']);
				$list = [];
				foreach ($arr as $k => $v) {
					$sortValue = 'custom';
					if (in_array($k, $this->tableNoSortable)) {
						$sortValue = false;
					}
					if (in_array($k, $this->tableImageColumn)) {
						$list[] = [
							'prop'     => $k,
							'label'    => $v,
							'width'    => $width,
							'template' => 'image',
							'sort'     => $sortValue,
						];
						unset($sortValue);
						continue;
					}
					if (in_array($k, $this->tableTextColumn)) {
						$list[] = [
							'prop'     => $k,
							'label'    => $v,
							'width'    => $width,
							'template' => 'text',
							'sort'     => $sortValue,
						];
						unset($sortValue);
						continue;
					}
					$list[] = [
						'prop'  => $k,
						'label' => $v,
						'width' => $width,
						'sort'  => $sortValue,
					];
				}
				unset($arr);
				$cache->set($key, $list);

				return $list;
			}

			return $data;
		}

		// tableTitle 格式化图片 - 关联表数据 - 用作于绑定当前model中的图片
		public $tableImageColumn = [];
		// tableData 格式化数据 - 模板显示 text
		public $tableTextColumn = [];
		// 该数组显示不显示排序按钮
		public $tableNoSortable = [];

		/**
		 * 返回该表所选格式用户自定义表单头
		 * ```php
		 * // 返回以下格式
		 * {
		 *  'prop':'id',
		 *  'label':"ID",
		 *  'width':180,
		 *  'sort':'custom',
		 * }
		 *
		 * ```
		 * @param $attributes
		 * @param $select
		 * @return array
		 */
		public function tableTitle($attributes, $select = []): array
		{
			if (empty($select)) {
				$select = Yii::$app->request->get('select') ?? [];
			}
			$tableTitle = [];
			if (count($select) <= 6 && count($attributes) <= 6) {
				$width = '';
			} else {
				$width = 180;
			}
			if (!empty($select)) {
				foreach ($select as $value) {
					foreach ($attributes as $v) {
						if ($v['prop'] == $value) {
							$v['width']   = $width;
							$tableTitle[] = $v;
						}
					}
				}
			} else {
				foreach ($attributes as $v) {
					$v['width']   = $width;
					$tableTitle[] = $v;
				}
			}

			return $tableTitle;
		}
	}
<?php
	declare(strict_types=1);

	namespace umono\multiple\model;

	use umono\multiple\tools\page\FormatTableData;
	use yii\db\ActiveRecord;

	abstract class ActiveRecordModel extends ActiveRecord
	{
		use ModelHelper;

		use FormatTableData;

		public $renderListJoinTable = [];

		/**
		 * 格式化所有数据 提供导出时的数据 也可做为循环数据处理
		 *
		 * @param       $model
		 * @param bool  $info
		 * @param array $options
		 * @return mixed
		 */
		public static function transFormatData($model, bool $info = false, array $options = [])
		{
			return $model;
		}
	}
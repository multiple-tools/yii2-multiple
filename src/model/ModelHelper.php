<?php

	namespace umono\multiple\model;

	use yii\db\ActiveRecord;
	use yii\db\Exception;

	trait ModelHelper
	{

		/**
		 * @param ActiveRecord $model
		 * @return mixed|string
		 */
		public static function getModelError(ActiveRecord $model)
		{
			$errors = $model->getErrors();    //得到所有的错误信息
			if (!is_array($errors)) {
				return '';
			}
			$firstError = array_shift($errors);
			if (!is_array($firstError)) {
				return '';
			}
			return array_shift($firstError);
		}

		/**
		 * @throws Exception
		 */
		public function save($runValidation = true, $attributeNames = null): bool
		{
			$save = parent::save($runValidation, $attributeNames); // TODO: Change the autogenerated stub
			if (!$save) {
				self::msgModel($this);
			}
			return $save;
		}


		/**
		 * @throws Exception
		 */
		private static function msgModel($model): void
		{
			$msg = self::getModelError($model);
			throw new Exception($msg);
		}


		public static function getClass()
		{
			if (get_parent_class(get_called_class()) === ActiveRecordModel::class) {
				return get_called_class();
			}

			return get_parent_class(get_called_class());
		}

		/**
		 *  缓存查询model中ALL的数据
		 *
		 * @param array $where
		 * @param array $with
		 * @param array $options
		 *
		 * @return array|mixed|\yii\db\ActiveRecord[]
		 */
		public static function backCacheList(
			array $where = [],
            array $with = [],
            array $options = []
		)
		{
			$select     = $options['select'] ?? ['*'];
			$orderBy    = $options['orderBy'] ?? [];
			$orWhere    = $options['orWhere'] ?? [];
			$andWhere   = $options['andWhere'] ?? [];
			$modelClass = static::getClass();
			$model      = new $modelClass;
			$isDel      = array_key_exists('is_del', $model->attributes);
			$cache      = \Yii::$app->cache;
			$CACHE_KEY  = ($modelClass);
			$arr        = $cache->get($CACHE_KEY);
			if (is_array($where)) {
				ksort($where);
			}
			ksort($with);
			ksort($select);
			$key  = json_encode($where) . json_encode($orderBy) . json_encode($with) . json_encode($orWhere) . json_encode($andWhere) . json_encode($select);
			$data = [];
			// 有arr 没有 key的值时继续做缓存
			$arr = json_decode($arr, true);
			if (empty($arr[$key])) {
				$query = static::find()->select($select)->with($with)->where($where);
				foreach ($orWhere as $v) {
					$query->orWhere($v);
				}
				foreach ($andWhere as $v) {
					$query->andWhere($v);
				}
				if (!empty($isDel)) {
					$query->andWhere(['is_del' => 0]);
				}
				$res = $query->orderBy($orderBy)->asArray()->all();
				if (!empty($res)) {
					$data[$key] = $res;
					$arr[$key]  = $res;
					unset($res);
					$arr = json_encode($arr);
					$cache->set($CACHE_KEY, $arr, 10800);
				}
			} else {
				// 如果有的话就直接返回
				$data[$key] = $arr[$key];
			}
			unset($cache);
			unset($where);
			unset($arr);
			unset($cache);
			unset($query);
			unset($model);
			unset($modelClass);
			unset($options);

			return $data[$key] ?? [];
		}
	}
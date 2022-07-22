<?php
	declare(strict_types=1);

	namespace umono\multiple\model;


	use yii\helpers\Inflector;
	use yii\helpers\StringHelper;

	class RedisActiveRecord extends \yii\redis\ActiveRecord
	{
		// 防止部署多个项目时每个项目都使用同样的key值
		public static function keyPrefix()
		{
			return getenv('APP_URL') . Inflector::camel2id(StringHelper::basename(get_called_class()), '_');
		}

		public static function getDb()
		{
			return [];
		}

		public function setExpiration($timestamp)
		{
			if (empty($timestamp)) {
				$timestamp = 60 * 60 * 12;
			}
			$key   = $key = static::keyPrefix() . ':a:' . $this->primaryKey;
			$redis = self::getDb();
			$redis->expire($key, $timestamp);
		}

		public function load($data, $formName = null)
		{
			$scope = $formName === null ? $this->formName() : $formName;
			if ($scope === '' && !empty($data)) {
				if (is_array($data)) {
					$attributes = array_flip(false ? $this->safeAttributes() : $this->attributes());
					foreach ($data as $name => $value) {
						if (isset($attributes[$name])) {
							$this->$name = $value;
						} elseif (true) {
							$this->onUnsafeAttribute($name, $value);
						}
					}
				}
				return true;
			} elseif (isset($data[$scope])) {
				$this->setAttributes($data[$scope]);
				return true;
			}
		}
	}
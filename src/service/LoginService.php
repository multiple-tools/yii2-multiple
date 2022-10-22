<?php

	namespace umono\multiple\service;

	use umono\multiple\helpers\AesEncryptHelper;
	use yii\base\InvalidConfigException;

	class LoginService
	{
		public static $aesClass = AesEncryptHelper::class;


		public static function verbs()
		{
			return [
				'admin' => AesEncryptHelper::class
			];
		}

		// 验证token
		public static function verificationToken($token, $name): array
        {
			if (!empty($token)) {
				$tokens = static::decode($token, $name);
				if (empty($tokens)) {
					return [0, 0, 0];
				}
				$res    = explode(',', $tokens);
				$userId = $res[0] ?? 0;
				$time   = $res[2] ?? 0;
				if (empty($time) || empty($userId)) {
					return [$userId, 0, $time];
				}
				$userId = self::verificationTime($name, $userId, $time);
				if (empty($userId)) {
					return [0, 0, 0];
				}
				return [$userId, $res[1] ?? '', $time];
			} else {
				return [0, 0, 0];
			}
		}

        /**
         * @throws InvalidConfigException
         */
        private static function decode($token, $scope): string
        {
			$token = str_replace(' ', "+", $token);

			/**
			 * @var AesEncryptHelper $ascClass
			 */
			$ascClass = self::verbs()[$scope] ?? null;

			if (!$ascClass) {
				throw new InvalidConfigException('请配置对应的加密数据。');
			}

			if (method_exists($ascClass, 'des_decrypt')) {
				throw new InvalidConfigException('请继承\umono\multiple\helpers\AesEncryptHelper');
			}


			return $ascClass::aes_decrypt($token);
		}


		// 验证时间是否正确
		private static function verificationTime($name, $userId, $time)
		{
			$cache    = \Yii::$app->cache;
			$key      = $name . '_' . json_encode(['user_id' => $userId]);
			$old_time = $cache->get($key);

			// 请求的时间一定要大于过去记录的实际，且小于等于现在的解释时间
			if ($time > $old_time) {
				$cache->set($key, $time - 5000, 300);
				unset($cache);
				return $userId;
			}
			return 0;
		}
	}
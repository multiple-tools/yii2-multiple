<?php

	namespace umono\multiple\tools\page;

	/**
	 * SQL 查询分页数据记录
	 */
	class SqlPageCache
	{
		public static function writeSql($sql, $uid)
		{
			$cache = \Yii::$app->cache;
			$cache->set('SQL_CACHE_' . $uid, $sql, 3600 * 6);
			unset($cache);
		}

		public static function findOneByParam($uid)
		{
			$cache = \Yii::$app->cache;
			$res   = $cache->get('SQL_CACHE_' . $uid . '-PARAM');
			unset($cache);
			return $res;
		}

		public static function findOneBy($uid)
		{
			$cache = \Yii::$app->cache;
			$res   = $cache->get('SQL_CACHE_' . $uid);
			unset($redis);
			return $res;
		}
	}
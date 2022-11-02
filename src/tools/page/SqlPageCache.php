<?php

    namespace umono\multiple\tools\page;

    /**
     * SQL 查询分页数据记录
     */
    class SqlPageCache
    {
        public static function writeSql($sql, $uid)
        {
            \Yii::$app->getCache()->set('SQL_CACHE_' . $uid, $sql, 3600 * 6);
        }

        public static function finOneByModelClass($uid)
        {
            return \Yii::$app->getCache()->get('SQL_CACHE_' . $uid . '-MODEL');
        }

        public static function findOneByParam($uid)
        {
            return \Yii::$app->getCache()->get('SQL_CACHE_' . $uid . '-PARAM');
        }

        public static function findOneBy($uid)
        {
            return \Yii::$app->getCache()->get('SQL_CACHE_' . $uid);
        }
    }
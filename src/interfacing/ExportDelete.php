<?php

    namespace umono\multiple\interfacing;

    use umono\multiple\tools\page\SqlPageCache;

    abstract class ExportDelete
    {
        public $suffix = '';

        abstract public static function go($param);

        abstract protected static function can();

        public static function getModelClass($uid)
        {
            $modelClass = SqlPageCache::finOneByModelClass($uid);
            if (empty($modelClass)) {
                throw new \Exception("无效的模型缓存");
            }
            return $modelClass;
        }
    }
<?php
    declare(strict_types=1);

    namespace umono\multiple\model;

    use umono\multiple\tools\page\FormatTableData;
    use umono\multiple\tools\page\PageHandler;
    use yii\db\ActiveQuery;
    use yii\db\ActiveRecord;
    use yii\helpers\Inflector;
    use yii\helpers\StringHelper;
    use yii\web\ForbiddenHttpException;
    use yii\web\NotFoundHttpException;

    abstract class ActiveRecordModel extends ActiveRecord
    {
        use ModelHelper;

        use FormatTableData;

        public $renderListJoinTable = [];


        public static function camel2id(): string
        {
            $className = static::className();

            $modelStrName = explode('\\',$className);

            return Inflector::camel2id($modelStrName[count($modelStrName)-1]);
        }

        /**
         * 格式化所有数据 提供导出时的数据 也可做为循环数据处理
         *
         * @param       $item
         * @param bool  $info
         * @param array $options
         * @return mixed
         */
        public static function transFormatData($item, bool $info = false, array $options = [])
        {
            return $item;
        }

        public static function page(): PageHandler
        {
            $page             = new PageHandler();
            $page->query      = static::find();
            $page->table      = static::tableName();
            $className        = static::class;
            $page->modelClass = new $className;
            return $page;
        }

        /**
         * 普通消息
         *
         * @param $message
         *
         * @throws ForbiddenHttpException
         */
        public static function error($message)
        {
            throw new ForbiddenHttpException($message);
        }

        /**
         * @throws NotFoundHttpException
         */
        public static function error404($message)
        {
            throw new NotFoundHttpException($message);
        }

        public static function success($msg, $data = [])
        {
            return array_merge(['message' => $msg], ['data' => $data]);
        }
    }
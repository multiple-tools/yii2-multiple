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
        public function __construct($config = [])
        {
            parent::__construct($config);
            static::$modelInstance = $this;
        }

        use ModelHelper;

        use FormatTableData;

        public $renderListJoinTable = [];

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

        public static $modelInstance = null;

        public static function getModel()
        {
            if (static::$modelInstance == null) {
                $className = static::class;
                new $className;
            }
            return static::$modelInstance;
        }

        public static function page(): PageHandler
        {
            $page             = new PageHandler();
            $page->query      = static::find();
            $page->table      = static::tableName();
            $page->modelClass = static::getModel();
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
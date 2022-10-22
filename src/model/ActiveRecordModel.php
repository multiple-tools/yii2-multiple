<?php
    declare(strict_types=1);

    namespace umono\multiple\model;

    use umono\multiple\tools\page\FormatTableData;
    use umono\multiple\tools\page\PageHandler;
    use yii\db\ActiveRecord;
    use yii\web\ForbiddenHttpException;
    use yii\web\NotFoundHttpException;

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

        public static function toTaleDataArray($model, $whereParam, $withTable, $select): array
        {
            $page = new PageHandler();
            return $page->toTableData($model, $whereParam, $withTable, $select);
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
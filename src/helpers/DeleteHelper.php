<?php

    namespace umono\multiple\helpers;

    use umono\multiple\interfacing\ExportDelete;
    use umono\multiple\model\ActiveRecordModel;
    use yii\web\ForbiddenHttpException;

    class DeleteHelper extends ExportDelete
    {
        public $suffix = '';
        public static $deleteColumn = 'id';
        public static $bind = 'is_del';
        private static $attributes = ['is_del' => 1];

        public static function go($param)
        {
            $tr = \Yii::$app->db->beginTransaction();
            try {

                $modelClass = self::getModelClass($param['uid']);
                /**
                 * @var $model ActiveRecordModel
                 */
                $model = new $modelClass;
                $msg   = $param['deleteMsgModel']['msg'];
                if (empty($msg)) {
                    throw new ForbiddenHttpException('请输入删除原因！');
                }
                if (!array_key_exists(self::$bind, $model->attributes)) {
                    throw new ForbiddenHttpException('该项暂不支持删除功能');
                }
                if (!empty($param['list'])) {
                    $modelClass::updateAll(
                        self::$attributes,
                        [self::$deleteColumn => $param['list']]
                    );

                    static::callDeleteMsg($msg, $param['list'], $modelClass);
                }
                \Yii::$app->getCache()->flush();

                $tr->commit();

            } catch (\Exception $e) {
                $tr->rollBack();
                throw new ForbiddenHttpException($e->getMessage());
            }
        }

        protected static function can()
        {
        }

        public static function callDeleteMsg($msg, $ids, $modelClass){
            // 删除信息保存
        }
    }
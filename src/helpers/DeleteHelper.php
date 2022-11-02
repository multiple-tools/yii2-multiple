<?php

    namespace umono\multiple\helpers;

    use umono\multiple\interfacing\ExportDelete;
    use yii\web\ForbiddenHttpException;

    class DeleteHelper extends ExportDelete
    {
        public $suffix = '';
        public $deleteColumn = 'id';
        public $bind = 'is_del';
        private $attributes = ['is_del' => 1];

        public static function go($param)
        {
            $tr = \Yii::$app->db->beginTransaction();

            try {

                //$model = null;
                //$msg   = $param['deleteFormModel']['msg'];
                //if (empty($msg)) {
                //    throw new ForbiddenHttpException('请输入删除原因！');
                //}
                //$delParams = $param['delParams'] ?? '';
                //$param     = $param['list'];
                //$ids       = $this->formatIds($param);
                //
                //
                //if (!array_key_exists($this->bind, $model->attributes)) {
                //    throw new ForbiddenHttpException('该项暂不支持删除功能');
                //}
                //
                //$num = $model::updateAll(
                //    $this->attributes,
                //    [$this->deleteColumn => $ids]
                //);
                //
                //\Yii::$app->getCache()->flush();

                $tr->commit();

            } catch (\Exception $e) {
                $tr->rollBack();

            }
        }

        protected static function can()
        {
        }

        private function formatIds($param): array
        {
            $ids = [];
            if (is_array($param)) {
                foreach ($param as $v) {
                    $ids[] = $v[$this->deleteColumn] ?? '';
                }
            }

            return $ids;
        }
    }
<?php

    namespace umono\multiple\helpers;

    use umono\multiple\interfacing\ExportDelete;
    use umono\multiple\tools\page\Export;
    use yii\web\BadRequestHttpException;

    class ExportHelper extends ExportDelete
    {
        public $suffix = 'export-index-file';

        public static function exportSelect($param)
        {
            if (empty($param['selectRows'])) {
                throw new BadRequestHttpException("请选择导出的数据");
            }
            return self::go($param);
        }

        public static function exportAll($param)
        {
            return self::go($param);
        }

        public static function go($param)
        {
            self::can();

            $export = new Export(null, $param['fileName']??'');

            $header = self::handlerHeader($param['tableHeader']);
            $select = self::handlerSelect($param);
            $where  = [];
            if (!empty($param['selectRows'])) {
                $where = ['id' => $param['selectRows']];
            }
            $orderBy = [];

            $export->setTable($header, $select, $where, $orderBy);
            $export->setSql($param['uid']);
            return $export->go();
        }

        protected static function can()
        {
        }

        private static function handlerSelect($param): array
        {
            $select = $param['search']['select'] ?? [];
            if (empty($select)) {
                foreach ($param['tableHeader'] as $v) {
                    $select[] = $v['prop'];
                }
            }
            return $select;
        }

        private static function handlerHeader($param): array
        {
            $header = [];
            foreach ($param as $v) {
                $header[] = $v['label'];
            }
            return $header;
        }
    }
<?php

    namespace umono\multiple\tools\page;


    use umono\multiple\model\ActiveRecordModel;
    use Yii;
    use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
    use yii\db\ActiveQuery;
    use yii\db\ActiveRecord;
    use yii\web\BadRequestHttpException;
    use yii\web\Response;


    /**
     * 导出文件类
     * Class Export
     *
     * @package app\common\models\handleTable
     */
    class Export
    {
        /**
         * @var array 当前导出数据的中文字段
         */
        protected $tableHeader = [];
        /**
         * @var array 当前导出的数据的选择字段
         */
        protected $tableSelect = [];
        /**
         * @var array 表的条件
         */
        protected $tableWhere = [];
        /**
         * @var string 文件名称
         */
        public $fileName;
        private $path;
        private $sql;
        private $sqlParam = null;
        private $modelClass = null;

        public function getPath()
        {
            return $this->path;
        }

        public function __construct($path, $fileName)
        {

            if (!$path) {
                $path = \Yii::getAlias('@app/web') . '/excel/';
            }
            if (!file_exists($path)) {
                mkdir($path, 0777);
            }
            chmod($path, 0777);

            $this->path = $path;

            $this->fileName = ($fileName) . '[' . date('Y-m-d h:i:s') . '].xlsx';;
        }

        public function setSql($uid)
        {
            $modelClass = SqlPageCache::finOneByModelClass($uid);
            if (empty($modelClass)) {
                throw new \Exception("无效的模型缓存");
            }

            $sql = SqlPageCache::findOneBy($uid);
            if (empty($sql)) {
                throw new \Exception("该页面暂不支持导出");
            }
            $sqlParam = SqlPageCache::findOneByParam($uid);

            $this->sql        = $sql;
            $this->sqlParam   = $sqlParam;
            $this->modelClass = $modelClass;
        }

        /**
         * ```php
         * $header = ['序号','名称'],
         * $selectColumn = ['id','name'],
         * $where = ['id'=>123]
         * ```
         *
         * @param array $header       ['id','名称']
         * @param array $selectColumn ['id','name']
         * @param array $where        ['id'=>1]
         * @return void
         */
        public function setTable(array $header, array $selectColumn = [], array $where = [])
        {
            $this->tableSelect = $selectColumn;
            $this->tableHeader = $header;
            $this->tableWhere  = $where;
        }

        public function go()
        {
            $model = $this->modelClass;
            $data  = [];
            /**
             * @var ActiveQuery $m
             */
            $m = unserialize($this->sql);
            //var_dump($m->createCommand()->getRawSql());die;
            if (!empty($this->tableWhere)) {
                $m->andWhere($this->tableWhere);
            }
            $res = $m->asArray()->all();
            unset($m);
            $res = array_chunk($res, 500);

            $sqlParam = [];
            if (!empty($this->sqlParam)) {
                $sqlParam = $this->sqlParam;
            }
            foreach ($res as $v) {
                foreach ($v as $value) {
                    $format = $model::transFormatData($value, false, $sqlParam);
                    if ($format !== false) {
                        $value = $format;
                    }
                    // 所有数据根据selectArr来导出 如果select Arr 为空则所有列都导出
                    $columnData = [];
                    foreach ($this->tableSelect as $_v) {
                        $columnData[$_v] = $value[$_v] ?? '';
                    }
                    $data[] = $columnData;
                    unset($format);
                    unset($columnData);
                }
            }
            unset($res);
            unset($model);
            unset($sqlParam);

            return $this->exportData($data);
        }

        private function exportData($data)
        {
            $path      = $this->path;
            $headerRow = $this->tableHeader;

            $fileName = $this->fileName;

            $this->save($headerRow, $data, $path . $fileName);

            return $this->sendDownload($fileName, $path);
        }

        private function save($header, $data, $path)
        {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($path);

            $headerRow = WriterEntityFactory::createRowFromArray($header);
            $writer->addRow($headerRow);


            foreach ($data as $v) {
                $writer->addRow(WriterEntityFactory::createRowFromArray($v));
            }

            $writer->close();
        }

        public function ExportZip($param, $fileColumnsName, $fileNewName = '')
        {
            $zip = new \ZipArchive();

            $path = $this->path;

            $fileName = ($this->fileName ?? time()) . '.zip';

            if ($zip->open($path . '/download/' . $fileName, \ZipArchive::CREATE) === true) {

                foreach ($param as $v) {
                    if (file_exists($path . $v[$fileColumnsName])) {
                        if (empty($fileNewName)) {
                            $zip->addFile($path . $v[$fileColumnsName], basename($v[$fileColumnsName]));
                        } else {
                            $zip->addFile($path . $v[$fileColumnsName], $v[$fileNewName] . '.png');
                        }
                    }
                }

                $zip->close();

                if (!file_exists($path . '/download/' . $fileName)) {
                    throw new BadRequestHttpException('所选数据当前为空:(');
                }

                return $this->sendDownload($fileName, \Yii::getAlias('@app/web') . '/download/');

            }
        }

        private function sendDownload($fileName, $path, $option = [])
        {
            \Yii::$app->getResponse()->getHeaders()->set('X-Suggested-Filename', rawurlencode($fileName));

            $file = $path . $fileName;

            \Yii::$app->getResponse()->on(
                Response::EVENT_AFTER_SEND, function () use ($file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            });
            return \Yii::$app->getResponse()->sendFile($file, $fileName, $option);
        }
    }
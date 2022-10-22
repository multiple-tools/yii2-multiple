<?php

    namespace umono\multiple\helpers;

    use PhpOffice\PhpSpreadsheet\IOFactory;
    use yii\base\Model;
    use yii\web\ForbiddenHttpException;
    use yii\web\UploadedFile;

    /**
     * Class ImportHelper
     *
     * @property string $key
     * @package app\common\helpers
     */
    class ImportHelper extends Model
    {
        public $key;
        public $count;

        /**
         * @throws ForbiddenHttpException
         */
        public function __construct($config = [])
        {
            parent::__construct($config);
            $key = $_ENV['APP_URL'] . '/' . \Yii::$app->request->pathInfo;
            $key = md5($key);
            if (\Yii::$app->cache->get($key) !== false) {
                throw new ForbiddenHttpException('当前有人正在进行导入！');
            }
            $this->key = $key;
        }

        //只能一个人导入，其他人判断是否存在key
        public function iniData($count, $param = [])
        {
            if (empty($param)) {
                $param = \Yii::$app->request->getBodyParams();
            }
            $this->count = $count;
            \Yii::$app->cache->set($this->key . '-user', $param['code']);
            \Yii::$app->cache->set($this->key . '-count', $count);
        }

        public function setValue($value)
        {
            \Yii::$app->cache->set($this->key, $value, 5);
            \Yii::$app->cache->set($this->key . '-count', $this->count, 5);
        }

        public function clear()
        {
            \Yii::$app->cache->delete($this->key . '-count');
            \Yii::$app->cache->delete($this->key);
        }

        /**
         * 处理文件进行读取，默认去除第1、2行数据
         *
         * @throws ForbiddenHttpException
         */
        public static function excelRead(
            $unsetColumn = [
                1,
                2,
            ]
        ) {
            $file = UploadedFile::getInstanceByName('file');
            if (!empty($file)) {
                $path = dirname(\Yii::getAlias("@app")) . "/public/uploads/admins/excel/";
                if (!is_dir($path)) {
                    @mkdir($path, 0777, true);
                    @chmod($path, 0777);
                }
                $name = md5($file->name . time()) . '.' . $file->extension;

                if (($file->extension != 'xls') && ($file->extension != 'xlsx')) {
                    throw new ForbiddenHttpException('文件格式错误！仅支持xls、xlsx');
                }

                $fileName = $path . $name;

                try {
                    if ($file->saveAs($fileName)) {
                        unset($file);

                        $inputFileType = IOFactory::identify($fileName);

                        $reader = IOFactory::createReader($inputFileType);

                        $spreadsheet = $reader->load($fileName);
                        unset($reader);


                        $sheetData = $spreadsheet->getActiveSheet()
                            ->toArray(null, true, true, true);
                        unset($spreadsheet);

                        foreach ($unsetColumn as $v) {
                            unset($sheetData[$v]);
                        }

                        $sheetData = array_values($sheetData);


                        if (file_exists($fileName)) {
                            unlink($fileName);
                        }

                        unset($inputFileType);
                        return $sheetData;
                    } else {
                        return [];
                    }
                } catch (\Exception $e) {
                    if (file_exists($fileName)) {
                        unlink($fileName);
                    }
                    throw new ForbiddenHttpException($e->getTraceAsString());
                }
            } else {
                throw new ForbiddenHttpException('未收到文件');
            }
        }
    }
<?php

	namespace umono\multiple\controllers;

	use yii\console\Controller;

	class ConsoleController extends Controller
	{
        /**
         * 初始化资源数据
         * @return void
         */
        public function actionIndex()
        {
            $assets = \Yii::getAlias("@base") . "/vendor/bower-asset/";
            @mkdir($assets, 0777, true);
            $copyDir = [
                \Yii::getAlias("@base") . "/vendor/swagger-api/swagger-ui"         => "swagger-ui",
                \Yii::getAlias("@base") . "/vendor/twbs/bootstrap"                 => "bootstrap",
                \Yii::getAlias("@base") . "/extend/yii2-multiple/assets/jquery"    => "jquery",
                \Yii::getAlias("@base") . "/extend/yii2-multiple/assets/yii2-pjax" => "yii2-pjax",
            ];
            foreach ($copyDir as $k => $v) {
                @mkdir($assets . $v, 0777, true);
                $this->recurse_copy($k, $assets . $v);
            }

            @mkdir(\Yii::getAlias("@base") . '/runtime', 0777, true);
            echo "init over.\n";
        }

        public function recurse_copy($src, $dst)
        {
            $dir = opendir($src);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        @mkdir($dst . '/' . $file, 0777, true);
                        $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        @copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
        }
	}
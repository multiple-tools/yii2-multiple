<?php
    declare(strict_types=1);

    namespace umono\multiple\model;


    use InvalidArgumentException;
    use yii\base\BaseObject;
    use yii\helpers\ArrayHelper;
    use yii\web\View;
    use yii\helpers\Json;
    use yii\web\ForbiddenHttpException;

    class ManifestAssetBundle extends BaseObject
    {
        public $manifestFile = 'manifest.json';
        public $assetPath = DIRECTORY_SEPARATOR . 'statics' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        public $js = [];
        public $css = [];
        public $etag = '';

        // 引入前端资源
        public static function register(View $view)
        {
            /* @var $bundle ManifestAssetBundle */
            $bundle = \Yii::createObject(get_called_class());

            $responseHeaders = \Yii::$app->getResponse()->getHeaders();

            $responseHeaders->set('ETag', $bundle->etag);

            foreach ($bundle->js as $js) {
                $view->registerJsFile($bundle->assetPath . $js,['type'=>'module','crossorigin'=>true]);
            }

            foreach ($bundle->css as $css) {
                $view->registerCssFile($bundle->assetPath . $css);
            }
        }

        public function init()
        {

            parent::init();

            $manifestPath = \Yii::getAlias("@app/web") . $this->assetPath;

            $manifestFile = $manifestPath . $this->manifestFile;

            if (!file_exists($manifestFile)) {
                throw new ForbiddenHttpException('manifest.json is not exist.');
            }

            $this->parseManifestFile($manifestFile);
        }

        protected function parseManifestFile($manifestFile)
        {
            $text = file_get_contents($manifestFile);

            $this->etag = md5($text);

            $json = Json::decode($text);

            foreach ($json as $file => $url) {
                if ($file =='index.html') {
                    if ($url['file']) {
                        $this->js[] = $url['file'];
                    }
                    if (isset($url['css']) && !empty($url['css'])) {
                        $this->css = ArrayHelper::merge($this->css, $url['css']);
                    }
                }
            }
        }
    }
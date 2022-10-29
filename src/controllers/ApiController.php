<?php

    namespace umono\multiple\controllers;

    use umono\multiple\tools\page\PageHandler;
    use Yii;
    use yii\web\UnauthorizedHttpException;

    class ApiController extends ApiBaseController
    {
        use Helper;

        public $isBenDi = false;
        public $param;
        public $user_id;
        public $user;
        public $get;

        /**
         * @throws UnauthorizedHttpException
         */
        public function discard()
        {
            throw new UnauthorizedHttpException("此方法已弃用！");
        }

        /**
         * 处理get传递的参数
         * @return array|mixed
         */
        public function handlerGetParam()
        {
            $get = Yii::$app->request->get();

            foreach ($get as $k => $v) {
                if (is_string($v)) {
                    $get[$k] = htmlspecialchars($v);
                }
                if (empty($v) && ($v != 0 || $v != '0')) {
                    unset($get[$k]);
                }
            }

            return $get;
        }
    }
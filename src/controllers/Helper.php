<?php

    namespace umono\multiple\controllers;

    use yii;
    use yii\web\ForbiddenHttpException;
    use yii\web\NotFoundHttpException;

    trait Helper
    {
        public function test($data, $msg = '测试数据中...'): array
        {
            Yii::$app->response->statusCode = 400;
            return ['message' => $msg, 'data' => $data];
        }

        public function success($msg, $data = [])
        {
            return array_merge(['message' => $msg], ['data' => $data]);
        }

        /**
         * @throws ForbiddenHttpException
         */
        public function error($msg)
        {
            throw new ForbiddenHttpException($msg);
        }

        /**
         * @throws NotFoundHttpException
         */
        public function notFound(string $msg = '404')
        {
            throw new NotFoundHttpException($msg);
        }


        // 是否是post

        /**
         * @throws NotFoundHttpException
         */
        public function isPost()
        {
            if (!Yii::$app->request->isPost) {
                throw new NotFoundHttpException('Invalid verification method');
            }
        }

        /**
         * @throws NotFoundHttpException
         */
        public function isPut()
        {
            if (!Yii::$app->request->isPut) {
                throw new NotFoundHttpException('Invalid verification method');
            }
        }

        /**
         * @throws NotFoundHttpException
         */
        public function isDelete()
        {
            if (!Yii::$app->request->isDelete) {
                throw new NotFoundHttpException('Invalid verification method');
            }
        }

        /**
         * @throws NotFoundHttpException
         */
        public function isGet()
        {
            if (!Yii::$app->request->isGet) {
                throw new NotFoundHttpException('Invalid verification method');
            }
        }
    }
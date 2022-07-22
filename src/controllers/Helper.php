<?php

	namespace umono\multiple\controllers;

	use yii;
	use yii\web\ForbiddenHttpException;
	use yii\web\NotFoundHttpException;

	trait Helper
	{
		public function backTest($data, $msg = '测试数据中...')
		{
			Yii::$app->response->statusCode = 400;
			return ['message' => $msg, 'data' => $data];
		}

		public function success($msg, $data = [])
		{
			return array_merge(['message' => $msg], ['data' => $data]);
		}

		public function error($msg)
		{
			throw new ForbiddenHttpException($msg);
		}

		public function notFound(string $msg = '数据不存在')
		{
			throw new NotFoundHttpException($msg);
		}


		// 是否是post
		public function postGo()
		{
			if (!Yii::$app->request->isPost) {
				throw new NotFoundHttpException('Invalid verification method');
			}
		}

		public function putGo()
		{
			if (!Yii::$app->request->isPut) {
				throw new NotFoundHttpException('Invalid verification method');
			}
		}

		public function deleteGo()
		{
			if (!Yii::$app->request->isDelete) {
				throw new NotFoundHttpException('Invalid verification method');
			}
		}

		public function getGo()
		{
			if (!Yii::$app->request->isGet) {
				throw new NotFoundHttpException('Invalid verification method');
			}
		}
	}
<?php

	namespace umono\multiple\controllers;

	use yii\filters\ContentNegotiator;
	use yii\filters\Cors;
	use yii\filters\RateLimiter;
	use yii\filters\VerbFilter;
	use yii\web\Response;

	abstract class ApiBaseController extends \yii\web\Controller
	{

		public $enableCsrfValidation = false;

		/**
		 * {@inheritdoc}
		 */
		public function behaviors()
		{
			return [
				'contentNegotiator' => [
					'class'   => ContentNegotiator::class,
					'formats' => [
						'application/json' => Response::FORMAT_JSON,
					],
				],
				'corsFilter'        => [
					'class' => Cors::class,
				],
				'verbFilter'        => [
					'class'   => VerbFilter::class,
					'actions' => $this->verbs(),
				],
				'rateLimiter'       => [
					'class' => RateLimiter::class,
				],
			];
		}

		public function afterAction($action, $result)
		{
			$result = parent::afterAction($action, $result);

			if (is_array($result)) {
				if (isset($result['data'])) {
					$data = $result;
				} else {
					$data['data'] = $result;
				}
			} else {
				if (isset($result->data)) {
					$data = ['data' => $result->data, 'count' => $result->count??0];
				} else {
					$data['data'] = $result;
				}
			}
			return $data;
		}

		protected function verbs()
		{
			return [];
		}


		public function getIdentity()
		{
			return \Yii::$app->getUser()->getIdentity();
		}
	}
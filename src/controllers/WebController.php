<?php

	namespace umono\multiple\controllers;

	use Yii;
	use yii\helpers\Url;
	use yii\web\Controller;

	abstract class WebController extends Controller
	{
		/**
		 * @inheritdoc
		 */
		public function render($view, $params = []): string
		{
			return parent::render($this->changeTemplatesViewPath($view), $params);
		}

		private function changeTemplatesViewPath($view)
		{
			if ((strncmp($view, '/', 1) !== 0 || strncmp($view, '@', 1) !== 0)) {
				$view = '/templates/' . $this->id . '/' . $view;
			}
			return $view;
		}

		/**
		 * @inheritdoc
		 */
		public function renderPartial($view, $params = []): string
		{
			return parent::renderPartial($this->changeTemplatesViewPath($view), $params);
		}

		public function getBackUrl($default = ['site/index']): ?string
        {
			$backUrl = Yii::$app->getRequest()->getReferrer();

			if (empty($backUrl)) {
				$backUrl = Url::toRoute($default);
			}

			return $backUrl;
		}
	}
<?php

	namespace umono\multiple\modules;

	use yii\base\Module;
	use yii\console\Application as ConsoleApplication;
	use yii\helpers\Url;
	use yii\web\Application as WebApplication;
	use yii\web\GroupUrlRule;

	abstract class AbstractModule extends Module
	{
		public $disableDebugModule = false;

		abstract public static function getUserComponent();

		/**
		 * @return array|null
		 */
		public static function getUrlRules(): ?array
        {
			return [
				'class'       => GroupUrlRule::class,
				'prefix'      => static::getUrlPrefix(),
				'routePrefix' => static::getRoutePrefix(),
				'rules'       => static::getRouteRules(),
			];
		}

		public static function getUrlPrefix()
		{
			return static::getModuleId();
		}

		public static function getModuleId()
		{
			return null;
		}

		public static function getRoutePrefix()
		{
			return static::getModuleId();
		}

		public static function getRouteRules(): array
        {
			return [
				''                      => 'site/index',
				'<controller>'          => '<controller>/index',
				'<controller>/<action>' => '<controller>/<action>',
			];
		}

		public static function toRoute($route, $scheme = false): string
		{
			if (is_array($route)) {
				$route[0] = '/' . static::getUrlPrefix() . '/' . $route[0];
			} else {
				$route = '/' . static::getUrlPrefix() . '/' . $route;
			}
			return Url::toRoute($route, $scheme);
		}

		public function init()
		{
			parent::init();

			$this->layout = 'main';

			if (\Yii::$app instanceof WebApplication) {
				\Yii::$app->getErrorHandler()->errorAction = static::getRoutePrefix() . '/site/error';
			}

			if (\Yii::$app instanceof ConsoleApplication) {
				$this->controllerNamespace = (new \ReflectionClass(
						get_called_class()))->getNamespaceName() . '\\commands';
			}

			if ($this->disableDebugModule) {
				/* @var $debug \yii\debug\Module|null */
				$debug = \Yii::$app->getModule('debug');

				if ($debug) {
					$debug->allowedIPs = [];
				}
			}
		}
	}

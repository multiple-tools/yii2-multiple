<?php

	namespace umono\multiple\model;

	use yii\base\BootstrapInterface;
	use yii\base\Application;


	abstract class AbstractModuleBootstrap implements BootstrapInterface
	{
		/**
		 * @var $moduleClass AbstractModule
		 */
		protected $moduleClass;

		/**
		 * @param $app Application
		 * @return void
		 * @throws \yii\base\InvalidConfigException
		 */
		public function bootstrap($app)
		{
			$this->moduleClass = (new \ReflectionClass(get_called_class()))->getNamespaceName() . '\Module';

			$moduleUrlRules = $this->moduleClass::getUrlRules();

			if ($moduleUrlRules) {
				$app->getUrlManager()->addRules([$moduleUrlRules], true);
			}


			if ($app instanceof \yii\web\Application) {
				$userComponent = $this->moduleClass::getUserComponent();

				if ($userComponent != null) {
					$app->set('user', $userComponent);
				}
			}
		}
	}
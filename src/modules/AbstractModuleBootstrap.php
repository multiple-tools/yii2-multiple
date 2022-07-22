<?php

	namespace umono\multiple\modules;

	use app\modules\admin\spa\Module;
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
			// $path = \Yii::getAlias("@app") . DIRECTORY_SEPARATOR . 'modules';
			// $a    = scandir($path);
			// var_dump($a);
			// die;

			$this->moduleClass = (new \ReflectionClass(get_called_class()))->getNamespaceName() . '\Module';

			$moduleUrlRules = $this->moduleClass::getUrlRules();
			// if ($this->moduleClass == Module::class) {
			// var_dump($moduleUrlRules);die;
			// }

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

		public static function verbs()
		{
			return [
				'admin' => 'admin',
				'api'   => 'i/',
			];
		}
	}
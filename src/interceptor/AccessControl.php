<?php

	namespace umono\multiple\interceptor;

	use app\modules\backend\api\models\admin\Admin;
    use yii\base\ActionFilter;
	use yii\di\Instance;
	use yii\web\ForbiddenHttpException;
    use yii\web\IdentityInterface;
    use yii\web\User;

	class AccessControl extends ActionFilter
	{
		/* @var $user User|array|string|bool */
		public $user = 'user';

		public function init()
		{
			parent::init();

			if ($this->user !== false) {
				$this->user = Instance::ensure($this->user, User::class);
			}
		}

        /**
         * @throws \Throwable
         * @throws ForbiddenHttpException
         */
        public function beforeAction($action): bool
        {
			$actionId = $this->getActionId($action);

			$identity = $this->user->getIdentity();

			if (!$identity->can($actionId)) {
				throw new ForbiddenHttpException('您没有权限进行此操作');
			}

			return true;
		}

		protected function getActionId($action): string
        {
			return $action->controller->id . '/' . $action->id;
		}
	}
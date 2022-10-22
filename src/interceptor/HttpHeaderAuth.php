<?php

	namespace umono\multiple\interceptor;

	use umono\multiple\service\LoginService;
	use yii\filters\auth\AuthMethod;
	use yii\web\UnauthorizedHttpException;

	class HttpHeaderAuth extends AuthMethod
	{
		public $header = 'X-Access-Token';

		public $clientId = '';

		protected $scope = "admin";


		/**
		 * @param $user
		 * @param $request
		 * @param $response
		 * @return \yii\web\IdentityInterface|null
		 * @throws UnauthorizedHttpException
		 */
		public function authenticate($user, $request, $response): ?\yii\web\IdentityInterface
        {
			$authHeader = $request->getHeaders()->get($this->header);

			if ($authHeader !== null) {
				// 验证token时差问题
				$res         = LoginService::verificationToken($authHeader, $this->scope);
				$accessToken = $res[1] ?? '';
				$identity    = $user->loginByAccessToken($accessToken, $this->clientId);

				$request->getHeaders()->set('name', $identity['name'] ?? '');
				if ($identity === null) {
					$this->challenge($response);
					$this->handleFailure($response);
				}

				return $identity;
			}

			return null;
		}

		public function handleFailure($response)
		{
			throw new UnauthorizedHttpException('必须登陆才能进行操作');
		}

		protected function getActionId($action): string
        {
			return $action->controller->id . '/' . $action->id;
		}
	}
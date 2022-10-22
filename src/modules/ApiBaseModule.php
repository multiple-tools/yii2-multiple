<?php

	namespace umono\multiple\modules;

    use umono\multiple\service\RequestService;
	use yii\web\Application;
	use yii\web\Response;
    use yii\web\User;

    class ApiBaseModule extends AbstractModule
	{

		public static function getUserComponent(): array
        {
			return [
                'class'           => User::class,
                'identityClass'   => User::class,
                'enableAutoLogin' => false,
                'enableSession'   => false,
                'loginUrl'        => null,
            ];
		}

		public $startTime;

		public function init()
		{
			parent::init();

			$this->startTime = microtime(true);

			if (\Yii::$app instanceof Application) {

				\Yii::$app->set(
					'request', [
					'class'                  => RequestService::class,
					'enableCsrfValidation'   => false,
					'enableCsrfCookie'       => false,
					'enableCookieValidation' => false,
					'parsers'                => [
						'application/json' => 'yii\web\JsonParser',
					],
				]);

				\Yii::$app->set(
					'response', [
					'class'         => Response::class,
					'format'        => Response::FORMAT_JSON,
					'charset'       => 'UTF-8',
					'on beforeSend' => function ($event) {
						$response = $event->sender;
						$end_time = microtime(true);
						$time     = round(($end_time - $this->startTime), 9);
						if ($response->isSuccessful) {
							$response->data =
								[
									'msg'     => ($response->data['message']) ?? 'OK',
									'data'    => $response->data['data'] ?? null,
									'code'    => $response->statusCode,
									'runtime' => $time,
								];
						} else {
							$response->data = [
								'msg'     => ($response->data['message']) ?? 'OK',
								'data'    => $response->data,
								'code'    => $response->statusCode,
								'runtime' => $time,
							];
						}
						$response->statusCode = 200;
					},
				]);
			}
		}
	}
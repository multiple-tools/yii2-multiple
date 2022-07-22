<?php

	namespace umono\multiple\helpers;


	use umono\multiple\kernel\ServiceContainer;

	/**
	 * @property StringHelper     $stringHelper
	 * @property ImageHelper      $imageHelper
	 * @property AesEncryptHelper $aesEncryptHelper
	 * @property UploadHelper     $uploadHelper
	 *
	 *
	 */
	class GeneralHelper extends ServiceContainer
	{
		protected $providers = [

		];


		public function __call($name, $arguments)
		{
			return call_user_func_array([$this['base'], $name], $arguments);
		}
	}
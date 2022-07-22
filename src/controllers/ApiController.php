<?php

	namespace umono\multiple\controllers;

	use umono\multiple\tools\page\PageHandler;
	use Yii;

	class ApiController extends ApiBaseController
	{
		use Helper;

		public $isBenDi = false;
		public $param;
		public $user_id;
		public $user;
		public $get;

		/**
		 * @var PageHandler $page
		 */
		public $page;

	}
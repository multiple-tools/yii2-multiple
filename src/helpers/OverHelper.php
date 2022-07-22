<?php

	namespace umono\multiple\helpers;


	class OverHelper
	{
		public static function veryLong()
		{
			set_time_limit(0);
			ini_set('memory_limit', -1);
		}
	}
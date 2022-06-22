<?php

	namespace umono\multiple\helpers;

	class AesEncryptHelper
	{
		//16位
		public $AES_KEY = "3t12and4o92fLY20";
		public $AES_IV = "30ut029d41oAf1YL";

		/**
		 * 解密
		 *
		 * @param $str
		 * @return string
		 */
		public static function aes_decrypt($str)
		{
			$class     = get_called_class();
			$enc       = new $class;
			$decrypted = openssl_decrypt(
				base64_decode($str), 'aes-128-cbc', $enc->AES_KEY, OPENSSL_RAW_DATA, $enc->AES_IV);

			return $decrypted;
		}

		/**
		 * 加密
		 *
		 * @param $plain_text
		 * @return string
		 */
		public static function aes_encrypt($plain_text)
		{

			$class          = get_called_class();
			$enc            = new $class;
			$encrypted_data = openssl_encrypt(
				$plain_text, 'aes-128-cbc', $enc->AES_KEY, OPENSSL_RAW_DATA, $enc->AES_IV);

			return base64_encode($encrypted_data);
		}
	}
<?php

    namespace umono\multiple\helpers;

    class AesEncryptHelper
    {
        //16位
        public $AES_KEY = "yt12an145920LYyt";
        public $AES_IV = "2y99029141NA21Yt";

        /**
         * 解密
         *
         * @param $str
         * @return string
         */
        public static function aes_decrypt($str): string
        {
            $class = get_called_class();
            $enc   = new $class;
            return openssl_decrypt(
                base64_decode($str), 'aes-128-cbc', $enc->AES_KEY, OPENSSL_RAW_DATA, $enc->AES_IV);
        }

        /**
         * 加密
         *
         * @param $plain_text
         * @return string
         */
        public static function aes_encrypt($plain_text): string
        {

            $class          = get_called_class();
            $enc            = new $class;
            $encrypted_data = openssl_encrypt(
                $plain_text, 'aes-128-cbc', $enc->AES_KEY, OPENSSL_RAW_DATA, $enc->AES_IV);

            return base64_encode($encrypted_data);
        }
    }
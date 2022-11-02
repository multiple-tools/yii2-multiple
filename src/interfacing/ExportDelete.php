<?php

    namespace umono\multiple\interfacing;

    abstract class ExportDelete
    {
        public $suffix = '';

        abstract public static function go($param);

        abstract protected static function can();
    }
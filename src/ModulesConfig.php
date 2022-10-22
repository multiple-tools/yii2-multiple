<?php

    namespace umono\multiple;

    abstract class ModulesConfig
    {
        abstract public static function bootstrap(): array;

        abstract public static function modules(): array;
    }
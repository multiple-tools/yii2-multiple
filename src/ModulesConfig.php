<?php

    namespace umono\multiple;

    /**
     * 需要注意：url的匹配规则上要会被模块的优先级会被替换
     */
    abstract class ModulesConfig
    {
        abstract public static function bootstrap(): array;

        abstract public static function modules(): array;
    }
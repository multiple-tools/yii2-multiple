<?php

    namespace umono\multiple;

    class ModulesConfig
    {
        public static function bootstrap(): array
        {
            return [
                'log',
                \app\modules\api\base\ModuleBootstrap::class,
                \app\modules\website\ModuleBootstrap::class,
            ];
        }

        public static function modules(): array
        {
            return [
                \app\modules\api\base\Module::getModuleId() => \app\modules\api\base\Module::class,
                \app\modules\website\Module::getModuleId()  => \app\modules\website\Module::class,
            ];
        }
    }
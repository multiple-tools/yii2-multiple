<?php
	namespace umono\multiple\helpers;

    use yii\db\Migration;
    use yii\web\ForbiddenHttpException;

    /**
     * Class TableHelpers
     * @method table($param)
     * @method indexColumns($param)
     * @method addColumns($param)
     * @method isCreateIndex($boolean)
     * @method isAddColumn($boolean)
     * @method batchAddColumn($boolean)
     * @method isAlterColumn($boolean)
     *
     * @package umono\multiple\helpers
     */
    class TableHelpers extends Migration
    {
        protected $table;
        protected $indexColumns;
        protected $addColumns;
        protected $isCreateIndex = false;
        protected $isAddColumn = false;
        protected $batchAddColumn = false;
        protected $isAlterColumn = false;

        public function __call($name, $params)
        {
            $this->$name = $params[0];
        }

        /**
         * @throws ForbiddenHttpException
         */
        public function setCreateIndex(array $columns)
        {
            $table = $this->table;
            foreach ($columns as $column) {
                if (!array_key_exists($column, $table->attributes)) {
                    throw new ForbiddenHttpException($column . '不在' . $this->table . '中');
                }
            }
            $this->indexColumns = $columns;
        }

        public function safeUp()
        {
            if ($this->isCreateIndex) {
                echo "正在创建索引...\n";
                $columns = $this->indexColumns;
                foreach ($columns as $column) {
                    $this->createIndex($column, $this->table, $column);
                }
                echo "索引创建完成\n";
            }

            if ($this->isAlterColumn) {
                echo "正在修改列表...\n";
            }

            if ($this->batchAddColumn) {
                $columns = $this->addColumns;
                echo "正在添加列...\n";
                foreach ($columns as $column) {
                    $this->addColumn($column[0], $column[1], $column[2]);
                }
                echo "添加列完成!\n";
            }


            if ($this->isAddColumn) {
                $columns = $this->addColumns;
                echo "正在添加列...\n";
                $table = $this->table;
                foreach ($columns as $column) {
                    $this->addColumn($table, $column[0], $column[1]);
                }
                echo "添加列完成!\n";
            }
        }

        public function safeDown()
        {
            if ($this->isCreateIndex) {
                $columns = $this->indexColumns;
                foreach ($columns as $column) {
                    $this->dropIndex($column, $this->table);
                }
            }
        }
    }
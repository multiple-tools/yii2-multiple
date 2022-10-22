<?php

    namespace umono\multiple\tools\page;

    use umono\multiple\helpers\StringHelper;
    use umono\multiple\model\ActiveRecordModel;
    use yii\data\Pagination;
    use yii\db\ActiveQuery;

    class PageHandler extends Pagination
    {
        protected $get;
        protected $param;
        protected $limit = 20;
        protected $page = 1;
        protected $offset;
        public $select = ['*'];
        public $orderBy = [];
        public $hiddenColumnWhere = ['is_del' => 0];
        public $orWhere = []; // 额外的条件
        public $andWhere = [];// 额外的条件
        // SQL CACHE PARAM
        public $sqlCacheParam = [];
        /**
         * @var $query ActiveQuery
         */
        public $query;
        // 当前查询的表名
        protected $table;
        protected $columns;

        /**
         * 返回表的列名
         *
         * @param  $model ActiveRecordModel
         * @return array
         */
        public function getColumn(ActiveRecordModel $model): array
        {
            $table         = $model::tableName();
            $this->table   = $table;
            $tableSchema   = \Yii::$app->db->schema->getTableSchema($table);
            $this->columns = $tableSchema->columnNames;
            unset($tableSchema);
            return $this->columns;
        }

        /**
         * 将传递的条件进行分页筛选
         *
         * @param array $where
         * @return void
         */
        private function formatParamHandler(array $where)
        {
            if (empty($where)) {
                $this->param = $this->get = \Yii::$app->request->get();
            } else {
                $this->param = $where;
            }

            $limit  = $this->param['limit'] ?? $this->limit;
            $page   = $this->param['page'] ?? $this->page;
            $offset = $limit * ($page - 1);

            $this->offset = $offset;
            $this->limit  = $limit;
            $this->page   = $page;
            unset($where);
        }

        /**
         * 重设limit page的值
         *
         * @param $limit
         * @param $page
         * @return void
         */
        public function setPageLimit($limit, $page)
        {
            if (!empty($limit)) {
                $this->limit = $limit;
            }
            if (!empty($page)) {
                $this->page = $page;
            }
        }

        /**
         * 分页数据基础条件
         *
         * @param       $model ActiveRecordModel
         * @param array $where
         * @param array $withTable
         * @return $this
         */
        public function getTablesParam(ActiveRecordModel $model, array $where = [], array $withTable = []): PageHandler
        {

            $this->formatParamHandler($where);


            $column = $this->getColumn($model);

            // 查询选择列字段
            $select = $this->select;
            foreach ($select as $k => &$v) {
                if (in_array($v, $column)) {
                    $v = $this->table . '.' . $v;
                } else {
                    unset($select[$k]);
                }
                unset($v);
            }
            // 合并默认与添加的with及去重
            $this->query = $model::find()->with($withTable)
                ->select($select);
            unset($select);
            unset($table);

            if (!empty($this->orWhere)) {
                $this->_orWhere($this->orWhere);
            }

            if (!empty($this->andWhere)) {
                $this->_andWhere($this->andWhere);
            }

            foreach ($where as $k => $v) {
                if (in_array($k, $column)) {
                    $this->query->andWhere([$this->table . '.' . $k => $v]);
                }
            }


            foreach ($this->hiddenColumnWhere as $k => $v) {
                if (in_array($k, $column)) {
                    $this->query->andWhere([$this->table . '.' . $k => $v]);
                }
            }

            unset($column);
            unset($this->andWhere);
            unset($where);

            return $this;
        }

        /**
         *
         *```php
         * // 使用字符串方式
         *->andWhere('id=1 and title like'%a%'')
         * // 使用数组方式
         *->andWhere([ 'id'=> 1,'title' => Title])
         *->andWhere(['like', 'id', 1])
         * // 使用多数组方式
         * ->andWhere([['id'=> 1],['title'=> Title]])
         *```
         *
         * @param $where
         * @param $table
         * @return $this
         */
        public function _andWhere($where, $table = null): PageHandler
        {
            if (!$table) {
                $table = $this->table;
            }
            if (is_array($where)) {
                if (count($where) == count($where, 1)) {
                    $where = $this->whereHandler($where, $table);
                    $this->query->andWhere($where);
                } else {
                    foreach ($where as $value) {
                        $value = $this->whereHandler($value, $table);
                        $this->query->andWhere($value);
                    }
                }
                unset($where);
            } else {
                $this->query->andWhere($where);
            }
            return $this;
        }

        /**
         *
         *```php
         * // 使用字符串方式
         *->orWhere('id=1 and title like'%a%'')
         * // 使用数组方式
         *->orWhere([ 'id'=> 1,'title' => Title])
         *->orWhere(['like', 'id', 1])
         * // 使用多数组方式
         * ->orWhere([['id'=> 1],['title'=> Title]])
         *```
         *
         * @param $where
         * @param $table
         * @return $this
         */
        public function _orWhere($where, $table = null): PageHandler
        {
            if (!$table) {
                $table = $this->table;
            }
            if (is_array($where)) {
                if (count($where) == count($where, 1)) {
                    $where = $this->whereHandler($where, $table);
                    $this->query->orWhere($where);
                } else {
                    foreach ($where as $value) {
                        $value = $this->whereHandler($value, $table);
                        $this->query->orWhere($value);
                    }
                }
            } else {
                $this->query->orWhere($where);
            }
            unset($where);
            return $this;
        }

        private function whereHandler($where, $table): array
        {
            $arr        = [];
            $countWhere = count($where);
            if ($countWhere == 1) {
                foreach ($where as $k => $v) {
                    $arr[$table . '.' . $k] = $v;
                }
            } else {
                $arr = [$where[0], $table . '.' . $where[1], $where[2]];
            }
            unset($countWhere);
            unset($where);
            return $arr;
        }

        public function toArrayAll(): array
        {
            $query = $this->query;

            $count = $query->count();

            $uid = StringHelper::guid();

            SqlPageCache::writeSql($query->orderBy($this->orderBy)->__toString(), $uid);
            if (!empty($this->sqlCacheParam)) {
                SqlPageCache::writeSql(json_encode($this->sqlCacheParam), $uid . '-PARAM');
            }
            unset($table);
            $data = $query->offset($this->offset)
                ->limit($this->limit)
                ->orderBy($this->orderBy)
                ->asArray()->all();
            unset($model);
            unset($query);
            return ['data' => $data, 'count' => (int)$count, 'uid' => $uid];
        }

        public function toSql(): string
        {
            return $this->query->createCommand()->getRawSql();
        }

        /**
         * ```php
         * 使用方式
         * $model = new User();
         * $page = new PageHandler()
         * $page->toTableData($model,$where,$table,$select);
         *
         * ```
         * @param ActiveRecordModel $model
         * @param array             $where
         * @param array             $withTable
         * @param array             $select
         * @return array
         */
        public function toTableData(
            ActiveRecordModel $model,
            array $where = [],
            array $withTable = [],
            array $select = []
        ): array {
            $attributes = $model->formatLabel();

            $data = $this->getTablesParam($model, $where, $withTable)->toArrayAll();

            foreach ($data['data'] as &$v) {
                $v = $model::transFormatData($v);
            }
            $arr = [
                'attribute' => $attributes,
                'list'      => $data,
                'tabTitle'  => $model->tableTitle($attributes, $select),
            ];
            unset($model);
            unset($attributes);
            return $arr;
        }
    }
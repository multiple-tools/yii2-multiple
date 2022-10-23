<?php

    namespace umono\multiple\tools\page;

    use umono\multiple\helpers\StringHelper;
    use umono\multiple\model\ActiveRecordModel;
    use yii\data\Pagination;
    use yii\db\ActiveQuery;
    use yii\web\BadRequestHttpException;

    class PageHandler extends Pagination
    {
        protected $param;
        protected $limit = 20;
        protected $page = 1;
        protected $offset;
        public $select = ['*'];
        public $hiddenColumnWhere = ['is_del' => 0];
        public $orWhere = []; // 额外的条件
        public $andWhere = [];// 额外的条件
        private $sqlCacheParam = []; // 额外的缓存sql参数用于导出时条件复现
        private $showSelectTable = [];
        private $orderBy = [];
        private $isHandlerParam = false;
        /**
         * @var $query ActiveQuery
         */
        public $query;
        // 当前查询的表名
        public $table;
        /**
         * @var ActiveRecordModel $modelClass
         */
        public $modelClass;

        /**
         * 获取表的列名
         *
         * @param $table
         * @return array
         */
        public function getColumn($table): array
        {
            $tableSchema = \Yii::$app->db->schema->getTableSchema($table);
            $columns     = $tableSchema->columnNames;
            unset($tableSchema);
            return $columns;
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
                $this->param = \Yii::$app->request->get();
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

            $this->isHandlerParam = true;
        }

        // 处理排序数据
        private function changTableSortHandler($column)
        {
            // 处理排序
            $toBy      = [];
            $_order_by = $this->param['_order_by'] ?? null;
            if (!empty($_order_by)) {
                $_order_by = explode('-', $_order_by);
                if (!in_array($_order_by[0], $column)) {
                    throw new BadRequestHttpException('当前字段不支持排序！');
                }
                if (!empty($_order_by[1])) {
                    $toBy = [
                        $_order_by[0] => ($_order_by[1] == 'ascending' ? SORT_ASC : SORT_DESC),
                    ];
                }
            }
            if (empty($this->orderBy)) {
                $default = [];
            } else {
                $default = $this->orderBy;
            }
            $orderBy       = array_merge($toBy, $default);
            $this->orderBy = $orderBy;
            unset($orderBy);
        }

        /**
         * 重设limit page的值
         *
         * @param $limit
         * @param $page
         * @return void
         */
        public function setPageLimit($limit, $page): PageHandler
        {
            if (!empty($limit)) {
                $this->limit = $limit;
            }
            if (!empty($page)) {
                $this->page = $page;
            }

            return $this;
        }

        /**
         * 获取分页数据基础条件
         *
         * @param array $where
         * @param array $withTable
         * @return $this
         */
        public function getTablesParam(array $where = [], array $withTable = []): PageHandler
        {
            // 处理传递的参数，获取参数中的基本分页数据
            $this->formatParamHandler($where);

            // 获取当前模型的表里的所有列名
            $column = $this->getColumn($this->table);

            // 处理排序数据
            $this->changTableSortHandler($column);

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
            $this->query->with($withTable)
                ->select($select);
            unset($select);
            unset($table);

            if (!empty($this->orWhere)) {
                $this->orWhere($this->orWhere);
            }

            if (!empty($this->andWhere)) {
                $this->andWhere($this->andWhere);
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
         * @param $where
         * @return $this
         */
        public function where($where): PageHandler
        {
            $this->query->where($where);

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
        public function andWhere($where, $table = null): PageHandler
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
        public function orWhere($where, $table = null): PageHandler
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

        private function whereHandler(array $where, $table): array
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

        /**
         * 返回查询的数据
         *
         * @return array
         */
        public function toArrayAll(): array
        {
            if (!$this->isHandlerParam) {
                $this->getTablesParam();
            }

            $query = $this->query;

            $count = $query->count();

            $uid = StringHelper::guid();

            SqlPageCache::writeSql($query->orderBy($this->orderBy)->__toString(), $uid);
            if (!empty($this->sqlCacheParam)) {
                SqlPageCache::writeSql(json_encode($this->sqlCacheParam), $uid . '-PARAM');
            }
            unset($table);
            $data = $this->execute()->asArray()->all();
            unset($model);
            unset($query);
            return ['data' => $data, 'count' => (int)$count, 'uid' => $uid];
        }

        public function execute(): ActiveQuery
        {
            return $this->query->offset($this->offset)
                ->limit($this->limit)
                ->orderBy($this->orderBy);
        }

        public function toSql(): string
        {
            return $this->execute()->createCommand()->getRawSql();
        }

        /**
         * 返回成数据表格形式
         *
         * @return array
         */
        public function toTableDataArray(): array
        {

            $data = $this->toArrayAll();

            return $this->handlerTableData($this->modelClass, $data);

        }

        private function handlerTableData(ActiveRecordModel $model, $data): array
        {
            $attributes = $model->formatLabel();

            foreach ($data['data'] as &$v) {
                $v = $model::transFormatData($v);
            }
            $arr = [
                'attribute' => $attributes,
                'list'      => $data,
                'tabTitle'  => $model->tableTitle($attributes, $this->showSelectTable),
            ];
            unset($model);
            unset($attributes);

            return $arr;
        }

        public function setSelectTableColumn($column): PageHandler
        {
            $this->showSelectTable = $column;

            return $this;
        }

        public function setSqlCacheParam($sqlCacheParam)
        {
            $this->sqlCacheParam = $sqlCacheParam;
            return $this;
        }

        public function orderBy($orderBy)
        {
            $this->orderBy = $orderBy;
            return $this;
        }
    }
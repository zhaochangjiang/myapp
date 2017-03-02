<?php

namespace framework\bin\database;

use framework\bin\ADBException;

/*
   * 使用pdo连接sqlite
   */

class ADatabaseSQLite implements ADatabase
{

    private $connection;
    public $file;
    public static $debugMessage;
    private $needFetchSqlFlag = true;

    public function __construct($linkName = '')
    {

        $config = App:: base()->database;
        $this->_db_config_prefix = isset($config [$linkName] ['prefix']) ? $config [$linkName] ['prefix'] : '';
        $this->setLinkName($linkName);
    }

    function getNeedFetchSqlFlag()
    {
        return $this->needFetchSqlFlag;
    }

    function setNeedFetchSqlFlag($needFetchSqlFlag)
    {
        $this->needFetchSqlFlag = $needFetchSqlFlag;
    }

    /**
     *
     * @param String $dbname
     */
    public function setDbname($dbname)
    {
        $this->file = App::getBasePath() . D_S . 'data' . D_S . $dbname;

        $this->createConnection();
    }

    /**
     * 创建数据库链接
     */
    public function createConnection()
    {
        if (!file_exists($this->file)) {
            ABaseApplication::createDir(dirname($this->file), 0777);
        }
        try {
            $this->connection = new PDO('sqlite:' . $this->file);
        } catch (PDOException $e) {
            try {
                $this->connection = new PDO('sqlite2:' . $this->file);
            } catch (PDOException $e) {
                throw new ADBException($e->getMessage() . "-{$this->file}");
            }
        }
        //stop($this -> connection );
    }

    /**
     * 执行SQL语句
     */
    public function query($strSQL, $isQuery = true)
    {
        $stm = microtime(true);
        try {
            $stmt = $this->connection->query($strSQL);

            // 如果需要收集执行的SQL语句
            if ($isQuery === true && $this->getNeedFetchSql()) {
                $mss = intval((microtime(true) - $stm) * 1000) . 'ms';
                $this->setExecuteArraySql("[SQL]:{$strSQL} [EXECUTETIME]:{$mss}");
            }
            return $stmt;
        } catch (PDOException $e) {
            throw new ADBException($e->getMessage());
        }
    }

    /**
     *
     * @param field_type $executeArraySql
     */
    public function setExecuteArraySql($strSQL)
    {
        self::$debugMessage [] = $strSQL;
    }

    /**
     * 根据SQL语句返回一组结果集
     */
    public function queryRow($strSQL)
    {
        $stmt = $this->query($strSQL);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetch();
    }

    /**
     * 根据SQL语句返回所有结果集
     */
    public function queryAll($strSQL)
    {
        $stmt = $this->query($strSQL);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    /**
     * 将参数还原为默认值
     *
     * @return NULL
     */
    private function setDefaultVar()
    {
        $this->where = $this->limitString = $this->orderBy = ''; // 清除条件内容
        $this->feild = ' * ';
        $this->condition = null;
        $this->groupBy = '';
    }

    /**
     * 删除表中一条信息。
     *
     * @param $tableName -
     *            String 表名
     * @param $condition -
     *            String OR 一维数组
     * @param $limistString -String
     *            字符串
     * @return Ambigous <NULL, resource>
     */
    public function delete($tableName, $condition, $limitString = '')
    {
        $this->setCondition($condition);
        $this->setLimitString($limitString);
        $strSQL = "DELETE FROM `{$tableName}` WHERE 1 {$this->where} {$this->limitString}";
        return $this->query($strSQL);
    }

    /**
     *
     * @param $feild -String
     *            或得feild的拼接字符串
     * @param
     *            unknown
     */
    private function setFeild($feild)
    {
        // 如果$feild为空，则默认查询全部内容
        if (empty($feild)) {
            $this->feild = ' * ';
            return;
        }
        if (!is_array($feild)) {
            $this->feild = $feild;
            return;
        }
        $this->feild = '';
        foreach ($feild as $value) {
            if (stripos($value, '|')) {
                $temp = explode('|', $value);
                $this->feild .= empty($this->feild) ? "`{$temp[0]}` as {$temp[1]}" : ",`{$temp[0]}` as {$temp[1]}";
            } else {
                $this->feild .= empty($this->feild) ? "`{$value}`" : ",`{$value}`";
            }
        }
    }

    public function setGroupBy($colum)
    {
        if (empty($colum))
            return;
        $this->groupBy = " GROUP BY {$colum} ";
    }

    /**
     *
     * @param $condition -String
     *            or 一维Array
     * @return unkown
     */
    private function setCondition($condition)
    {
        if (empty($condition))
            return;
        $this->condition = $condition;
        $this->getWhere();
    }

    /**
     *
     * @return string
     */
    private function getWhere()
    {
        // xmp($this->condition);
        $aliases = '';
        if (empty($this->condition) || !is_array($this->condition)) {
            $this->where .= empty($this->condition) ? '1 ' : ' and ' . $this->condition;
            return;
        }

        foreach ($this->condition as $key => $value) {
            $temp = $value;
            if (!is_array($value)) {
                $this->where .= " and `{$key}`='{$value}'";
                continue;
            }
            // 处理有别名的情况如a.`type` 中的{a.}
            if (isset($value ['aliases'])) {
                $aliases = "{$value['aliases']}.";
            }

            $temp = $value;
            if (is_array(array_pop($temp))) {
                $this->where .= ' and (1 ';
                foreach ($value as $key => $v) {
                    // 设置默认值
                    if (empty($v ['colum'])) {
                        $v ['colum'] = $key;
                    }
                    // 组织WHERE字符串模块
                    $this->orgWhereByCondition($v, $aliases);
                }
                $this->where .= ')';
                continue;
            }

            // 设置默认值
            if (empty($value ['colum'])) {
                $value ['colum'] = $key;
            }
            // 组织WHERE字符串模块
            $this->orgWhereByCondition($value, $aliases);
        }
        return $this->where;
    }

    /**
     * 组织WHERE条件
     */
    private function orgWhereByCondition($param, $aliases)
    {
        $param ['doType'] = strtolower($param ['doType']);
        switch ($param ['doType']) {
            // 等于 （=）用
            case 'not like' :
            case 'like' :
                $valArr = array(
                    '0' => " '%{$param['value']}%'",
                    '1' => " '{$param['value']}%'",
                    '2' => " '{$param['value']}%'");
                $val = isset($valArr [$param ['likeType']]) ? $valArr [$param ['likeType']] : $valArr ['0'];
                $this->where .= " and {$aliases}`{$param['colum']}` {$param['doType']} {$val}";
                break;
            case '>' :
            case '<>' :
            case '<' :
            case '>=' :
            case '!=' :
            case '=' :
            case '<=' :
                // 如果Value的值为一个数据表字段
                $this->where .= ('colum' === $param ['valueType']) ? " and {$aliases}`{$param['colum']}` {$param['doType']} `{$param['value']}`" : " and {$aliases}`{$param['colum']}` {$param['doType']} '{$param['value']}'";
                // $this->where .= " and {$aliases}`{$param['colum']}` {$param['doType']} '{$param['value']}'";
                break;
            case 'not in' :
                $this->where .= " and {$aliases}`{$param['colum']}` {$param['doType']} ({$param['value']})";
                break;
            case 'in' :
                $this->where .= " and {$aliases}`{$param['colum']}` {$param['doType']} ({$param['value']})";
                break;
            default :
                die('参数错误' . "'{$param ['doType']}'");
                break;
        }
    }

    /*
     * 查询一条数据 @see Database::fetch()
     */

    public function fetch($tableName, $condition, $feild = '', $orderBy = '',
                          $groupBy = '')
    {
        $this->setFeild($feild);
        $this->setCondition($condition);
        $this->setGroupBy($groupBy);
        $strSQL = "SELECT {$this->feild} FROM  `{$tableName}` WHERE 1 {$this->where}   {$this->groupBy}  {$orderBy} LIMIT 1";
        // $strSQL = "SELECT {$this->feild} FROM `{$tableName}` WHERE {$condition} LIMIT 1";
        // xmp($strSQL);
        return $this->queryRow($strSQL);
    }

    /*
     * 获得当前查询条件下的所有数据 @see Database::fetchAll()
     */

    public function fetchAll($tableName, $condition, $feild = '*',
                             $orderBy = '', $limitString = '1000',
                             $groupBy = '')
    {
        $this->setFeild($feild);
        $this->setLimitString($limitString);
        $this->setCondition($condition);
        $this->setOrderBy($orderBy);
        $this->setGroupBy($groupBy);
        $strSQL = "SELECT {$this->feild}
        FROM  `{$tableName}`
        WHERE 1 {$this->where}  {$this->groupBy} {$this->orderBy} {$this->limitString}";
        //xmp($strSQL);
        return $this->queryAll($strSQL);
    }

    /*
     * (non-PHPdoc) @see ADatabase::fetchJoin()
     */

    public function fetchJoin($param, $type = 'leftJoin')
    {
        $strSQL = '';
        switch ($type) {
            case 'leftJoin' :
                $strSQL = "SELECT {$param['feild']}
				FROM `{$param['mainTable']}`  as a 
				LEFT JOIN `{$param['subsidiary']}` as b 
				ON a.`{$param['mainColum']}` = b.`{$param['subsidiaryColum']}` 
				WHERE 1  {$this->where} {$this->groupBy}  {$this->orderBy} {$this->limitString}";
                break;
            case 'rightJoin' :
                $strSQL = "SELECT {$param['feild']}
				FROM `{$param['mainTable']}`  as a
				RIGHT JOIN `{$param['subsidiary']}` as b
				ON a.`{$param['mainColum']}` = b.`{$param['subsidiaryColum']}`
				WHERE 1  {$this->where}  {$this->groupBy}  {$this->orderBy} {$this->limitString}";
                break;
            case 'join' :
                $strSQL = "SELECT {$param['feild']}
				FROM `{$param['mainTable']}`  as a
				, `{$param['subsidiary']}` as b
				WHERE  a.`{$param['mainColum']}` = b.`{$param['subsidiaryColum']}`
				 {$this->where}{$this->groupBy}  {$this->orderBy}  {$this->limitString}";
                break;
            default :
                die('ADatabase::fetchJoin() param error!');
                break;
        }
        return $this->queryAll($strSQL);
    }

    /*
     * (non-PHPdoc) @see Database::add()
     */

    public function add($feild, $tableName)
    {
        $colum = array_keys($feild);

        $valueStr = '\'';
        $i = 0;
        foreach ($feild as $key => $value) {
            if (stripos($value, "'") !== false) {
                $feild[$key] = str_replace("'", "''", $value);
                //implode ( '\',\'' , $feild ) .
            }
            $valueStr .= (
            ($i === 0) ?
                $feild[$key] :
                '\',\'' . $feild[$key]);
            $i++;
        }

        $valueStr .= '\'';

        $strSQL = "INSERT INTO `{$tableName}` (`" . implode('`,`', $colum) . "`) VALUES ({$valueStr})";

        $queryid = $this->query($strSQL);
        if ($queryid)
            return $this->lastId();
        return false;
    }

    public function lastId()
    {
        return $this->connection->lastInsertId();
    }

    /*
     * 批量插入数据 @see Database::addBatch()
     */

    public function addBatch($data, $table)
    {

        //   $this -> startAffair () ;
        $colStr = '';
        $valStr = '';
        $arrayCol = array();
        $j = 0;
        $str_sql = '';

        foreach ($data as $key => $value) {
            if (empty($colStr)) {
                $arrayCol = array_keys($value);
                $colStr = implode(',', $arrayCol);
            }
            if ($arrayCol) {
                $valStr = '';
                $valStr .= $j == 0 ? '(' : '(';
                $i = 0;
                foreach ($arrayCol as $k => $v) {
                    if (stripos($value[$v], "'") !== false) {
                        $value[$v] = str_replace("'", "''", $value[$v]);
                    }

                    $valStr .= $i == 0 ? "'{$value[$v]}'" : ",'{$value[$v]}'";
                    $i++;
                }
                $valStr .= ')';
                $str_sql .= empty($str_sql) ? "INSERT INTO {$table}({$colStr}) VALUES {$valStr}" : ";INSERT INTO {$table}({$colStr}) VALUES {$valStr}";
            }
            // if($j==3)break;
            $j++;
        }

        //  $this -> commit () ;
        $return = $this->query($str_sql);
        // stop ( $str_sql ) ;
        return $return;
    }

    /*
     * @see Database::update()
     */

    public function update($feild, $table, $condition = null, $limit = 10000)
    {
        //$limit无效
        // 需要选择修改的字段和内容
        if (empty($feild)) {
            throw new Exception('您没有设置需要修改的字段和内容');
        }
        $limitStr = $valStr = '';
        foreach ($feild as $key => $value) {
            $valStr .= empty($valStr) ? "`{$key}`='{$value}'" : ",`{$key}`='{$value}'";
        }
        $this->setCondition($condition);
        // 如果$limit为空，则修改整个表
//        if (!empty($limit))
//        {
//            $limitStr = " LIMIT {$limit}";
//        }
        $strSQL = "UPDATE `{$table}` SET {$valStr} WHERE 1 {$this->where}";
//        echo $strSQL;
        return $this->query($strSQL);
    }

    /**
     * 开启数据库事务
     */
    public function startAffair()
    {

        $this->query("BEGIN TRANSACTION");
    }

    /**
     * 事务回滚
     */
    public function rollBack()
    {
        $this->query("ROLLBACK TRANSACTION");
    }

    /**
     * 事务提交
     */
    public function commit()
    {
        $this->query("COMMIT TRANSACTION");
    }

    /**
     * 断开数据库链接
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * 析构函数，断开数据库链接
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * 设置是否收集SQL语句
     *
     * @param
     *            $param
     * @return boolean
     */
    public function getNeedFetchSql()
    {
        return true;
    }

    /**
     *
     * @param string $limitString
     */
    public function setLimitString($limitString)
    {
        if (!empty($limitString))
            $this->limitString = " LIMIT {$limitString}";
        return;
    }

    /**
     *
     * @param string $orderBy
     */
    public function setOrderBy($orderBy)
    {
        if (!empty($orderBy)) {
            $this->orderBy = " ORDER BY {$orderBy}";
        } else {
            $this->orderBy = $orderBy;
        }
    }

    /**
     * 获得链接数据库的基本信息
     */
    public function getDBProperty()
    {
        global $_config;
        if (!empty($this->linkName)) {
            if (isset($_config ['database'] [$this->linkName])) {
                if (!isset($_config ['database'] [$this->linkName] ['port'])) {
                    $_config ['database'] [$this->linkName] ['port'] = 3306;
                }
                return $_config ['database'] [$this->linkName];
            } else {
                throw new ADBException("The config file has not set \$config['database'][{$this->linkName}] !");
            }
        }
    }

    /**
     *
     * @param string $linkName
     */
    public function setLinkName($linkName)
    {
        (empty($linkName)) ? '' : $this->linkName = $linkName;
    }

}

?>
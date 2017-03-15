<?php

namespace framework\bin\widget\database;

use framework\bin\interfaceLib\ADatabase;
use framework\App;

//use framework\bin\widget\database\ADBException;

/**
 * 数据库操作接口
 *
 * @author zhaocj
 */
class ADatabaseMysql implements ADatabase
{

    public static $debugMessage;
    private $where = '';
    private $limitString = '';
    private $orderBy = '';
    private $feild = ' * ';
    protected $linkName = '';
    private $linkId;
    private $condition;
    private $connect;
    private $groupBy = '';
    protected $_db_config_prefix = '';
    private $needFetchSqlFlag = true;

    /**
     * 构造函数
     *
     * @param $linkName -String
     */
    public function __construct($linkName = '')
    {
        $config = App:: base()->database;
        $this->_db_config_prefix = isset($config [$linkName] ['prefix']) ? $config [$linkName] ['prefix'] : '';
        $this->setLinkName($linkName);
        $this->createConnection($linkName, $this->linkName !== $linkName ? false : true);
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
     * 获得当前页面执行的SQL语句数组
     *
     * @return the $executeArraySql
     */
    public function getExecuteArraySql()
    {
        return self::$debugMessage;
    }

    /**
     * 开启数据库事务
     */
    public function startAffair()
    {
        $this->query('START TRANSACTION;');
    }

    /**
     * 事务回滚
     */
    public function rollBack()
    {
        $this->query("ROLLBACK;");
    }

    /**
     * 事务提交
     */
    public function commit()
    {
        $this->query("COMMIT;");
    }

    /**
     *
     * @param field_type $executeArraySql
     */
    public function setExecuteArraySql($strSQL)
    {
        self::$debugMessage [] = $strSQL;
    }

    /*
     * 建立数据库链接 @see Database::createConnection()
     */

    public function createConnection($linkName = null, $newlink = false)
    {
        $this->setLinkName($linkName);
        // 如果当前数据库链接存在，则销毁已有链接，并重新建立链接。

        if ($this->linkId) {
            $this->disconnect();
        }
        $dbconnection = $this->getDBProperty();
        if (!($this->linkId = mysql_connect("{$dbconnection ['host']}:{$dbconnection ['port']}", $dbconnection ['user'], $dbconnection ['password'], $newlink))) {
            throw new ADBException('[' . mysql_errno() . "] \$linkName:{$linkName};Message:" . mysql_error());
        }
        if (!(mysql_select_db($dbconnection ['dbname'], $this->linkId))) {
            throw new ADBException('[' . mysql_errno() . "] \$linkName:{$linkName};Message:" . mysql_error());
        }
        // 设置字符编码集UTF8;
        mysql_query('set names utf8');
    }

    public function lastId()
    {
        return mysql_insert_id($this->linkId);
    }

    /*
     * (non-PHPdoc) @see Database::query() 修正表前缀 select * from {{user}}; 不用填写表前缀
     */

    public function query($strSQL, $isQuery = true)
    {
        //如果数据库配置，表前缀不为空
        if (!empty($this->_db_config_prefix)) {
            $strSQL = preg_replace('/{{(.*?)}}/', $this->_db_config_prefix . '\1', $strSQL);
        }

        $stm = microtime(true);
        $queryId = null;

        if (!$queryId = mysql_query($strSQL, $this->linkId)) {
            throw new ADBException('[' . mysql_errno($this->linkId) . '] ' . mysql_error($this->linkId) . '    [sql]:' . $strSQL);
        }
        /* 将参数还原为默认值 */
        $this->setDefaultVar();
        // 如果需要收集执行的SQL语句
        // if ($isQuery === true && $this->getNeedFetchSql())
        if (true) {
            $mss = intval((microtime(true) - $stm) * 1000) . 'ms';
            $this->setExecuteArraySql("[SQL]:{$strSQL} [EXECUTETIME]:{$mss}");
        }

//         if ( IS_DEBUG )
//         {
//         $feild = array (
//         'sqlString' => $strSQL ,
//         'sqlerror' => mysql_error ( ) ,
//         'SCRIPT_FILENAME' => App :: base ( )->request -> getVar ( 'SCRIPT_FILENAME' ) ,
//         'REQUEST_URI' => App :: base ( )->request -> getVar ( 'REQUEST_URI' ) ,
//         '$_POST' => $_POST );
//         // 将SQL 执行信息记录下来
//          Log :: set ( 'debugsql-'.date ( 'Y-m-d' ).'.txt' , serialize ( $feild ) );
//         }
        unset($strSQL);
        return $queryId;
    }

    public function queryRow($strSQL)
    {

        $stm = microtime(true);
        $query_id = $this->query($strSQL, false);
        $records = array();
        if ($query_id)
            $record = mysql_fetch_array($query_id, MYSQL_ASSOC);

        // 如果需要收集执行的SQL语句
        if ($this->getNeedFetchSql()) {
            $mss = intval((microtime(true) - $stm) * 1000) . 'ms';
            $this->setExecuteArraySql("[SQL]:{$strSQL} [EXECUTETIME]:{$mss}");
        }
        $query_id ? mysql_free_result($query_id) : '';
        return $record;
    }

    /*
     * (non-PHPdoc) @see ADatabase::queryStr()
     */

    public function queryAll($strSQL)
    {

        $stm = microtime(true);
        $query_id = $this->query($strSQL, false);
        $records = array();
        if ($query_id)
            while ($item = mysql_fetch_array($query_id, MYSQL_ASSOC))
                $records [] = $item;
        // 如果需要收集执行的SQL语句
        if ($this->getNeedFetchSql()) {
            $mss = intval((microtime(true) - $stm) * 1000) . 'ms';
            $this->setExecuteArraySql("[SQL]:{$strSQL} [EXECUTETIME]:{$mss}");
        }
        $query_id ? mysql_free_result($query_id) : '';
        return $records;
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
        if (empty($feild))
            $this->feild = ' * ';
        else {
            if (is_array($feild)) {
                $this->feild = '';
                foreach ($feild as $key => $value) {
                    if (stripos($value, '|')) {
                        $temp = explode('|', $value);
                        $this->feild .= empty($this->feild) ? "`{$temp[0]}` as {$temp[1]}" : ",`{$temp[0]}` as {$temp[1]}";
                    } else
                        $this->feild .= empty($this->feild) ? "`{$value}`" : ",`{$value}`";
                }
            } else
                $this->feild = $feild;
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
        $aliases = '';
        if (empty($this->condition) || !is_array($this->condition)) {
            $this->where .= empty($this->condition) ? '1 ' : ' and ' . $this->condition;
            return;
        }

        foreach ($this->condition as $key => $value) {
            if (!is_array($value)) {
                $this->where .= " and `{$key}`='{$value}'";
                continue;
            }
            // 处理有别名的情况如a.`type` 中的{a.}
            if (isset($value ['aliases'])) {
                $aliases = "{$value['aliases']}.";
            }

            $temp = $value;


            //xmp ( $temp ) ;
            //此为处理当某几个条件处理的同一个字段内容
            if (is_array(array_shift($temp))) {

                $this->where .= ' and (1 ';
                foreach ($value as $key => $v) {
                    // 设置默认值
                    empty($v ['colum']) ? $v ['colum'] = $key : '';

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
        switch (strtolower($param ['doType'])) {
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
                if (is_array($param['value'])) {
                    $param['value'] = '"' . implode('","', $param['value']) . '"';
                }
                $this->where .= " and {$aliases}`{$param['colum']}` {$param['doType']} ({$param['value']})";
                break;
            case 'in' :
                if (is_array($param['value'])) {
                    $param['value'] = '"' . implode('","', $param['value']) . '"';
                }
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

        $queryId = $this->query($strSQL);
        return ($queryId) ? mysql_fetch_array($queryId, MYSQL_ASSOC) : array();
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
        // xmp($strSQL);
        return $this->queryAll($strSQL);
    }

    public function setDbConfigPrefix()
    {
        $config = App:: base()->database;
        $this->_db_config_prefix = isset($config [$this->linkName] ['prefix']) ? $config [$this->linkName] ['prefix'] : '';
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
        $valueStr = '\'' . implode('\',\'', $feild) . '\'';
        $strSQL = "INSERT INTO `{$tableName}` (`" . implode('`,`', $colum) . "`) VALUE ({$valueStr})";
        $queryid = $this->query($strSQL);
        if ($queryid)
            return $this->lastId();
        return false;
    }

    /*
     * (non-PHPdoc) @see Database::add()
     */

    public function replace($feild, $tableName)
    {
        $colum = array_keys($feild);
        $valueStr = '\'' . implode('\',\'', $feild) . '\'';
        $strSQL = "REPLACE INTO `{$tableName}` (`" . implode('`,`', $colum) . "`) VALUE ({$valueStr})";
        $queryid = $this->query($strSQL);
        if ($queryid)
            return $this->lastId();
        return false;
    }

    /*
     * (non-PHPdoc) @see Database::add()
     */

    public function replaceBatch($data, $table)
    {
        $colStr = '';
        $valStr = '';
        $arrayCol = array();
        $j = 0;
        foreach ($data as $key => $value) {
            if (empty($colStr)) {
                $arrayCol = array_keys($value);
                $colStr = '`' . implode('`,`', $arrayCol) . '`';
            }
            if ($arrayCol) {
                $valStr .= $j == 0 ? '(' : ',(';
                $i = 0;
                foreach ($arrayCol as $k => $v) {
                    $value[$v] = addcslashes($value[$v], "'");
                    $valStr .= $i == 0 ? "'{$value[$v]}'" : ",'{$value[$v]}'";
                    $i++;
                }
                $valStr .= ')';
            }
            $j++;
        }
        //  $str_sql = "INSERT INTO `{$table}`({$colStr}) VALUES {$valStr}";
        // stop($str_sql);
        return $this->query("REPLACE INTO `{$table}`({$colStr}) VALUES {$valStr}");
    }

    /*
     * 批量插入数据 @see Database::addBatch()
     */

    public function addBatch($data, $table)
    {
        $colStr = '';
        $valStr = '';
        $arrayCol = array();
        $j = 0;
        foreach ($data as $key => $value) {
            if (empty($colStr)) {
                $arrayCol = array_keys($value);
                $colStr = '`' . implode('`,`', $arrayCol) . '`';
            }
            if ($arrayCol) {
                $valStr .= $j == 0 ? '(' : ',(';
                $i = 0;
                foreach ($arrayCol as $k => $v) {
                    $value[$v] = addcslashes($value[$v], "'");
                    $valStr .= $i == 0 ? "'{$value[$v]}'" : ",'{$value[$v]}'";
                    $i++;
                }
                $valStr .= ')';
            }
            $j++;
        }
        //  $str_sql = "INSERT INTO `{$table}`({$colStr}) VALUES {$valStr}";
        // stop($str_sql);
        return $this->query("INSERT INTO `{$table}`({$colStr}) VALUES {$valStr}");
    }

    /*
     * @see Database::update()
     */

    public function update($feild, $table, $condition = null, $limit = 0,
                           $order = '')
    {
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
        if (!empty($limit)) {
            $limitStr = " LIMIT {$limit}";
        }
        $this->setOrderBy($order);
        $strSQL = "UPDATE `{$table}` SET {$valStr} WHERE 1 {$this->where} {$limitStr}";
        return $this->query($strSQL);
    }

    /**
     * 关闭数据库链接
     *
     * @see Database::disconnect()
     */
    public function disconnect()
    {
        if ($this->connect)
            mysql_close($this->connect);
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
        return $this->needFetchSqlFlag;
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
        $_config = App:: base()->database;
        if (empty($this->linkName)) {
            throw new ADBException("Model linkName is empty!");
        }

        if (!isset($_config [$this->linkName])) {
            throw new ADBException("The config file has not set \$config['database']['{$this->linkName}'] !");
            return;
        }
        if (!isset($_config [$this->linkName] ['port'])) {
            $_config [$this->linkName] ['port'] = 3306;
        }
        if (isset($_config [$this->linkName] ['prefix'])) {
            $this->_db_config_prefix = $_config [$this->linkName] ['prefix'];
        }
        return $_config [$this->linkName];
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
  
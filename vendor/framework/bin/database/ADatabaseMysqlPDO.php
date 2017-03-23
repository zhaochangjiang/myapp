<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace framework\bin\database;

use framework\bin\database\ADatabase;
use PDO;
use Exception;
use RuntimeException;

/**
 * Description of ADatabaseMysqlPDO
 *
 * @author zhaocj
 */
class ADatabaseMysqlPDO implements ADatabase
{

    private $needFetchSqlFlag = true;

    public function createConnection()
    {

    }

    private static $PDOStatement = null;

    /**
     * 数据库的连接参数配置
     * @var array
     * @access public
     */
    public static $config = null;

    /**
     * 是否使用永久连接
     * @var bool
     * @access public
     */
    public static $pconnect = false;

    /**
     * 错误信息
     * @var string
     * @access public
     */
    public static $error = '';

    /**
     * 单件模式,保存Pdo类唯一实例,数据库的连接资源
     * @var object
     * @access public
     */
    protected static $link;

    /**
     * 是否已经连接数据库
     * @var bool
     * @access public
     */
    public static $connected = false;

    /**
     * 数据库版本
     * @var string
     * @access public
     */
    public static $dbVersion = null;

    /**
     * 当前SQL语句
     * @var string
     * @access public
     */
    public static $queryStr = array();

    /**
     * 最后插入记录的ID
     * @var integer
     * @access public
     */
    public static $lastInsertId = null;

    /**
     * 返回影响记录数
     * @var integer
     * @access public
     */
    public static $numRows = 0;
// 事务指令数
    public static $transTimes = 0;
    private static $lastSql = '';

    /**
     * 构造函数，
     * @param $dbconfig 数据库连接相关信息，array('ServerName', 'UserName', 'Password', 'DefaultDb', 'DB_Port', 'DB_TYPE')
     */
    public function __construct($linkConfig)
    {
        $this->init($linkConfig);
    }

    /**
     *
     * @return type
     * @throws Exception
     */
    private function init($linkConfig)
    {
        self::$config = $linkConfig;

        //验证系统是否支持PDO
        $this->autoPhpModule();

        $this->authConfig();

        if (self::$pconnect) {
            self::$config['params'][constant('PDO::ATTR_PERSISTENT')] = true;
        }
        try {

            $dsn = 'mysql:host=' . self::$config['host'] . (empty(self::$config['port']) ? '' : ':' . self::$config['port']) . ';dbname=' . self::$config['dbName'];
            self::$link = new PDO($dsn, self::$config['user'], self::$config['password'], self::$config['params']);
        } catch (PDOException $e) {
            throw new Exception('PDO CONNECT ERROR:' . $e->getMessage());
        }
        if (!self::$link) {
            throw new Exception('PDO CONNECT ERROR');
        }
        self::$link->exec('SET NAMES ' . (isset(self::$config['chartset']) ? self::$config['chartset'] : 'utf8'));

        self::$dbVersion = self::$link->getAttribute(constant("PDO::ATTR_SERVER_INFO"));

        // 标记连接成功
        self::$connected = true;

        return self::$link;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /* 数据库操作 */

    /**
     * 获得一条查询结果
     * @access function
     * @param string $sql SQL指令
     * @param integer $seek 指针位置
     * @return array
     */
    public function getRow($sql = null)
    {

        self::query($sql);

        // 返回数组集
        return self::$PDOStatement->fetch(constant('PDO::FETCH_ASSOC'), constant('PDO::FETCH_ORI_NEXT'));
    }

    /**
     * 根据指定ID查找表中记录(仅用于单表操作)
     * @access function
     * @param integer $priId 主键ID
     * @param string $tables 数据表名
     * @param string $fields 字段名
     * @return ArrayObject 表记录
     */
    public function findById($tabName, $priId, $fields = '*')
    {
        return self::getRow(sprintf('SELECT %s FROM %s WHERE id=%d', self::parseFields($fields), $tabName, $priId));
    }

    /**
     * 查找记录
     * @access function
     * @param string $tables 数据表名
     * @param mixed $where 查询条件
     * @param string $fields 字段名
     * @param string $order 排序
     * @param string $limit 取多少条数据
     * @param string $group 分组
     * @param string $having
     * @param boolean $lock 是否加锁
     * @return ArrayObject
     */
    public function find($tables, $where = "", $fields = '*', $order = null,
                         $limit = null, $group = null, $having = null)
    {
        $sql = 'SELECT ' . self::parseFields($fields)
            . ' FROM ' . $tables
            . self::parseWhere($where)
            . self::parseGroup($group)
            . self::parseHaving($having)
            . self::parseOrder($order)
            . self::parseLimit($limit);
        $dataAll = $this->getAll($sql);
        if (count($dataAll) == 1) {
            $rlt = $dataAll[0];
        } else {
            $rlt = $dataAll;
        }
        return $rlt;
    }

    /**
     * 更新记录
     * @access function
     * @param mixed $sets 数据
     * @param string $table 数据表名
     * @param string $where 更新条件
     * @param string $limit
     * @param string $order
     * @return false | integer
     */
    // public function update($sets, $table, $where, $limit = 0, $order = '')
    public function update($feild, $table, $condition = null, $limit = 0,
                           $order = '')
    {
        $sets = $this->filterPost($table, $feild);
        $sql = 'UPDATE ' . $this->parseTableName($table) . ' SET ' . self::parseSets($sets) . self::parseWhere($condition) . self::parseOrder($order) . self::parseLimit($limit);

        return self::execute($sql);
    }

    /**
     * 保存某个字段的值
     * @access function
     * @param string $field 要保存的字段名
     * @param string $value 字段值
     * @param string $table 数据表
     * @param string $where 保存条件
     * @param boolean $asString 字段值是否为字符串
     * @return void
     */
    public function setField($field, $value, $table, $condition = "",
                             $asString = false)
    {
// 如果有'(' 视为 SQL指令更新 否则 更新字段内容为纯字符串
        if (false === strpos($value, '(') || $asString) {
            $value = '"' . $value . '"';
        }
        $sql = 'UPDATE ' . $this->parseTableName($table) . ' SET ' . $field . '=' . $value . self::parseWhere($condition);
        return self::execute($sql);
    }

    /**
     * 删除记录
     * @access function
     * @param mixed $where 为条件Map、Array或者String
     * @param string $table 数据表名
     * @param string $limit
     * @param string $order
     * @return false | integer
     */
    public function delete($table, $where, $limit = '', $order = '')
    {
        $sql = 'DELETE FROM ' . $this->parseTableName($table) . self::parseWhere($where) . self::parseOrder($order) . self::parseLimit($limit);
        return self::execute($sql);
    }

    /**
     * +----------------------------------------------------------
     * 修改或保存数据(仅用于单表操作)
     * 有主键ID则为修改，无主键ID则为增加
     * 修改记录：
     * +----------------------------------------------------------
     * @access function
     * +----------------------------------------------------------
     * @param $tabName 表名
     * @param $aPost 提交表单的 $_POST
     * @param $priId 主键ID
     * @param $aNot 要排除的一个字段或数组
     * @param $aCustom 自定义的一个数组，附加到数据库中保存
     * @param $isExits 是否已经存在 存在：true, 不存在：false
     * +----------------------------------------------------------
     * @return Boolean 修改或保存是否成功
     * +----------------------------------------------------------
     */
    public function saveOrUpdate($tabName, $aPost, $priId = "", $aNot = "",
                                 $aCustom = "", $isExits = false)
    {
        if (empty($tabName) || !is_array($aPost) || is_int($aNot)) {
            return false;
        }
        if (is_string($aNot) && !empty($aNot)) {
            $aNot = array(
                $aNot);
        }

        if (is_array($aNot) && is_int(key($aNot))) {
            $aPost = array_diff_key($aPost, array_flip($aNot));
        }
        if (is_array($aCustom) && is_string(key($aCustom))) {
            $aPost = array_merge($aPost, $aCustom);
        }
        if (empty($priId) && !$isExits) { //新增
            $aPost = array_filter($aPost, array(
                $this,
                'removeEmpty'));
            return self::add($aPost, $tabName);
        } else { //修改
            return self::update($aPost, $tabName, "id=" . $priId);
        }
    }

    /**
     * 获取最近一次查询的sql语句
     * @access function
     * @param
     * @return String 执行的SQL
     */
    public function getLastSql()
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }

        return self::$lastSql;
    }

    /**
     * 获取最后插入的ID
     * @access function
     * @param
     * @return integer 最后插入时的数据ID
     */
    public function getLastInsId()
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }
        return self::$lastInsertId;
    }

    /**
     * 获取DB版本
     * @access function
     * @param
     * @return string
     */
    public function getDbVersion()
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }
        return self::$dbVersion;
    }

    /**
     * 取得数据库的表信息
     * @access function
     * @return array
     */
    public function getTables()
    {
        $info = array();
        if (self::query("SHOW TABLES")) {
            $result = $this->getAll();
            foreach ($result as $key => $val) {
                $info[$key] = current($val);
            }
        }
        return $info;
    }

    public function setQueryStr($sql)
    {
        self::$queryStr[] = $sql;
        self::$lastSql = $sql;
    }

    /**
     * 取得数据表的字段信息
     * @access function
     * @return array
     */
    private function getFields($tableName)
    {
        $tableName = trim($this->parseTableName($tableName), '`');
        // 获取数据库联接
        $sql = "SELECT
                ORDINAL_POSITION ,COLUMN_NAME, COLUMN_TYPE, DATA_TYPE,
                IF(ISNULL(CHARACTER_MAXIMUM_LENGTH), (NUMERIC_PRECISION + NUMERIC_SCALE), CHARACTER_MAXIMUM_LENGTH) AS MAXCHAR,
                IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT
                FROM
                INFORMATION_SCHEMA.COLUMNS
                WHERE
                TABLE_NAME = :tabName AND TABLE_SCHEMA='" . self::$config['dbname'] . "'";

        $this->setQueryStr(sprintf($sql, $tableName));
        $sth = self::$link->prepare($sql);
        $sth->bindParam(':tabName', $tableName);
        $sth->execute();
        $result = $sth->fetchAll(constant('PDO::FETCH_ASSOC'));
        $info = array();
        foreach ($result as $val) {
            $info[$val['COLUMN_NAME']] = array(
                'postion' => $val['ORDINAL_POSITION'],
                'name' => $val['COLUMN_NAME'],
                'type' => $val['COLUMN_TYPE'],
                'd_type' => $val['DATA_TYPE'],
                'length' => $val['MAXCHAR'],
                'notnull' => (strtolower($val['IS_NULLABLE']) == "no"),
                'default' => $val['COLUMN_DEFAULT'],
                'primary' => (strtolower($val['COLUMN_KEY']) == 'pri'),
                'autoInc' => (strtolower($val['EXTRA']) == 'auto_increment'),
                'comment' => $val['COLUMN_COMMENT']
            );
        }

        // 有错误则抛出异常
        self::haveErrorThrowException();
        return $info;
    }

    /*       * ****************************************************************************************************** */
    /* 内部操作方法 */
    /*       * ****************************************************************************************************** */

    /**
     * 有出错抛出异常
     * @access function
     * @return
     */
    static function haveErrorThrowException()
    {
        $obj = empty(self::$PDOStatement) ? self::$link : self::$PDOStatement;

        $arrError = $obj->errorInfo();
        if (!empty($arrError[2])) { // 有错误信息
            throw new RuntimeException('[ RESULT ] :Query was failure.' . PHP_EOL . '[ ERROR ] :' . $arrError[2] . '".' . PHP_EOL . '  [ SQL语句 ] : ' . self::$lastSql, FRAME_THROW_EXCEPTION);
        }

        if (empty($arrError[1])) {//没有结果集
            return;
        }
    }

    /**
     * where分析
     * @access function
     * @param mixed $where 查询条件
     * @return string
     */
    static function parseWhere($where)
    {
        if (empty($where)) {
            return '';
        }
        if (!is_array($where)) {

            return '  WHERE ' . $where;
        }
        $aliases = '';
        $whereString = ' WHERE 1 ';
        foreach ($where as $key => $value) {
            if (!is_array($value)) {
                $whereString .= " and `{$key}`='{$value}'";
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

                $whereString .= ' and (1 ';
                foreach ($value as $key => $v) {
                    // 设置默认值
                    empty($v ['colum']) ? $v ['colum'] = $key : '';

                    // 组织WHERE字符串模块
                    $whereString .= static::orgWhereByCondition($v, $aliases);
                }
                $whereString .= ')';
                continue;
            }

            // 设置默认值
            if (empty($value ['colum'])) {
                $value ['colum'] = $key;
            }

            // 组织WHERE字符串模块
            $whereString .= static::orgWhereByCondition($value, $aliases);
        }
        return $whereString;
    }

    /**
     * 组织WHERE条件
     */
    private static function orgWhereByCondition($param, $aliases)
    {
        $conditionString = '';
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
                $conditionString .= " and {$aliases}`{$param['colum']}` {$param['doType']} {$val}";
                break;
            case '>' :
            case '<>' :
            case '<' :
            case '>=' :
            case '!=' :
            case '=' :
            case '<=' :
                // 如果Value的值为一个数据表字段
                $conditionString .= ('colum' === $param ['valueType']) ? " and {$aliases}`{$param['colum']}` {$param['doType']} `{$param['value']}`" : " and {$aliases}`{$param['colum']}` {$param['doType']} '{$param['value']}'";
                // $this->where .= " and {$aliases}`{$param['colum']}` {$param['doType']} '{$param['value']}'";
                break;

            case 'not in' :
                if (is_array($param['value'])) {
                    $param['value'] = '"' . implode('","', $param['value']) . '"';
                }
                $conditionString .= " and {$aliases}`{$param['colum']}` {$param['doType']} ({$param['value']})";
                break;
            case 'in' :
                if (is_array($param['value'])) {
                    $param['value'] = '"' . implode('","', $param['value']) . '"';
                }
                $conditionString .= " and {$aliases}`{$param['colum']}` {$param['doType']} ({$param['value']})";
                break;
            default :

                die('参数错误' . "'{$param ['doType']}'");
                break;
        }
        return $conditionString;
    }

    /**
     * order分析
     * @access function
     * @param mixed $order 排序
     * @return string
     */
    static function parseOrder($order)
    {
        $orderStr = '';
        if (is_array($order)) {
            $orderStr .= ' ORDER BY ' . implode(',', $order);
        } else if (is_string($order) && !empty($order)) {
            $orderStr .= ' ORDER BY ' . $order;
        }
        return $orderStr;
    }

    function setNeedFetchSqlFlag($needFetchSqlFlag)
    {
        $this->needFetchSqlFlag = $needFetchSqlFlag;
    }

    /**
     * limit分析
     * @access function
     * @param string $limit
     * @return string
     */
    static function parseLimit($limit)
    {
        $limitStr = '';
        if (is_array($limit)) {
            $limitStr .= (count($limit) > 1) ? ' LIMIT ' . $limit[0] . ' , ' . $limit[1] . ' ' : ' LIMIT ' . $limit[0] . ' ';
        } elseif ((is_string($limit) || is_numeric($limit)) && !empty($limit)) {
            $limitStr .= ' LIMIT ' . $limit . ' ';
        }
        return $limitStr;
    }

    /**
     * group分析
     * @access function
     * @param mixed $group
     * @return string
     */
    static function parseGroup($group)
    {
        if (empty($group)) {
            return '';
        }
        return is_array($group) ? ' GROUP BY ' . implode(',', $group) : ' GROUP BY ' . $group;
    }

    /**
     * having分析
     * @access function
     * @param string $having
     * @return string
     */
    static function parseHaving($having)
    {
        return (is_string($having) && !empty($having)) ? ' HAVING ' . $having : '';
    }

    /**
     * @param array $fields
     */
    static function walkFunction(array $fields)
    {
        foreach ($fields as $value) {
            self::addSpecialChar($value);
        }
    }

    /**
     * fields分析
     * @access function
     * @param mixed $fields
     * @return string
     */
    static function parseFields($fields)
    {
        if (is_array($fields)) {
            self::walkFunction($fields);
            $fieldsStr = implode(',', $fields);
            return $fieldsStr;
        }
        if (is_string($fields) && !empty($fields)) {
            if (false === strpos($fields, '`')) {
                $fields = explode(',', $fields);
                self::walkFunction($fields);
                $fieldsStr = implode(',', $fields);
            } else {
                $fieldsStr = $fields;
            }
            return $fieldsStr;
        }
        return '*';
    }

    /**
     * sets分析,在更新数据时调用
     * @access function
     * @param mixed $values
     * @return string
     */
    private function parseSets($sets)
    {
        if (is_string($sets)) {
            return $sets;
        }
        $setsStr = '';
        if (is_array($sets)) {
            foreach ($sets as $key => $val) {
                $key = $this->addSpecialChar($key);
                $val = $this->fieldFormat($val);
                $setsStr .= "$key = " . $val . ",";
            }
            $setsStr = substr($setsStr, 0, -1);
            return $setsStr;
        }
        throw new RuntimeException('The format $sets what you give  is wrong!', FRAME_THROW_EXCEPTION);
    }

    /**
     * 字段格式化
     * @access function
     * @param mixed $value
     * @return mixed
     */
    private function fieldFormat(&$value)
    {
        if (is_int($value)) {
            $value = intval($value);
        } else if (is_float($value)) {
            $value = floatval($value);
        } elseif (preg_match('/^\(\w*(\+|\-|\*|\/)?\w*\)$/i', $value)) {

            // 支持在字段的值里面直接使用其它字段
            // 例如 (score+1) (name) 必须包含括号
            $value = $value;
        } else if (is_string($value)) {
            $value = '\'' . $this->escape_string($value) . '\'';
        }
        return $value;
    }

    /**
     * 字段和表名添加` 符合
     * 保证指令中使用关键字不出错 针对mysql
     * @access function
     * @param mixed $value
     * @return mixed
     */
    private static function addSpecialChar(&$value)
    {
        if ('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos($value, '`')) {

            //如果包含* 或者 使用了sql方法 则不作处理
        } elseif (false === strpos($value, '`')) {
            $value = '`' . trim($value) . '`';
        }
        return $value;
    }

    /**
     * 去掉空元素
     * @access function
     * @param mixed $value
     * @return mixed
     */
    private function removeEmpty($value)
    {
        return !empty($value);
    }

    /**
     * 执行查询 主要针对 SELECT, SHOW 等指令
     * @access function
     * @param string $sql sql指令
     * @return mixed
     */
    public function query($sql = '')
    {

        //释放上次结果集,
        $this->free();
        $this->setQueryStr($sql);
        self::$PDOStatement = self::$link->prepare($sql);
        $queryResult = self::$PDOStatement->execute();

        // 有错误则抛出异常,系统捕获并输出调试信息
        self::haveErrorThrowException();
        return $queryResult;
    }

    /**
     * 数据库操作方法
     * @access function
     * @param string $sql 执行语句
     * @param boolean $lock 是否锁定(默认不锁定)
     * @return void
    public function execute($sql='',$lock=false) {
     * if(empty($sql)) $sql = $this->queryStr;
     * return $this->_execute($sql);
     * } */

    /**
     * 执行语句 针对 INSERT, UPDATE 以及DELETE
     * @access function
     * @param string $sql sql指令
     * @return integer
     */
    public function execute($sql = '')
    {

//        if (self::isMainIps($sql)) {
//            return $this->execute($sql);
//        } else {
//            return $this->getAll($sql);
//        }
//        
        // 获取数据库联接
        //释放前次的查询结果
        if (!empty(self::$PDOStatement)) {
            self::free();
        }
        $this->setQueryStr($sql);
        $result = self::$link->exec($sql);
        // 有错误则抛出异常
        self::haveErrorThrowException();
        return true;
    }

    /**
     * 是否为数据库更改操作
     * @access private
     * @param string $query SQL指令
     * @return boolen 如果是查询操作返回false
     */
    static function isMainIps($query)
    {
        $queryIps = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', $query)) {
            return true;
        }
        return false;
    }

    /**
     * 过滤POST提交数据
     * @access private
     * @param mixed $data POST提交数据
     * @param string $table 数据表名
     * @return mixed $newdata
     */
    private function filterPost($table, $data)
    {

        $table_column = self::getFields($table);

        $newdata = array();
        foreach ($table_column as $key => $val) {
            if (array_key_exists($key, $data) && ($data[$key]) !== '') {
                $newdata[$key] = $data[$key];
            }
        }
        return $newdata;
    }

    /**
     * 启动事务
     * @access function
     * @return void
     */
    public function startTrans()
    {


        if (self::$transTimes == 0) {
            self::$link->beginTransaction();
        }
        self::$transTimes += 1;
        return;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access function
     * @return boolen
     */
    public function commit()
    {

        if (self::$transTimes > 0) {
            $result = self::$link->commit();
            self::$transTimes = 0;
            if (!$result) {
                throw new Exception(self::$error);
            }
            return;
        }

        //你没有开启数据库事务
        throw new Exception("You hasn't open database transaction!");
    }

    /**
     * 事务回滚
     * @access function
     * @return boolen
     */
    public function rollback()
    {

        if (self::$transTimes > 0) {
            $result = self::$link->rollback();
            self::$transTimes = 0;
            if (!$result) {
                throw new Exception(self::error);
            }
        }
        return true;
    }

    private function autoPhpModule()
    {
        if (!class_exists('PDO')) {
            throw_exception("this system can't find PDO MODULE");
        }
    }

    private function authConfig()
    {
        if (empty(self::$config['params'])) {
            self::$config['params'] = array();
        }
    }

    /**
     * SQL指令安全过滤
     * @access function
     * @param string $str SQL指令
     * @return string
     */
    private function escape_string($str)
    {
        return addslashes($str);
    }

    /**
     * 插入（单条）记录
     * @access function
     * @param mixed $feild 数据
     * @param string $tableName 数据表名
     * @param boolean $ignore 是否忽略已经写入的数据
     * @return false | integer
     */
    public function add($feild, $tableName, $ignore = false)
    {

        $table = $this->parseTableName($tableName);
        //过滤提交数据
        $data = $this->filterPost($table, $feild);

        foreach ($data as $key => $val) {
            if (is_array($val) && strtolower($val[0]) == 'exp') {
                $val = $val[1]; // 使用表达式 ???
            } elseif (is_scalar($val)) {
                $val = self::fieldFormat($val);
            } else {
                // 去掉复合对象
                continue;
            }
            $data[$key] = $val;
        }
        $fields = array_keys($data);
        array_walk($fields, array(
            $this,
            'addSpecialChar'));
        $fieldsStr = implode(',', $fields);
        $values = array_values($data);
        $valuesStr = implode(',', $values);
        $sql = ($ignore) ? 'INSERT IGNORE  INTO ' . $this->parseTableName($table) . ' (' . $fieldsStr . ') VALUES (' . $valuesStr . ')' : 'INSERT  INTO ' . $this->parseTableName($table) . ' (' . $fieldsStr . ') VALUES (' . $valuesStr . ')';

        return self::execute($sql);
    }

    /**
     *
     * @param type $data
     * @param type $table
     * @param type $ignore
     * @return type
     */
    public function addBatch($data, $table, $ignore = false)
    {
        //过滤提交数据
        // $data  = $this->filterPost($table, $feild);
        $insertString = 'INSERT' . ($ignore ? ' IGNORE ' : ' ') . $this->parseTableName($table) . ' ';
        $feildArray = array();
        $valueStr = '';
        foreach ($data as $val) {
            if (empty($feildArray)) {
                $feildArray = array_keys($val);
            }
            $valueString = '';
            foreach ($feildArray as $v) {
                $valueString .= ($valueString === '') ? "{$val[$v]}" : ",{$val[$v]}";
            }
            $valueStr .= empty($valueStr) ? "({$valueString})" : ",({$valueString})";
        }
        $insertString .= '(`' . implode('`,`', $feildArray) . '`)' . " values {$valueStr}";

        return self::execute($insertString);
    }

    /**
     * 释放结果集
     */
    public function disconnect()
    {
        self::$PDOStatement = null;
        self::$link = null;
    }

    private function free()
    {
        self::$PDOStatement = null;
    }

    /**
     * 转换表名称
     * @param type $tableName
     * @return type
     */
    private function parseTableName($tableName)
    {
        return preg_replace('/{{(.*?)}}/', self::$config['prefix'] . '\1', $tableName);
    }

    /**
     *
     * @param type $data
     */
    public function replace($data, $table)
    {
        //过滤提交数据
        $data = $this->filterPost($table, $data);
        foreach ($data as $key => $val) {
            if (is_array($val) && strtolower($val[0]) == 'exp') {
                $val = $val[1]; // 使用表达式 ???
            } elseif (is_scalar($val)) {
                $val = self::fieldFormat($val);
            } else {
                // 去掉复合对象
                continue;
            }
            $data[$key] = $val;
        }
        $fields = array_keys($data);
        array_walk($fields, array(
            $this,
            'addSpecialChar'));
        $fieldsStr = implode(',', $fields);
        $values = array_values($data);
        $valuesStr = implode(',', $values);
        $sql = 'REPLACE INTO ' . $this->parseTableName($table) . ' (' . $fieldsStr . ') VALUES (' . $valuesStr . ')';
        return self::execute($sql);
    }

    /**
     * 检索单条数据
     * @param type $tableName
     * @param type $condition
     * @param type $fields
     * @param type $order
     * @param type $groupBy
     * @param type $having
     * @return type
     */
    public function fetch($tableName, $condition, $fields = '*', $order = '',
                          $groupBy = '', $having = '')
    {

        $sql = 'SELECT ' . self::parseFields($fields)
            . ' FROM  ' . $this->parseTableName($tableName)
            . self::parseWhere($condition)
            . self::parseGroup($groupBy)
            . self::parseHaving($having)
            . self::parseOrder($order) .
            self::parseLimit(1);

        $dataAll = $this->queryAll($sql);
        return isset($dataAll[0]) ? $dataAll[0] : array();
    }

    /**
     *
     * @param type $tableName
     * @param type $condition
     * @param type $fields
     * @param type $orderBy
     * @param type $limit
     * @param type $groupBy
     * @param type $having
     * @return type
     */
    public function fetchAll($tableName, $condition, $fields = '*',
                             $orderBy = '', $limit = '1000', $groupBy = '',
                             $having = '')
    {
        $sql = 'SELECT ' . self::parseFields($fields)
            . ' FROM  ' . $this->parseTableName($tableName)
            . self::parseWhere($condition)
            . self::parseGroup($groupBy)
            . self::parseHaving($having)
            . self::parseOrder($orderBy) .
            self::parseLimit($limit);

        return $this->queryAll($sql);
    }

    public function fetchJoin($param, $type = 'leftJoin')
    {

    }

    /**
     * 获得所有的查询数据
     * @access function
     * @return array
     */
    public function queryAll($strSQL)
    {
        self::query($strSQL);
        //返回数据集
        return self::$PDOStatement->fetchAll(constant('PDO::FETCH_ASSOC'));
    }

    public function queryRow($strSQL)
    {

    }

    public function startAffair()
    {
        self::$link->beginTransaction();
        self::$transTimes++;
    }

}
  
<?php

namespace framework\bin\database;
;

use framework\App;

use framework\bin\exception\ADBException;

/**
 * 系统基础Model
 *
 * @author zhaocj
 */
abstract class AModel
{

    /**
     * @var ADatabaseMysqlPDO
     */
    public $model = null;
    protected $tableName = '';
    protected $_db_config;
    protected $_db_config_prefix = '';
    protected $linkName = '';
    protected $dbName;
    protected $needFetchSqlFlag = false;

    /**
     * AModel constructor.
     * @param string $linkName
     */
    public function __construct($linkName = '')
    {

        if (!empty($linkName)) {
            $this->linkName = $linkName;
        }
    }


    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @throws ADBException
     */
    private function initDBConfig()
    {

        $this->_db_config = App:: $app->getDatabaseConfig();
        if (empty($this->linkName)) {
            throw new ADBException("Model linkName:'{$this->linkName}' is empty!");
        }
        if (!isset($this->_db_config [$this->linkName])) {
            throw new ADBException("The config file has not set \$config['database']['{$this->linkName}'] !");
        }
        $this->_db_config_prefix = isset($this->_db_config [$this->linkName] ['prefix']) ? $this->_db_config [$this->linkName] ['prefix'] : '';
    }

    /**
     * 初始化类
     * @return void
     */
    public function init()
    {
        //如果已经初始化了，就不在初始化连接信息
        if (!empty($this->model)) {
            return;
        }

        $this->initDBConfig();

        // 获得当前数据库类型，默认数据库类型为Mysql
        $modelClass = $this->getDatabaseType($this->linkName);
        $this->model = new $modelClass($this->_db_config[$this->linkName]);

        //如果是设置了数据库名称
        if (!empty($this->dbName)) {
            $this->model->setDbname($this->dbName);
        }
        $this->model->setNeedFetchSqlFlag($this->needFetchSqlFlag);

        $this->tableName = $this->tableName();
    }

    /**
     * 获得子类的表名
     * @return string
     */
    protected function tableName()
    {
        return $this->tableName;
    }

    /**
     *
     * @param String $linkName
     * @return Object
     */
    public static function getModel($linkName)
    {
        $dbType = self::$_db_config[$linkName]['DATABASE_TYPE'];
        $modelClass = "ADatabase{$dbType}";
        return new $modelClass($linkName);
    }

    /**
     * @param $strSQL
     * @return mixed
     */
    protected function query($strSQL)
    {
        $this->init();
        return $this->model->query($strSQL);
    }

    /**
     * @param $strSQL
     * @return mixed
     */
    protected function queryRow($strSQL)
    {
        $this->init();
        return $this->model->queryRow($strSQL);
    }

    protected function queryAll($strSQL)
    {
        $this->init();
        return $this->model->queryAll($strSQL);
    }

    protected function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }


    /**
     * @param $condition
     * @param $tableName
     * @param $limitString
     * @return boolean
     */
    protected function delete($condition, $tableName = '', $limitString = '')
    {

        $this->init();
        if ($tableName != '') {
            $this->setTableName($tableName);
        }

        return $this->model->delete($this->getTableName(), $condition, $limitString);
    }

    /**
     * 开启数据库事务
     */
    public function startAffair()
    {
        $this->init();
        $this->model->startAffair();
    }

    /**
     * 事务回滚
     */
    public function rollBack()
    {
        $this->init();
        $this->model->rollBack();
    }

    /**
     * 事务提交
     */
    public function commit()
    {
        $this->init();
        $this->model->commit();
    }

    /**
     *
     * @param Array $param
     * @param string $type
     */
    protected function fetchJoin($param, $type = 'leftJoin')
    {
        $this->init();
        return $this->model->fetchJoin($param, $type);
    }

    /*
     * 查询一条数据 @see Database::fetch()
     */

    protected function fetch($tableName, $condition = array(), $feild = '*', $orderBy = '', $groupBy = '')
    {
        $this->init();
        $this->setTableName($tableName);
        return $this->model->fetch($this->getTableName(), $condition, $feild, $orderBy, $groupBy);
    }

    /*
     * 获得当前查询条件下的所有数据 @see Database::fetchAll()
     */

    protected function fetchAll($tableName, $condition = array(), $feild = '*', $orderBy = '', $limitString = '1000', $groupBy = '')
    {
        $this->init();
        $this->setTableName($tableName);
        return $this->model->fetchAll($this->getTableName(), $condition, $feild, $orderBy, $limitString, $groupBy);
    }

    /**
     * 不需要填写$tableName参数的用法,新增$condition可以直接写where后面的语句
     *
     * @param string|array $condition
     * @param string|array $field
     * @param string|array $orderBy
     * @param string|array $orderBy
     * @return  array
     */
    protected function find($condition = '', $field = '*', $orderBy = '', $groupBy = '')
    {
        $this->init();
        return $this->model->fetch($this->getTableName(), $condition, $field, $orderBy, $groupBy);
    }

    /**
     *
     * @param string|array $condition
     * @param string|array $field
     * @param string|array $orderBy
     * @param string $limitString
     * @param string $groupBy
     * @return object
     */
    protected function findAll($condition = '', $field = '*', $orderBy = '', $limitString = '', $groupBy = '')
    {
        $this->init();
        if (!is_array($condition)) {
            if (FALSE === strpos(strtolower($condition), 'limit') && empty($limitString)) {
                $limitString = '1000';
            }
        }
        return $this->model->fetchAll($this->getTableName(), $condition, $field, $orderBy, $limitString, $groupBy);
    }

    /**
     *
     * @param array $field
     * @param string $tableName
     * @param bool $ignore
     * @return type
     */
    protected function add($field, $tableName = '', $ignore = false)
    {
        $this->init();
        if ($tableName !== '') {
            $this->setTableName($tableName);
        }
        return $this->model->add($field, $this->getTableName(), $ignore);
    }

    /**
     *
     * @param string|array $field
     * @param string $tableName
     * @return object
     */
    protected function replace($field, $tableName = '')
    {
        $this->init();
        if ($tableName !== '') {
            $this->setTableName($tableName);
            return $this->model->replace($field, $tableName);
        }
        return $this->model->replace($field, $this->getTableName());
    }

    /**
     *
     * @param string|array $field
     * @param string $tableName
     * @return bool
     */
    protected function replaceBatch($field, $tableName = '')
    {
        $this->init();
        if ($tableName !== '') {
            $this->setTableName($tableName);
            return $this->model->replaceBatch($field, $tableName);
        }
        return $this->model->replaceBatch($field, $this->getTableName());
    }


    /**
     * 批量插入数据 @see Database::addBatch()
     * @param $data
     * @param string $tableName
     * @param bool $ignore
     * @return bool
     */
    protected function addBatch($data, $tableName = '', $ignore = false)
    {

        if (empty($data)) {
            return true;
        }
        $this->init();
        if ($tableName != '') {
            $this->setTableName($tableName);
            return $this->model->addBatch($data, $tableName, $ignore);
        }
        return $this->model->addBatch($data, $this->getTableName(), $ignore);
    }


    /**
     * @param $field
     * @param string $condition
     * @param string $tableName
     * @param int $limit
     * @return mixed
     */
    protected function update($field, $condition = '', $tableName = '', $limit = 0)
    {
        $this->init();
        if ($tableName != '') {
            $this->setTableName($tableName);
        }
        return $this->model->update($field, $this->getTableName(), $condition, $limit);
    }

    /**
     * 关闭数据库链接
     *
     * @see Database::disconnect()
     */
    protected function disconnect()
    {

        if (is_object($this->model)) {
            $this->model->disconnect();
        }
    }

    protected function setLinkName($linkName)
    {
        $this->linkName = $linkName;
    }

    /**
     * 方法用来获取私有属性
     *
     * @param String $property
     * @return Object
     */
    protected function __get($property)
    {
        if (isset($this->$property)) {
            return $this->$property;
        }
        return NULL;
    }

    /**
     * 修改数据库连接
     *
     * @param String $linkName
     * @param bool $newLink
     * @return object
     */
    protected function createConnection($linkName = null, $newLink = false)
    {
        if (empty($this->model)) {

            // 获得当前数据库类型，默认数据库类型为Mysql
            $modelClass = $this->getDatabaseType($linkName);

            $this->model = new $modelClass($linkName);
        }
        return $this->model->createConnection($linkName, $newLink);
    }

    /**
     *
     * @param String $property
     * @param String $value
     */
    protected function __set($property, $value)
    {
        $this->$property = $value;
    }

    /*
     * 返回结果集数目 @condition 可以是where后面的条件 也可以是数组array('字段'=>'值')
     */

    protected function count($condition = '', $tableName = '')
    {
        $this->init();
        if ($tableName != '') {
            $this->setTableName($tableName);
        }

        $result = $this->model->fetch($this->getTableName(), $condition, ' count(*) as count ');
        $count = (isset($result ['count'])) ? $result ['count'] : 0;
        return $count;
    }

    /*
     * 检查所给条件存不存在
     */

    protected function exists($condition = '', $tableName = '')
    {
        $this->init();
        if ($tableName != '') {
            $this->setTableName($tableName);
        }
        $result = $this->model->fetch($this->getTableName(), $condition, ' count(*) as count ');
        $isExists = $result ['count'] > 0 ? true : FALSE;
        return $isExists;
    }

    /**
     * 获得分页数据.
     *
     * @param array $param =
     *            array(
     *            'condition'=>一维数组或者字符串，默认为空字符串,//检索条件
     *            'orderBy'=>排序方式,默认为空字符串。如果有值，则只需要加上需要排序的字段名称 和排序方式如： 'id DESC'
     *            'limit'=> 查询条数LIMIT,默认为空字符串。如果有值，则只需要加上需要条数如： '0,10'-从第0条开始，向后查询10条
     *            )
     *
     * @param integer $countNum
     * @param  string $tableName
     * @return  object
     *
     */
    protected function getPagedData($param, $countNum, $tableName = '')
    {
        $this->init();
        if ($tableName != '') {
            $this->setTableName($tableName);
        }

        $param ['condition'] = is_array($param ['condition']) ? $param ['condition'] : array();
        $data = array(
            'count' => $countNum,
            'list' => array());
// 如果查询出来数量为空，那么就没有必要查询详细内容了。肯定为空
        if (!empty($data ['count'])) {
            $data ['list'] = $this->model->fetchAll($this->getTableName(), $this->setDefaultValueByKey($param, 'condition', ''), '', $this->setDefaultValueByKey($param, 'orderBy', ''), $this->setDefaultValueByKey($param, 'limit', ''));
        }
        return $data;
    }

    /**
     * 给定数组返回指定键对应的值，默认用$defaultType代替
     *
     * @param array $param
     * @param string $key
     * @param string $defaultType
     * @return string
     */
    protected function setDefaultValueByKey($param, $key, $defaultType = '')
    {
        return isset($param [$key]) ? $param [$key] : $defaultType;
    }

    /**
     *
     * @return String
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     *
     * @param String $model
     */
    protected function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * 设置表名
     *
     * @param String $tableName
     */
    protected function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * 获得当前数据库类型，默认数据库类型为Mysql
     */
    private function getDatabaseType($linkName)
    {

// 获得当前数据库类型，默认数据库类型为Mysql
        if (empty($this->_db_config [$linkName] ['DATABASE_TYPE'])) {
            $this->_db_config [$linkName] ['DATABASE_TYPE'] = 'Mysql';
        }
        return __NAMESPACE__ . "\ADatabase{$this->_db_config[$linkName]['DATABASE_TYPE']}";
    }

}

<?php

namespace framework\bin;

use framework\App;
use framework\bin\database\ADatabaseMysql;
use framework\bin\database\ADBException;

/**
 * 系统基础Model
 *
 * @author zhaocj
 */
abstract class AModel
{

    public $model = null;
    protected $tableName = '';
    protected $_db_config;
    protected $_db_config_prefix = '';
    protected $linkName = '';
    protected $dbname;
    protected $needFetchSqlFlag = false;

    /**
     *
     * @param String $linkName
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

    private function initDBConfig()
    {

        $this->_db_config = App:: base()->database;

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
     */
    public function init()
    {
        //如果已经初始化了，就不在初始化连接信息
        if (!empty($this->model)) {
            return;
        }

        $this->initDBConfig();

        $databaseConfig = App:: base()->database;
        // 获得当前数据库类型，默认数据库类型为Mysql
        $modelClass = $this->getDatabaseType($this->linkName);
        $this->model = new $modelClass($databaseConfig[$this->linkName]);

//如果是设置了数据库名称
        if (!empty($this->dbname)) {
            $this->model->setDbname($this->dbname);
        }
        $this->model->setNeedFetchSqlFlag($this->needFetchSqlFlag);
//        $this -> model = empty ( $this -> dbname ) ? new $modelClass ( $this -> linkName )
//                    : new $modelClass ( $this -> sqlite_dbname ) ;

        $this->tableName = $this->tableName();
    }

    // 获得子类的表名
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
        $modelClass = "ADatabase{$this->_db_config[$linkName]['DATABASE_TYPE']}";
        return new $modelClass($linkName);
    }

    /*
     * (non-PHPdoc) @see ADatabase::queryStr()
     */

    protected function query($strSQL)
    {
        $this->init();
        return $this->model->query($strSQL);
    }

    /*
     * (non-PHPdoc) @see Database::query()
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

    protected function setDbname($dbname)
    {
        $this->dbname = $dbname;
    }

    /*
     * (non-PHPdoc) @see Database::delete()
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
    protected function startAffair()
    {
        $this->init();
        $this->model->startAffair();
    }

    /**
     * 事务回滚
     */
    protected function rollBack()
    {
        $this->init();
        $this->model->rollBack();
    }

    /**
     * 事务提交
     */
    protected function commit()
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
     * @param type $condition
     * @param type $feild
     * @return type array
     */
    protected function find($condition = '', $feild = '*', $orderBy = '', $groupBy = '')
    {
        $this->init();
        //  echo get_class($this->model);
        return $this->model->fetch($this->getTableName(), $condition, $feild, $orderBy, $groupBy);
    }

    /**
     *
     * @param type $condition
     * @param type $feild
     * @param type $orderBy
     * @param string $limitString
     * @param type $groupBy
     * @return type
     */
    protected function findAll($condition = '', $feild = '*', $orderBy = '', $limitString = '', $groupBy = '')
    {
        $this->init();
        if (!is_array($condition)) {
            if (FALSE === strpos(strtolower($condition), 'limit') && empty($limitString)) {
                $limitString = '1000';
            }
        }
        return $this->model->fetchAll($this->getTableName(), $condition, $feild, $orderBy, $limitString, $groupBy);
    }

    /**
     *
     * @param type $feild
     * @param type $tableName
     * @param type $ignore
     * @return type
     */
    protected function add($feild, $tableName = '', $ignore = false)
    {
        $this->init();
        if ($tableName !== '') {
            $this->setTableName($tableName);
            return $this->model->add($feild, $tableName, $ignore);
        }
        return $this->model->add($feild, $this->getTableName(), $ignore);
    }

    /**
     *
     * @param type $feild
     * @param type $tableName
     * @return type
     */
    protected function replace($feild, $tableName = '')
    {
        $this->init();
        if ($tableName !== '') {
            $this->setTableName($tableName);
            return $this->model->replace($feild, $tableName);
        }
        return $this->model->replace($feild, $this->getTableName());
    }

    /**
     *
     * @param type $feild
     * @param type $tableName
     * @return type
     */
    protected function replaceBatch($feild, $tableName = '')
    {
        $this->init();
        if ($tableName !== '') {
            $this->setTableName($tableName);
            return $this->model->replaceBatch($feild, $tableName);
        }
        return $this->model->replaceBatch($feild, $this->getTableName());
    }

    /*
     * 批量插入数据 @see Database::addBatch()
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

    /*
     * @see Database::update()
     */

    protected function update($feild, $condition = '', $tableName = '', $limit = 0)
    {
        $this->init();
        if ($tableName != '') {
            $this->setTableName($tableName);
            return $this->model->update($feild, $tableName, $condition, $limit);
        }
        return $this->model->update($feild, $this->getTableName(), $condition, $limit);
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
     * @param String $newlink
     */
    protected function createConnection($linkName = null, $newlink = false)
    {
        if (empty($this->model)) {
// 获得当前数据库类型，默认数据库类型为Mysql
            $modelClass = $this->getDatabaseType($linkName);

            $this->model = new $modelClass($linkName);
        }
        return $this->model->createConnection($linkName, $newlink);
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
     * @param $param =
     *            array(
     *            'condition'=>一维数组或者字符串，默认为空字符串,//检索条件
     *            'orderBy'=>排序方式,默认为空字符串。如果有值，则只需要加上需要排序的字段名称 和排序方式如： 'id DESC'
     *            'limit'=> 查询条数LIMIT,默认为空字符串。如果有值，则只需要加上需要条数如： '0,10'-从第0条开始，向后查询10条
     *            )
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
     * @param Array $param
     * @param String $key
     */
    protected function setDefaultValueByKey($param, $key, $defaultType = '')
    {
        return isset($param [$key]) ? $param [$key] : $defaultType;
    }

    /**
     *
     * @return String $tablename
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
        return __NAMESPACE__ . "\database\ADatabase{$this->_db_config[$linkName]['DATABASE_TYPE']}";
    }

}

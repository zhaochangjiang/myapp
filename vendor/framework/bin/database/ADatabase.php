<?php

namespace framework\bin\database;

/**
 * 数据库规范接口
 *
 * @author zhaocj
 */
interface ADatabase
{

    /**
     * 创建数据库链接
     */
    public function createConnection();

    /**
     * 执行SQL语句
     */
    public function query($strSQL);

    /**
     * 根据SQL语句返回一组结果集
     */
    public function queryRow($strSQL);

    /**
     * 根据SQL语句返回所有结果集
     */
    public function queryAll($strSQL);

    /**
     * 查询出一条结果 返回一维数组
     *
     * @param $condition -一维Array
     *            or String查询条件
     * @param $tableName -
     *            String 表名字
     * @param $feild -Array
     *            or String查询的字段内容
     * @param $orderBy -
     *            String;
     */
    public function fetch($tableName, $condition, $feild = '*', $orderBy = '',
                          $groupBy = '', $having = '');

    /**
     *
     * @param
     *            $param
     * @param $type -Enum
     *            ('leftJoin','join','rightJoin')
     */
    public function fetchJoin($param, $type = 'leftJoin');

    /**
     * 返回一组结果集
     *
     * @param $tableName String
     *            表名字
     * @param $condition 查询条件
     * @param $feild -String
     *            or Array 查询的内容结果集
     * @param $orderBy -String
     *            排序方式
     * @param $limit 查询条数限制，默认查询1000条,空字符串表示查询所有可传参数格式Enum（'','10,20','10'）
     */
    public function fetchAll($tableName, $condition, $feild = '*',
                             $orderBy = '', $limit = '1000', $groupBy = '');

    /**
     * 向数据库添加一条数据
     *
     * @param
     *            $feild
     * @param
     *            $tableName
     * @return false 或者 主键
     */
    public function add($feild, $tableName);

    /**
     * 一次性添加多条数据
     *
     * @param $data -
     *            二维数据组
     * @param $table -
     *            表名
     */
    public function addBatch($data, $table);

    /**
     * 修改一条数据
     *
     * @param $feild -
     *            一维数据键值对
     * @param $table -
     *            String 表明
     * @param
     *            $condition
     * @return boolean
     */
    public function update($feild, $table, $condition = null, $limit = 0,
                           $order = '');

    /**
     * 开启数据库事务
     */
    public function startAffair();


    /**
     * 删除数据
     * @param type $table
     * @param type $where
     * @param type $limit
     * @param type $order
     */
    public function delete($table, $where, $limit = '', $order = '');

    /**
     * 事务回滚
     */
    public function rollBack();

    /**
     * 事务提交
     */
    public function commit();

    /**
     * 断开数据库链接
     */
    public function disconnect();
}
  
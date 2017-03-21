<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
return array(
    'version' => '1.0',
    'parameters' => array(
        'siteName' => '掘囤网',
        'domain' => array(
            'userProfile' => 'http://user.fanghuiju.com',
            'dashboard' => 'http://dashboard.fanghuiju.com',
            'web' => 'http://www.fanghuiju.com',
            'client' => 'http://client.fanghuiju.com'
        )
    ),
    'database' => array(
        'user' => array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => '123456',
            'prefix' => 'user_',
            'dbName' => 'juetun_user',
            'DATABASE_TYPE' => 'MysqlPDO'
        ),
        'admin' => array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => '123456',
            'prefix' => 'admin_',
            'dbName' => 'juetun_admin',
            'DATABASE_TYPE' => 'MysqlPDO'
        ),
        'data' => array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => '123456',
            'prefix' => 'data_',
            'dbName' => 'juetun_data',
            'DATABASE_TYPE' => 'MysqlPDO'
        ),
    )
);
  
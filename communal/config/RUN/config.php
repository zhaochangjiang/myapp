<?php

define('DOMAIN', 'http://' . $_SERVER['HTTP_HOST']);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
return array(

    'parameters' => array(
        'version'  => '1.0',
        'siteName' => '掘囤网',
        'domain'   => array(
            'userProfile' => DOMAIN . '/frontend/web',
            'dashboard'   => DOMAIN . '/backend/web',
            'web'         => DOMAIN . '/frontend/web',
            'client'      => DOMAIN . '/client/web'
        )
    ),
    'database' => array(
        'user' => array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => '123456',
            'prefix' => 'user_',
            'dbName' => 'baopou_user',
            'DATABASE_TYPE' => 'MysqlPDO'
        ),
        'admin' => array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => '123456',
            'prefix' => 'admin_',
            'dbName' => 'baopou_admin',
            'DATABASE_TYPE' => 'MysqlPDO'
        ),
        'data' => array(
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => '123456',
            'prefix' => 'data_',
            'dbName' => 'baopou_data',
            'DATABASE_TYPE' => 'MysqlPDO'
        ),
    )
);
  
<?php

class Catalog_OrderModel extends Model {

    public $_timestamps = true;
    
    public $_hasMany = array('catalog.orderitem');

    public $_fields = array(
        'first_name' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'last_name' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'email' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'phone' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'address' => array(
            'type' => 'varchar',
            'length' => 255, 
            'not null' => true
        ),
        'city' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'state' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'zip' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'country' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'status' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        )
    );

    public $_indexes = array('status');

}

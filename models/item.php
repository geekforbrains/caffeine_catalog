<?php

class Catalog_ItemModel extends Model {

    public $_belongsTo = array('catalog.category');

    public $_hasAndBelongsToMany = array('media.file');

    public $_fields = array(
        'slug' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'name' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'description' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => true
        ),
        'price' => array(
            'type' => 'double',
            'not null' => true
        )
    );

}

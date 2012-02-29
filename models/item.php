<?php

class Catalog_ItemModel extends Model {

    public $_belongsTo = array('catalog.category');

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
        'blurb' => array(
            'type' => 'text',
            'size' => 'small',
            'not null' => true
        ),
        'description' => array(
            'type' => 'text',
            'size' => 'normal',
            'not null' => true
        ),
        'price' => array(
            'type' => 'double',
            'not null' => true
        )
    );

    public $_fulltext = array('name', 'blurb', 'description');

}

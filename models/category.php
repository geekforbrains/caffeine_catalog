<?php

class Catalog_CategoryModel extends Model {

    public $_hasMany = array('catalog.items');

    public $_belongsTo = array('catalog.category'); // Parent categories

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
        'weight' => array( // Used for sorting
            'type' => 'int',
            'not null' => true
        )
    );

    public $_indexes = array('slug', 'weight');

}

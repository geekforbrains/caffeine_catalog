<?php

class Catalog_OrderItemModel extends Model {

    public $_belongsTo = array('catalog.order', 'catalog.item');

    public $_fields = array(
        'quantity' => array(
            'type' => 'int',
            'not null' => true
        )
    );

}

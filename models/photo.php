<?php

class Catalog_PhotoModel extends Model {

    public $_belongsTo = array('catalog.item', 'media.file');

}

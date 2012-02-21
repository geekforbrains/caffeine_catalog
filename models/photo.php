<?php

class Catalog_PhotoModel extends Model {

    public $_belongsTo = array('media.file', 'catalog.item');

}

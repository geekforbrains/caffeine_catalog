<?php

class Catalog_FileModel extends Model {

    public $_belongsTo = array('catalog.item', 'media.file');

}

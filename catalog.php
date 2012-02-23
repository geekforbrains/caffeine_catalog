<?php

class Catalog extends Module {

    /**
     * Converts categories to an array that has been formatted for forms.
     */
    public static function getSortedCategories($useNone = true)
    {
        $categories = Catalog::category()->orderBy('name')->all();
        $sortedCategories = ($useNone) ? array(0 => 'None') : array('' => 'Choose One');

        if($categories)
        {
            MultiArray::load($categories, 'category_id');
            $indentedCategories = MultiArray::indent();

            if($indentedCategories)
                foreach($indentedCategories as $c)
                    $sortedCategories[$c->id] = $c->indent . $c->name;
        }

        return $sortedCategories;
    }

    /**
     * Gets all categories, sorted by name.
     */
    public static function getCategories() {
        return Catalog::category()->orderBy('name')->all();
    }

    /**
     * Gets all items, and adds photos and fields properties to each object.
     */
    public static function getItems()
    {
        $items = Catalog::item()
            ->select('catalog_items.*, catalog_categories.name AS category')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
            ->orderBy('catalog_categories.name, catalog_items.name', 'ASC')
            ->all();

        if($items)
            $items = self::getItemData($items);

        return $items;
    }

    public static function getItemsByCategoryId($categoryId)
    {
        $items = Catalog::item()
            ->select('catalog_items.*, catalog_categories.name AS category')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
            ->where('catalog_categories.id', '=', $categoryId)
            ->orderBy('catalog_categories.name, catalog_items.name', 'ASC')
            ->all();

        if($items)
            $items = self::getItemData($items);

        return $items;
    }

    public static function getItemsByCategorySlug($categorySlug)
    {
        $items = Catalog::item()
            ->select('catalog_items.*, catalog_categories.name AS category')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
            ->where('catalog_categories.slug', 'LIKE', $categorySlug)
            ->orderBy('catalog_categories.name, catalog_items.name', 'ASC')
            ->all();

        if($items)
            $items = self::getItemData($items);

        return $items;
    }

    public static function getItemById($id)
    {
        $item = Catalog::item()
            ->select('catalog_items.*, catalog_categories.name AS category')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
            ->where('catalog_items.id', '=', $id)
            ->first();

        if($item)
            $item = self::getItemData($item);

        return $item;
    }

    public static function getItemBySlug($slug)
    {
        $item = Catalog::item()
            ->select('catalog_items.*, catalog_categories.name AS category')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
            ->where('catalog_items.slug', 'LIKE', $slug)
            ->first();

        if($item)
            $item = self::getItemData($item);

        return $item;

    }
    
    public static function getItemData($item)
    {
        if(is_array($item))
        {
            foreach($item as &$i)
                $i = self::getItemData($i);
        }
        else
        {
            $item->photos = Catalog::photo()->where('item_id', '=', $item->id)->all();
            $item->files = Catalog::file()->where('item_id', '=', $item->id)->all();
        }

        return $item;
    }

}

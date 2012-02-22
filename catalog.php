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
    public static function getCategories()
    {

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
        {
            foreach($items as &$i)
            {
                $i->photos = Catalog::photo()->where('item_id', '=', $i->id)->all();
                $i->files = Catalog::file()->where('item_id', '=', $i->id)->all();
            }
        }

        return $items;
    }

    public static function getItemsByCategoryId($categoryId)
    {

    }

    public static function getItemsByCategorySlug($categorySlug)
    {

    }

    public static function getItemById($id)
    {

    }

    public static function getItemBySlug($slug)
    {

    }

}

<?php

class Catalog extends Module {

    /**
     * Converts categories to an array that has been formatted for forms.
     */
    public static function getSortedCategories($useNone = true)
    {
        $categories = Catalog::category()->orderBy('weight')->orderBy('name')->all();
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
     * Gets order statuses from config and turns them into an array compatbile
     * with the form builder.
     */
    public static function getSortedStatuses($prepend = array())
    {
        $config = Config::get('catalog.statuses'); 
        $statuses = array();

        if($prepend)
            $statuses = array_merge($prepend, $statuses);

        foreach($config as $status)
            $statuses[$status] = ucfirst($status);

        return $statuses;
    }

    /**
     * Returns the number of items currently in cart.
     */
    public static function getCartCount()
    {
        $count = 0;
        $key = Config::get('catalog.cart_session');

        if(isset($_SESSION[$key]) && $items = $_SESSION[$key])
        {
            foreach($items as $id => $qty)
                $count += $qty;
        }

        return $count;
    }

    /**
     * Gets all categories, sorted by name.
     */
    public static function getCategories() {
        return Catalog::category()->orderBy('weight')->orderBy('name')->all();
    }

    /**
     * Gets a single level (no sub-categories) of categories that are related to the given
     * parent id. Passing 0 will get top-level categories.
     *
     * @param int $parentId The parent id of categories to get.
     */
    public static function getCategoriesByParentId($parentId = 0) {
        return Catalog::category()->where('category_id', '=', $parentId)->orderBy('weight')->orderBy('name')->all();
    }

    /**
     * Gets the parent category for the given child catgory id.
     *
     * @param int $childCategoryId The child category id to get the parent category for.
     */
    public static function getParentCategoryByChildId($childCategoryId)
    {
        $child = Catalog::category()->find($childCategoryId);
        return Catalog::category()->find($child->category_id); // Get category by parent id
    }

    /** 
     * Returns an array of parent categories associated with the given category id. This is
     * used for making cookie crumb type navigation. The given category id is also included as the
     * last array element.
     */
    public static function getCategoryChain($categoryId, $chain = array())
    {
        $category = Catalog::category()->find($categoryId);
        array_unshift($chain, $category);
    
        if($category->category_id > 0)
            $chain = self::getCategoryChain($category->category_id, $chain);

        return $chain;
    }

    /**
     * Gets all items, and adds photos and fields properties to each object.
     */
    public static function getItems()
    {
        $items = Catalog::item()
            ->select('catalog_items.*, catalog_categories.name AS category')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
            ->orderBy('catalog_categories.weight')
            ->orderBy('catalog_categories.name')
            ->orderBy('catalog_items.name')
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
            ->orderBy('catalog_categories.weight')
            ->orderBy('catalog_categories.name')
            ->orderBy('catalog_items.name')
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
            ->orderBy('catalog_categories.weight')
            ->orderBy('catalog_categories.name')
            ->orderBy('catalog_items.name')
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

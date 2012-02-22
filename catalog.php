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

}

<?php

class Catalog_Admin_CategoriesController extends Controller {

    /**
     * Displays a table of created categories.
     *
     * Route: admin/categories/manage
     */
    public static function manage()
    {
        if(isset($_POST['update_order']))
        {
            foreach($_POST['weight'] as $id => $value)
            {
                Catalog::category()->where('id', '=', $id)->update(array(
                    'weight' => intval($value)
                ));
            }
        }

        $table = Html::table();
        $header = $table->addHeader();
        $header->addCol('Category', array('colspan' => 3));

        $categories = Catalog::category()->orderBy('weight')->orderBy('name')->all();

        MultiArray::load($categories, 'category_id');
        $indentedCategories = MultiArray::indent();

        if($indentedCategories)
        {
            foreach($indentedCategories as $category)
            {
                $row = $table->addRow(); 
                $row->addCol($category->indent . Html::a()->get($category->name, 'admin/catalog/categories/edit/' . $category->id));
                $row->addCol('<input type="text" name="weight[' . $category->id . ']" 
                    value="' . $category->weight . '" maxlength="2" size="2" />');
                $row->addCol(
                    Html::a()->get('Delete', 'admin/catalog/categories/delete/' . $category->id),
                    array('class' => 'right')
                );
            }
        }
        else
            $table->addRow()->addCol('<em>No categories.</em>', array('colspan' => 2));

        $html = Html::form()->open(null, 'post', false, array('name' => 'sort', 'id' => 'sort'));
        $html .= $table->render();
        $html .= '<div class="buttons">';
        $html .= '<input type="hidden" name="update_order" />';
        $html .= '<a class="btn blue" href="javascript:document.sort.submit(); return false;">Update Order</a>';
        $html .= '</div>';
        $html .= Html::form()->close();

        return array(
            'title' => 'Manage Categories',
            'content' => $html
        );
    }

    /**
     * Displays a form for creating new categories.
     *
     * Route: admin/categories/create
     */
    public static function create()
    {
        if($_POST && Html::form()->validate())
        {
            if(!Catalog::category()->where('name', 'LIKE', $_POST['name'])->first())
            {
                $id = Catalog::category()->insert(array(
                    'category_id' => $_POST['category_id'],
                    'slug' => String::slugify($_POST['name']),
                    'name' => $_POST['name']
                ));

                if($id)
                {
                    Message::ok('Category created successfully.');
                    $_POST = array(); // Clear form
                }
                else
                    Message::error('Error creating category. Please try again.');
            }
            else
                Message::error('A category with that name already exists.');
        }

        $form[] = array(
            'fields' => array(
                'category_id' => array(
                    'title' => 'Parent Category',
                    'type' => 'select',
                    'options' => Catalog::getSortedCategories(),
                    'validate' => array('required')
                ),
                'name' => array(
                    'title' => 'Name',
                    'type' => 'text',
                    'validate' => array('required')
                ),
                'create_category' => array(
                    'type' => 'submit',
                    'value' => 'Create Category'
                )
            )
        );

        return array(
            'title' => 'Create Category',
            'content' => Html::form()->build($form)
        );
    }

    /**
     * Displays an edit for for a category.
     *
     * Route: admin/categories/edit/:id
     *
     * @param int $id The id of the category to edit.
     */
    public static function edit($id)
    {
        if(!$category = Catalog::category()->find($id))
            return ERROR_NOTFOUND;

        if($_POST && Html::form()->validate())
        {
            $status = Catalog::category()->where('id', '=', $id)->update(array(
                'category_id' => $_POST['category_id'],
                'slug' => String::slugify($_POST['name']),
                'name' => $_POST['name']
            ));

            if($status > 0)
            {
                Message::ok('Category updated successfully.');
                $category = Catalog::category()->find($id);
            }
            elseif($status == 0)
                Message::info('Nothing changed.');
            else
                Message::error('Error updating category, please try again.');
        }

        $form[] = array(
            'fields' => array(
                'category_id' => array(
                    'title' => 'Parent Category',
                    'type' => 'select',
                    'options' => Catalog::getSortedCategories(),
                    'selected' => $category->category_id
                ),
                'name' => array(
                    'title' => 'Name',
                    'type' => 'text',
                    'validate' => array('required'),
                    'default_value' => $category->name
                ),
                'update_category' => array(
                    'type' => 'submit',
                    'value' => 'Update Category'
                )
            )
        );

        return array(
            'title' => 'Edit Category',
            'content' => Html::form()->build($form)
        );
    }

    /**
     * Deletes a category based on id and redirects to the manage page.
     *
     * Route: admin/categories/delete/:id
     *
     * @param int $id The id of the category to delete.
     */
    public static function delete($id)
    {
        $parentId = Catalog::category()->find($id)->category_id;

        if(Catalog::category()->delete($id))
        {
            // Set any child categories of this category to be under this categories parent
            Catalog::category()->where('category_id', '=', $id)->update(array(
                'category_id' => $parentId
            ));

            Message::ok('Category deleted successfully.');
        }
        else
            Message::error('Error deleting category. Please try again.');

        Url::redirect('admin/catalog/categories/manage');
    }

}

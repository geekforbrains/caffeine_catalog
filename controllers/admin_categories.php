<?php

class Catalog_Admin_CategoriesController extends Controller {

    /**
     * Displays a table of created categories.
     *
     * Route: admin/categories/manage
     */
    public static function manage()
    {
        $table = Html::table();
        $header = $table->addHeader();
        $header->addCol('Category', array('colspan' => 2));

        $categories = Catalog::category()->orderBy('name')->all();

        if($categories)
        {
            foreach($categories as $category)
            {
                $row = $table->addRow(); 
                $row->addCol(Html::a()->get($category->name, 'admin/catalog/categories/edit/' . $category->id));
                $row->addCol(
                    Html::a()->get('Delete', 'admin/catalog/categories/delete/' . $category->id),
                    array('class' => 'right')
                );
            }
        }
        else
            $table->addRow()->addCol('<em>No categories.</em>', array('colspan' => 2));

        return array(
            'title' => 'Manage Categories',
            'content' => $table->render()
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
                'name' => array(
                    'title' => 'Name',
                    'type' => 'text',
                    'validate' => array('required')
                ),
                'submit' => array(
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
                'name' => array(
                    'title' => 'Name',
                    'type' => 'text',
                    'validate' => array('required'),
                    'default_value' => $category->name
                ),
                'submit' => array(
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
        if(Catalog::category()->delete($id))
            Message::ok('Category deleted successfully.');
        else
            Message::error('Error deleting category. Please try again.');

        Url::redirect('admin/catalog/categories/manage');
    }

}

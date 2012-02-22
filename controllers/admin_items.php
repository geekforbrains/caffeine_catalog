<?php

class Catalog_Admin_ItemsController extends Controller {

    /**
     * Shows a table of items, filterable by category.
     *
     * Route: admin/catalog/items/manage
     */
    public static function manage()
    {
        $output = array();

        $filterForm[] = array(
            'fields' => array(
                'category_id' => array(
                    'title' => 'Category',
                    'type' => 'select',
                    'options' => Catalog::getSortedCategories()
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => 'Filter'
                )
            )
        );

        $output[] = array(
            'title' => 'Filter Items',
            'content' => Html::form()->build($filterForm)
        );

        $table = Html::table();
        $header = $table->addHeader();
        $header->addCol('Item');
        $header->addCol('Category', array('colspan' => 2));

        $items = Catalog::item()
            ->select('catalog_items.*, catalog_categories.name AS category')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
            ->orderBy('catalog_categories.name, catalog_items.name', 'ASC')
            ->all();

        if($items)
        {
            foreach($items as $i)
            {
                $row = $table->addRow();
                $row->addCol(Html::a()->get($i->name, 'admin/catalog/items/edit/' . $i->id));
                $row->addCol($i->category);
                $row->addCol(Html::a()->get('Delete', 'admin/catalog/items/delete/' . $i->id), array('class' => 'right'));
            }
        }
        else
            $table->addRow()->addCol('<em>No items.</em>', array('colspan' => 3));

        $output[] = array(
            'title' => 'Manage Items',
            'content' => $table->render()
        );

        return $output;
    }

    /**
     * Displays a form for creating catalog items.
     * 
     * Route: admin/catalog/items/create
     */
    public static function create()
    {
        if($_POST && Html::form()->validate())
        {
            $id = Catalog::item()->insert(array(
                'category_id' => $_POST['category_id'],
                'slug' => String::slugify($_POST['name']),
                'name' => $_POST['name'],
                'price' => $_POST['price'],
                'description' => $_POST['description']
            ));

            if($id)
            {
                Message::ok('Item created successfully.');
                Url::redirect('admin/catalog/items/edit/' . $id);
            }
            else
                Message::error('Error creating item, please try again.');
        }

        $form[] = array(
            'fields' => array(
                'category_id' => array(
                    'title' => 'Category',
                    'type' => 'select',
                    'options' => Catalog::getSortedCategories(false),
                    'validate' => array('required')
                ),
                'name' => array(
                    'title' => 'Name',
                    'type' => 'text',
                    'validate' => array('required')
                ),
                'price' => array(
                    'title' => 'Price',
                    'type' => 'text',
                    'validate' => array('numeric'),
                    'default_value' => '0.00'
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea'
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => 'Create Item'
                )
            )
        );

        return array(
            'title' => 'Create Item',
            'content' => Html::form()->build($form)
        );
    }

    public static function edit($id)
    {
        if(!$item = Catalog::item()->find($id))
            return ERROR_NOTFOUND;

        $form[] = array(
            'fields' => array(
                'category_id' => array(
                    'title' => 'Category',
                    'type' => 'select',
                    'options' => Catalog::getSortedCategories(false),
                    'validate' => array('required'),
                    'selected' => $item->category_id
                ),
                'name' => array(
                    'title' => 'Name',
                    'type' => 'text',
                    'validate' => array('required'),
                    'default_value' => $item->name
                ),
                'price' => array(
                    'title' => 'Price',
                    'type' => 'text',
                    'validate' => array('numeric'),
                    'default_value' => $item->price
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'default_value' => $item->description
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => 'Update Item'
                )
            )
        );

        $photoForm[] = array(
            'fields' => array(
                'photo' => array(
                    'title' => 'Choose a Photo',
                    'type' => 'file'
                ),
                'upload_photo' => array(
                    'type' => 'submit',
                    'value' => 'Upload Photo'
                )
            )
        );

        $table = Html::table();
        $table->addHeader('Photo', array('colspan' => 2));

        $photos = Db::table('files_items')->where('item_id', '=', $id)->all();

        if($photos)
        {
            foreach($photos as $p)
            {
                $row = $table->addRow();
                $row->addCol(Html::img()->getMedia($p->file_id, 0, 75, 75));
                $row->addCol('Delete', array('class' => 'right'));
            }
        }
        else
            $table->addRow()->addCol('<em>No photos.</em>', array('colspan' => 2));

        return array(
            array(
                'title' => 'Edit Item',
                'content' => Html::form()->build($form)
            ),
            array(
                'title' => 'Upload Photo',
                'content' => Html::form()->build($photoForm, null, 'post', true) // multipart
            ),
            array(
                'title' => 'Manage Photos',
                'content' => $table->render()
            )
        );
    }

    public static function delete($id)
    {

    }

}

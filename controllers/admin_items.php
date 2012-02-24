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

        // If filter form was submitted, get items with associated category
        if($_POST && $_POST['category_id'] > 0)
        {
            $items = Catalog::item()
                ->select('catalog_items.*, catalog_categories.name AS category')
                ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
                ->where('catalog_items.category_id', '=', $_POST['category_id'])
                ->orderBy('catalog_categories.name, catalog_items.name', 'ASC')
                ->all();
        }
        else
        {
            $items = Catalog::item()
                ->select('catalog_items.*, catalog_categories.name AS category')
                ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_items.category_id')
                ->orderBy('catalog_categories.name, catalog_items.name', 'ASC')
                ->all();
        }

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
                'create_item' => array(
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

    /**
     * Displays an edit form for an item, an upload form for photos and a table for
     * managing uploaded photos.
     *
     * Route: admin/catalog/items/edit/:id
     *
     * @param int $id The id of the item to edit.
     */
    public static function edit($id)
    {
        if(!$item = Catalog::item()->find($id))
            return ERROR_NOTFOUND;

        // Edit item form posted
        if(isset($_POST['update_item']))
        {
            $status = Catalog::item()->where('id', '=', $id)->update(array(
                'category_id' => $_POST['category_id'],
                'slug' => String::slugify($_POST['name']),
                'name' => $_POST['name'],
                'price' => $_POST['price'],
                'description' => $_POST['description']
            ));

            if($status > 0)
            {
                Message::ok('Item updated successfully.');
                $item = Catalog::item()->find($id);
            }
            elseif($status == 0)
                Message::info('Nothing changed.');
            else
                Message::error('Error updating item, please try again.');
        }

        // Upload photo form posted
        if(isset($_POST['upload_photo']))
        {
            $photo = Media::image()->save('photo');

            if(!$photo->hasError())
            {
                $photoId = Catalog::photo()->insert(array(
                    'item_id' => $id,
                    'file_id' => $photo->getId()
                ));

                if($photoId)
                    Message::ok('Photo uploaded successfully.');
                else
                {
                    Media::delete($photo->getId());
                    Message::error('Unkown error uploading photo, please try again.');
                }
            }
            else
                Message::error($photo->getError());
        }

        // Upload file form posted
        if(isset($_POST['upload_file']))
        {
            $file = Media::file()->save('userfile');

            if(!$file->hasError())
            {
                $fileId = Catalog::file()->insert(array(
                    'item_id' => $id,
                    'file_id' => $file->getId()
                ));

                if($fileId)
                    Message::ok('File uploaded successfully.');
                else
                {
                    Media::delete($file->getId());
                    Message::error('Unkown error uploading file, please try again.');
                }
            }
            else
                Message::error($file->getError());
        }

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
                'update_item' => array(
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

        $photoTable = Html::table();
        $header = $photoTable->addHeader();
        $header->addCol('Photo', array('colspan' => 2));

        $photos = Catalog::photo()->where('item_id', '=', $id)->all();

        if($photos)
        {
            foreach($photos as $p)
            {
                $row = $photoTable->addRow();
                $row->addCol(Html::img()->getMedia($p->file_id, 0, 75, 75));
                $row->addCol(
                    Html::a()->get('Delete', 'admin/catalog/items/edit/' . $id . '/delete-photo/' . $p->id),
                    array('class' => 'right')
                );
            }
        }
        else
            $photoTable->addRow()->addCol('<em>No photos.</em>', array('colspan' => 2));

        $fileForm[] = array(
            'fields' => array(
                'userfile' => array(
                    'title' => 'Choose a File',
                    'type' => 'file'
                ),
                'upload_file' => array(
                    'type' => 'submit',
                    'value' => 'Upload File'
                )
            )

        );

        $fileTable = Html::table();
        $header = $fileTable->addHeader();
        $header->addCol('File', array('colspan' => 2));

        $files = Catalog::file()->where('item_id', '=', $id)->all();

        if($files)
        {
            foreach($files as $f)
            {
                $media = Media::m('file')->find($f->file_id);

                $row = $fileTable->addRow();
                $row->addCol(Html::a()->get($media->name, Media::file()->getUrl($media->id, false)));
                $row->addCol(
                    Html::a()->get('Delete', 'admin/catalog/items/edit/' . $id . '/delete-file/' . $f->id),
                    array('class' => 'right')
                );
            }
        }
        else
            $fileTable->addRow()->addCol('<em>No files.</em>', array('colspan' => 2));

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
                'content' => $photoTable->render()
            ),
            array(
                'title' => 'Upload File',
                'content' => Html::form()->build($fileForm, null, 'post', true) // multipart
            ),
            array(
                'title' => 'Manage Files',
                'content' => $fileTable->render()
            )
        );
    }

    /**
     * Deletes an items photo and redirects back to edit item page.
     *
     * Route: admin/catalog/items/edit/:id/delete-photo/:id
     *
     * @param int $itemId The item id the photo is associated with
     * @param int $photoId The id of the photo to delete.
     */
    public static function deletePhoto($itemId, $photoId)
    {
        if(!$photo = Catalog::photo()->find($photoId))
            return ERROR_NOTFOUND;

        Media::delete($photo->file_id);

        if(Catalog::photo()->delete($photoId))
            Message::ok('Photo deleted successfully.');
        else
            Message::error('Error deleting photo, please try again.');

        Url::redirect('admin/catalog/items/edit/' . $itemId);
    }

    /**
     * Deletes an items file and redirects back to edit item page.
     *
     * Route: admin/catalog/items/edit/:id/delete-file/:id
     *
     * @param int $itemId The item id the photo is associated with
     * @param int $fileId The id of the file to delete.
     */
    public static function deleteFile($itemId, $fileId)
    {
        if(!$file = Catalog::file()->find($fileId))
            return ERROR_NOTFOUND;

        Media::delete($file->file_id);

        if(Catalog::file()->delete($fileId))
            Message::ok('File deleted successfully.');
        else
            Message::error('Error deleting file, please try again.');

        Url::redirect('admin/catalog/items/edit/' . $itemId);
    }

    /**
     * Deletes an item and all photos and files associated with it.
     *
     * Route: admin/catalog/items/delete/:id
     *
     * @param int $id The id of the item to delete.
     */
    public static function delete($id)
    {
        // Get photos and delete any
        if($photos = Catalog::photo()->where('item_id', '=', $id)->all())
            foreach($photos as $p)
                Media::delete($p->file_id);

        // Get files and delete any
        if($files = Catalog::file()->where('item_id', '=', $id)->all())
            foreach($files as $f)
                Media::delete($f->file_id);

        // Clear photo and file records
        Catalog::photo()->where('item_id', '=', $id)->delete();
        Catalog::file()->where('item_id', '=', $id)->delete();

        // Delete actual record
        if(Catalog::item()->delete($id))
            Message::ok('Item and associated files deleted successfully.');
        else
            Message::error('Error deleting item, please try again.');

        Url::redirect('admin/catalog/items/manage');
    }

}

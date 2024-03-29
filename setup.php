<?php return array(

    'configs' => array(
        'catalog.statuses' => array('new', 'complete', 'cancelled'), // Must be single words, lower-case
        'catalog.default_status' => 'new',
        'catalog.cart_session' => 'catalog_items',
        'catalog.order_session' => 'catalog_order'
    ),

    'routes' => array(
        // Front
        'catalog' => array(
            'title' => 'Catalog',
            'callback' => array('catalog', 'items')
        ),
        'catalog/search' => array(
            'title' => 'Search',
            'callback' => array('catalog', 'search')
        ),
        'catalog/category/:slug' => array(
            'title' => 'Category',
            'callback' => array('catalog', 'category')
        ),
        'catalog/item/:slug' => array(
            'title' => 'Item',
            'callback' => array('catalog', 'item')
        ),
        'catalog/cart' => array(
            'title' => 'Cart',
            'callback' => array('catalog', 'cart')
        ),
        'catalog/cart/delete/:id' => array(
            'callback' => array('catalog', 'deleteFromCart')
        ),
        'catalog/checkout' => array(
            'title' => 'Checkout',
            'callback' => array('catalog', 'checkout')
        ),
        'catalog/checkout/complete' => array(
            'title' => 'Checkout Complete',
            'callback' => array('catalog', 'complete')
        ),

        // Admin
        'admin/catalog' => array(
            'title' => 'Catalog',
            'redirect' => 'admin/catalog/orders',
            'permissions' => array('catalog.admin_catalog')
        ),

        // Admin Orders
        'admin/catalog/orders' => array(
            'title' => 'Orders',
            'redirect' => 'admin/catalog/orders/manage',
            'permissions' => array('catalog.admin_orders')
        ),
        'admin/catalog/orders/manage' => array(
            'title' => 'Manage',
            'callback' => array('admin_orders', 'manage'),
            'permissions' => array('catalog.manage_orders')
        ),
        'admin/catalog/orders/details/:id' => array(
            'title' => 'Details',
            'callback' => array('admin_orders', 'details'),
            'permissions' => array('catalog.view_order_details')
        ),
        'admin/catalog/orders/delete/:id' => array(
            'callback' => array('admin_orders', 'delete'),
            'permissions' => array('catalog.delete_orders')
        ),

        // Admin Items
        'admin/catalog/items' => array(
            'title' => 'Items',
            'redirect' => 'admin/catalog/items/manage',
            'permissions' => array('catalog.admin_items')
        ),
        'admin/catalog/items/manage' => array(
            'title' => 'Manage',
            'callback' => array('admin_items', 'manage'),
            'permissions' => array('catalog.manage_items')
        ),
        'admin/catalog/items/create' => array(
            'title' => 'Create',
            'callback' => array('admin_items', 'create'),
            'permissions' => array('catalog.create_items')
        ),
        'admin/catalog/items/edit/:id' => array(
            'title' => 'Edit',
            'callback' => array('admin_items', 'edit'),
            'permissions' => array('catalog.edit_items')
        ),
        'admin/catalog/items/edit/:id/delete-photo/:id' => array(
            'callback' => array('admin_items', 'deletePhoto'),
            'permissions' => array('catalog.delete_item_photos')
        ),
        'admin/catalog/items/edit/:id/delete-file/:id' => array(
            'callback' => array('admin_items', 'deleteFile'),
            'permissions' => array('catalog.delete_item_files')
        ),
        'admin/catalog/items/delete/:id' => array(
            'callback' => array('admin_items', 'delete'),
            'permissions' => array('catalog.delete_items')
        ),

        // Admin Categories
        'admin/catalog/categories' => array(
            'title' => 'Categories',
            'redirect' => 'admin/catalog/categories/manage',
            'permissions' => array('catalog.admin_categories')
        ),
        'admin/catalog/categories/manage' => array(
            'title' => 'Manage',
            'callback' => array('admin_categories', 'manage'),
            'permissions' => array('catalog.manage_categories')
        ),
        'admin/catalog/categories/create' => array(
            'title' => 'Create',
            'callback' => array('admin_categories', 'create'),
            'permissions' => array('catalog.create_categories')
        ),
        'admin/catalog/categories/edit/:id' => array(
            'title' => 'Edit',
            'callback' => array('admin_categories', 'edit'),
            'permissions' => array('catalog.edit_categories')
        ),
        'admin/catalog/categories/delete/:id' => array(
            'callback' => array('admin_categories', 'delete'),
            'permissions' => array('catalog.delete_categories')
        )
    ),

    'events' => array(
        'multilanguage.modules' => function() {
            return 'catalog';
        },

        'multilanguage.content[catalog]' => function()
        {
            $content = array('category' => array(), 'item' => array());
            $categories = Catalog::category()->orderBy('name')->all();
            $items = Catalog::item()->all();

            if($categories)
                foreach($categories as $c)
                    $content['category'][$c->id] = $c->name;

            if($items)
                foreach($items as $i)
                    $content['item'][$i->id] = $i->name;

            return $content;
        },

        'multilanguage.content_type[catalog][category]' => function()
        {
            return array(
                'name' => 'text'
            );
        },

        'multilanguage.content_type[catalog][item]' => function()
        {
            return array(
                'name' => 'text',
                'description' => 'textarea',
                'price' => 'text'
            );
        }
    )

);

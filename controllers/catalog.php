<?php

class Catalog_CatalogController extends Controller {

    /**
     * Performs a search based on keywords, searches catalog item descriptions.
     *
     * TODO Make this use FULLTEXT
     */
    public static function search()
    {
        $items = null;

        if(isset($_POST['keywords']))
        {
            $keywords = '%' . str_replace(' ', '%', $_POST['keywords']) . '%';
            $items = Catalog::item()
                ->distinct()
                ->where('description', 'LIKE', $keywords)
                ->orWhere('blurb', 'LIKE', $keywords)
                ->orWhere('name', 'LIKE', $keywords)
                ->all();
            
            if($items)
                foreach($items as &$i)
                    $i = Catalog::getItemData($i);
        }
        else
            Url::redirect('catalog');

        View::data('keywords', strip_tags($_POST['keywords']));
        View::data('resultCount', count($items));
        View::data('items', $items);
    }

    /**
     * Displays all items associated with the current category, and any sub-categories
     * where this is the parent category.
     *
     * @param string $slug The slug of the category to get.
     */
    public static function category($slug)
    {
        if(!$category = Catalog::category()->find($slug))
            return ERROR_NOTFOUND;

        $subCategories = Catalog::getCategoriesByParentId($category->id);
        $items = Catalog::getItemsByCategoryId($category->id);

        View::data('category', $category);
        View::data('subCategories', $subCategories);
        View::data('items', $items);
    }

    /**
     * Gets all items ordered by catalog name, then by item name ascending.
     *
     * Route: catalog
     */
    public static function items() {
        View::data('items', Catalog::getItems()); 
    }

    /**
     * Gets an item based on its slug.
     *
     * Route: catalog/item/:slug
     *
     * @param string $slug The slug of the item to get.
     */
    public static function item($slug)
    {
        if(!$item = Catalog::getItemBySlug($slug))
            return ERROR_NOTFOUND;

        View::data('item', $item);
    }

    /**
     * Displays the cart with any products added to it. Also handles updating
     * quantities.
     *
     * Route: catalog/cart
     */
    public static function cart()
    {
        $sess =& self::_getCartSession();

        // Handle products being added to cart
        if(isset($_POST['add_to_cart']))
        {
            if(isset($sess[$_POST['item_id']]))
                $sess[$_POST['item_id']] += $_POST['qty'];
            else
                $sess[$_POST['item_id']] = $_POST['qty'];
        }

        // Handle updates to qty in cart
        if(isset($_POST['update_cart']))
        {
            foreach($_POST['qty'] as $id => $qty)
            {
                if($qty <= 0)
                    unset($sess[$id]);
                else
                    $sess[$id] = $qty;
            }
        }

        $items = array();

        foreach($sess as $itemId => $qty)
        {
            $item = Catalog::getItemById($itemId);
            $item->qty = $qty;
            $items[] = $item;
        }

        View::data('items', $items);
    }

    /**
     * Deletes an item from the cart via url (items can also be deleted by setting their qty to 0).
     *
     * Route: catalog/cart/delete/:id
     *
     * @param int $id The id of the product to remove from the cart
     */
    public static function deleteFromCart($id)
    {
        $sess =& self::_getCartSession();

        if(isset($sess[$id]))
            unset($sess[$id]);

        Url::redirect('catalog/cart');
    }

    /**
     * Shows a form for entering personal details to complete the checkout process. Will redirect
     * to cart if no items exist in cart session.
     *
     * Route: catalog/checkout
     */
    public static function checkout()
    {
        if(self::_cartIsEmpty())
            Url::redirect('catalog/cart');

        if(isset($_POST['checkout']))
        {
            Validate::check('first_name', array('required'));
            Validate::check('last_name', array('required'));
            Validate::check('email', array('required', 'email'));
            Validate::check('phone', array('required'));
            Validate::check('address', array('required'));
            Validate::check('city', array('required'));
            Validate::check('state', array('required'));
            Validate::check('zip', array('required'));
            Validate::check('country', array('required'));

            if(Validate::passed())
            {
                $orderId = Catalog::order()->insert(array(
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'address' => $_POST['address'],
                    'city' => $_POST['city'],
                    'state' => $_POST['state'],
                    'zip' => $_POST['zip'],
                    'country' => $_POST['country'],
                    'status' => Config::get('catalog.default_status')
                ));

                if($orderId)
                {
                    // Add items from cart to new order
                    $items = self::_getCartSession();

                    foreach($items as $itemId => $qty)
                    {
                        Catalog::orderitem()->insert(array(
                            'order_id' => $orderId,
                            'item_id' => $itemId,
                            'quantity' => $qty
                        ));
                    }

                    $order =& self::_getOrderSession();
                    $order = $orderId;

                    Url::redirect('catalog/checkout/complete');
                }
                else
                    Message::error('Unkown error, please try again.');
            }
        }
    }

    /**
     * Shows a success/thankyou page for orders. The order id is passed in a session so we
     * can easily get order information for making a receipt etc.
     */
    public static function complete()
    {
        $orderId = self::_getOrderSession();

        if(self::_cartIsEmpty() || $orderId <= 0)
            Url::redirect('catalog/cart');

        $order = Catalog::order()->find($orderId);
        $orderItems = Catalog::orderitem()->where('order_id', '=', $order->id)->all();

        $items = array();
        foreach($orderItems as $i)
            $items[] = Catalog::getItemById($i->item_id);

        $order->items = $items;

        self::_clearSessions();
        View::data('order', $order);
    }

    /**
     * Checks if the cart is empty.
     *
     * @return boolean
     */
    private static function _cartIsEmpty()
    {
        if(!self::_getCartSession())
            return true;
        return false;
    }

    /**
     * Convenience method for getting the current cart session as a reference.
     */
    private static function &_getCartSession()
    {
        $key = Config::get('catalog.cart_session');

        if(!isset($_SESSION[$key]))
            $_SESSION[$key] = array();

        return $_SESSION[$key];
    }

    /**
     * Convenience method for getting the current checkout session as a reference.
     */
    private static function &_getOrderSession()
    {
        $key = Config::get('catalog.order_session');

        if(!isset($_SESSION[$key]))
            $_SESSION[$key] = 0;

        return $_SESSION[$key];
    }

    /**
     * Clears cart and order session.
     */
    private static function _clearSessions()
    {
        unset($_SESSION[Config::get('catalog.cart_session')]);
        unset($_SESSION[Config::get('catalog.order_session')]);
    }

}

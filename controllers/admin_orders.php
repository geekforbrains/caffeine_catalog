<?php

class Catalog_Admin_OrdersController extends Controller {

    /**
     * Displays a table of orders, that can be filtered by order status.
     *
     * Route: admin/catalog/orders/manage
     */
    public static function manage()
    {
        if(isset($_POST['filter']) && $_POST['status'] != 'all')
            $orders = Catalog::order()->where('status', '=', $_POST['status'])->orderBy('created_at', 'DESC')->all();
        else
            $orders = Catalog::order()->orderBy('created_at', 'DESC')->all();

        $table = Html::table();
        $header = $table->addHeader();
        $header->addCol('Order ID');
        $header->addCol('Date', array('colspan' => 2));

        if($orders)
        {
            foreach($orders as $o)
            {
                $row = $table->addRow();
                $row->addCol(Html::a()->get('#' . $o->id, 'admin/catalog/orders/details/' . $o->id));
                $row->addCol(date('M jS, Y', $o->created_at));
                $row->addCol(
                    Html::a()->get('Delete', 'admin/catalog/orders/delete/' . $o->id),
                    array('class' => 'right')
                );
            }
        }
        else
            $table->addRow()->addCol('<em>No orders.</em>', array('colspan' => 3));

        // Filter form
        $filterForm[] = array(
            'fields' => array(
                'status' => array(
                    'title' => null,
                    'type' => 'select',
                    'options' => Catalog::getSortedStatuses(array('all' => 'All'))
                ),
                'filter' => array(
                    'type' => 'submit',
                    'value' => 'Filter'
                )
            )
    
        );

        return array(
            'title' => 'Manage Orders',
            'content' => $table->render(),
            'topright' => Html::form()->build($filterForm)
        );
    }

    /**
     * Displays an orders details.
     *
     * Route: admin/catalog/orders/details/:id
     *
     * @param int $id The id of the order to get details for.
     */
    public static function details($id)
    {
        if(!$order = Catalog::order()->find($id))
            return ERROR_NOTFOUND;

        if(isset($_POST['update_status']))
        {
            $status = Catalog::order()->where('id', '=', $id)->update(array(
                'status' => $_POST['status']
            ));

            if($status)
            {
                Message::ok('Status updated successfully.');
                $order->status = $_POST['status']; // Update status for table
            }
            else
                Message::error('Error updating status, please try again.');
        }

        // Build table for order details
        $table = Html::table();
        $width = array('width' => '125');
        $tdTag = array('tag' => 'td');

        $table->addHeader()->addCol('Order #', $width)->addCol($order->id, $tdTag);
        $table->addHeader()->addCol('Status', $width)->addCol(ucfirst($order->status), $tdTag);
        $table->addHeader()->addCol('Name', $width)->addCol($order->first_name . ' ' . $order->last_name, $tdTag);
        $table->addHeader()->addCol('Email', $width)->addCol($order->email, $tdTag);
        $table->addHeader()->addCol('Phone', $width)->addCol($order->phone, $tdTag);
        $table->addHeader()->addCol('Address', $width)->addCol($order->address, $tdTag);
        $table->addHeader()->addCol('City', $width)->addCol($order->city, $tdTag);
        $table->addHeader()->addCol('State / Province', $width)->addCol($order->state, $tdTag);
        $table->addHeader()->addCol('Zip / Postal', $width)->addCol($order->zip, $tdTag);
        $table->addHeader()->addCol('Country', $width)->addCol($order->country, $tdTag);
        
        // Form for updating status
        $statusForm[] = array(
            'fields' => array(
                'status' => array(
                    'type' => 'select',
                    'options' => Catalog::getSortedStatuses()
                ),
                'update_status' => array(
                    'type' => 'submit',
                    'value' => 'Update Status'
                )
            )
        );

        // Build table of order items;
        $itemsTable = Html::table();
        $itemsTable->addHeader()->addCol('Item')->addCol('Quantity', array('class' => 'right'));

        $orderItems = Catalog::orderitem()
            ->select('catalog_orderitems.quantity, catalog_items.*')
            ->leftJoin('catalog_items', 'catalog_items.id', '=', 'catalog_orderitems.item_id')
            ->where('catalog_orderitems.order_id', '=', $id)
            ->all();

        if($orderItems)
        {
            foreach($orderItems as $item)
            {
                $row = $itemsTable->addRow();
                $row->addCol(Html::a()->get($item->name, 'admin/catalog/items/edit/' . $item->id));
                $row->addCol($item->quantity, array('class' => 'right'));
            }
        }
        else
            $itemsTable->addRow()->addCol('<em>Apparently there are no items.</em>', array('colspan' => 2));

        return array(
            array(
                'title' => 'Order Details',
                'content' => $table->render(),
                'topright' => Html::form()->build($statusForm)
            ),
            array(
                'title' => 'Order Items',
                'content' => $itemsTable->render()
            )
        );
    }

    /**
     * Deletes an order and redirects back to manage orders page.
     *
     * Route: admin/catalog/orders/delete/:id
     *
     * @param int $id The id of the order to delete.
     */
    public static function delete($id)
    {
        Catalog::orderitem()->where('order_id', '=', $id)->delete();

        if(Catalog::order()->delete($id))
            Message::ok('Order deleted successfully.');
        else
            Message::error('Error deleting order. Please try again.');

        Url::redirect('admin/catalog/orders/manage');
    }

}

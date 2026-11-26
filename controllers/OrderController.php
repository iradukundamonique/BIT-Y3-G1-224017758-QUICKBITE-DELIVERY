<?php
declare(strict_types=1);

final class OrderController
{
    public function index(): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        view('orders/index', [
            'title'  => $user['role'] === 'customer' ? 'My orders' : 'Orders',
            'orders' => OrderModel::listFor($user['role'], $user['id']),
        ]);
    }

    public function show(): void
    {
        Auth::requireLogin();
        $order = OrderModel::find(get_id());
        if (!$order || !OrderModel::canView($order, Auth::user())) {
            notFound(); // same response whether it does not exist or is not yours
        }
        view('orders/show', [
            'title' => 'Order #' . $order['order_id'],
            'order' => $order,
            'items' => OrderModel::items((int)$order['order_id']),
        ]);
    }

    /** Customer places an order from a restaurant menu page. */
    public function place(): void
    {
        Auth::requireRole('customer');
        if (!is_post()) {
            redirect('home/index');
        }
        Csrf::verify();

        $restaurant = RestaurantModel::find((int)input('restaurant_id'));
        if (!$restaurant) {
            notFound();
        }

        $qty = [];
        $raw = $_POST['qty'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $itemId => $q) {
                if (!is_string($q) || !ctype_digit($q) || !ctype_digit((string)$itemId)) {
                    continue;
                }
                $q = (int)$q;
                if ($q > 0) {
                    $qty[(int)$itemId] = min($q, 50);
                }
            }
        }

        $v = (new Validator($_POST))
            ->required('delivery_address', 'Delivery address')
            ->min('delivery_address', 'Delivery address', 5)
            ->max('delivery_address', 'Delivery address', 255);
        $errors = $v->errors;
        if (!$qty) {
            $errors['qty'] = 'Choose at least one item.';
        }

        if ($errors) {
            http_response_code(422);
            view('restaurants/show', [
                'title'      => $restaurant['name'],
                'restaurant' => $restaurant,
                'items'      => MenuItemModel::byRestaurant((int)$restaurant['restaurant_id'], true),
                'errors'     => $errors,
            ]);
            return;
        }

        try {
            $id = OrderModel::create(Auth::id(), (int)$restaurant['restaurant_id'], input('delivery_address'), $qty);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('restaurants/show', ['id' => $restaurant['restaurant_id']]);
        }
        flash('success', 'Order placed! We will notify you as it progresses.');
        redirect('orders/show', ['id' => $id]);
    }

    /** Admin: change status / assign rider. */
    public function edit(): void
    {
        Auth::requireRole('admin');
        $order = OrderModel::find(get_id());
        if (!$order) {
            notFound();
        }
        $riders = UserModel::riders();
        $errors = [];

        if (is_post()) {
            Csrf::verify();
            $riderIds = array_map(static fn($r) => (string)$r['user_id'], $riders);
            $errors = (new Validator($_POST))
                ->inList('status', 'status', OrderModel::STATUSES)
                ->errors;
            $riderInput = input('rider_id');
            if ($riderInput !== '' && !in_array($riderInput, $riderIds, true)) {
                $errors['rider_id'] = 'Choose a valid rider.';
            }
            if (!$errors) {
                OrderModel::adminUpdate((int)$order['order_id'], input('status'), $riderInput === '' ? null : (int)$riderInput);
                flash('success', 'Order updated.');
                redirect('orders/show', ['id' => $order['order_id']]);
            }
        }
        view('orders/edit', ['title' => 'Update order #' . $order['order_id'], 'order' => $order, 'riders' => $riders, 'errors' => $errors]);
    }

    public function cancel(): void
    {
        Auth::requireRole('customer');
        $this->postOnly();
        if (OrderModel::cancel(get_id(), Auth::id())) {
            flash('success', 'Order cancelled.');
        } else {
            flash('error', 'Only your own pending orders can be cancelled.');
        }
        redirect('orders/index');
    }

    public function claim(): void
    {
        Auth::requireRole('rider');
        $this->postOnly();
        $id = get_id();
        if (OrderModel::claim($id, Auth::id())) {
            flash('success', 'Order claimed. Happy delivering!');
        } else {
            flash('error', 'Sorry, that order is no longer available.');
        }
        redirect('orders/index');
    }

    public function deliver(): void
    {
        Auth::requireRole('rider');
        $this->postOnly();
        if (OrderModel::markDelivered(get_id(), Auth::id())) {
            flash('success', 'Order marked as delivered.');
        } else {
            flash('error', 'You can only complete your own out-for-delivery orders.');
        }
        redirect('orders/index');
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        $this->postOnly();
        $order = OrderModel::find(get_id());
        if (!$order) {
            notFound();
        }
        OrderModel::delete((int)$order['order_id']);
        flash('success', 'Order deleted.');
        redirect('orders/index');
    }

    private function postOnly(): void
    {
        if (!is_post()) {
            redirect('orders/index');
        }
        Csrf::verify();
    }
}

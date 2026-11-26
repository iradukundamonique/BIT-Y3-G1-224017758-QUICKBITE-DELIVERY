<?php
declare(strict_types=1);

final class RestaurantController
{
    public function index(): void
    {
        $q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        view('restaurants/index', [
            'title'       => 'Restaurants',
            'restaurants' => RestaurantModel::all($q),
            'q'           => $q,
        ]);
    }

    public function show(): void
    {
        $restaurant = RestaurantModel::find(get_id());
        if (!$restaurant) {
            notFound();
        }
        $isAdmin = Auth::hasRole('admin');
        view('restaurants/show', [
            'title'      => $restaurant['name'],
            'restaurant' => $restaurant,
            'items'      => MenuItemModel::byRestaurant((int)$restaurant['restaurant_id'], !$isAdmin),
            'errors'     => [],
        ]);
    }

    public function create(): void
    {
        Auth::requireRole('admin');
        $errors = [];
        if (is_post()) {
            Csrf::verify();
            $errors = $this->validate();
            if (!$errors) {
                $id = RestaurantModel::create(input('name'), input('location'), input('phone'));
                flash('success', 'Restaurant created.');
                redirect('restaurants/show', ['id' => $id]);
            }
        }
        view('restaurants/form', ['title' => 'Add restaurant', 'errors' => $errors, 'restaurant' => null]);
    }

    public function edit(): void
    {
        Auth::requireRole('admin');
        $restaurant = RestaurantModel::find(get_id());
        if (!$restaurant) {
            notFound();
        }
        $errors = [];
        if (is_post()) {
            Csrf::verify();
            $errors = $this->validate();
            if (!$errors) {
                RestaurantModel::update((int)$restaurant['restaurant_id'], input('name'), input('location'), input('phone'));
                flash('success', 'Restaurant updated.');
                redirect('restaurants/show', ['id' => $restaurant['restaurant_id']]);
            }
        }
        view('restaurants/form', ['title' => 'Edit restaurant', 'errors' => $errors, 'restaurant' => $restaurant]);
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        if (!is_post()) {
            redirect('restaurants/index');
        }
        Csrf::verify();
        if (RestaurantModel::delete(get_id())) {
            flash('success', 'Restaurant deleted.');
        } else {
            flash('error', 'This restaurant has order history and cannot be deleted.');
        }
        redirect('restaurants/index');
    }

    private function validate(): array
    {
        return (new Validator($_POST))
            ->required('name', 'Name')->max('name', 'Name', 100)
            ->required('location', 'Location')->max('location', 'Location', 150)
            ->required('phone', 'Phone')
            ->pattern('phone', '/^[0-9+()\-\s]{7,20}$/', 'Phone must be 7-20 digits (may include + - ( ) and spaces).')
            ->errors;
    }
}

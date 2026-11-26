<?php
declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        view('home/index', [
            'title'       => 'Home',
            'restaurants' => RestaurantModel::all(),
            'menuItems'   => MenuItemModel::allAvailableWithRestaurant(),
        ]);
    }
}

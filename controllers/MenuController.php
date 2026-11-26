<?php
declare(strict_types=1);

final class MenuController
{
    public function create(): void
    {
        Auth::requireRole('admin');
        $restaurant = RestaurantModel::find(get_id('restaurant_id'));
        if (!$restaurant) {
            notFound();
        }
        $errors = [];
        if (is_post()) {
            Csrf::verify();
            $errors = $this->validate();
            if (!$errors) {
                $photo = $this->savePhoto($errors);
                if (!$errors) {
                    MenuItemModel::create((int)$restaurant['restaurant_id'], input('name'), input('price'), isset($_POST['available']), $photo);
                    flash('success', 'Menu item added.');
                    redirect('restaurants/show', ['id' => $restaurant['restaurant_id']]);
                }
            }
        }
        view('menu/form', ['title' => 'Add menu item', 'errors' => $errors, 'restaurant' => $restaurant, 'item' => null]);
    }

    public function edit(): void
    {
        Auth::requireRole('admin');
        $item = MenuItemModel::find(get_id());
        if (!$item) {
            notFound();
        }
        $restaurant = RestaurantModel::find((int)$item['restaurant_id']);
        $errors = [];
        if (is_post()) {
            Csrf::verify();
            $errors = $this->validate();
            if (!$errors) {
                $photo = $this->savePhoto($errors);
                if (!$errors) {
                    MenuItemModel::update((int)$item['item_id'], input('name'), input('price'), isset($_POST['available']), $photo);
                    flash('success', 'Menu item updated.');
                    redirect('restaurants/show', ['id' => $item['restaurant_id']]);
                }
            }
        }
        view('menu/form', ['title' => 'Edit menu item', 'errors' => $errors, 'restaurant' => $restaurant, 'item' => $item]);
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        if (!is_post()) {
            redirect('restaurants/index');
        }
        Csrf::verify();
        $item = MenuItemModel::find(get_id());
        if (!$item) {
            notFound();
        }
        if (MenuItemModel::delete((int)$item['item_id'])) {
            flash('success', 'Menu item deleted.');
        } else {
            flash('error', 'This item appears in past orders. Mark it as unavailable instead of deleting it.');
        }
        redirect('restaurants/show', ['id' => $item['restaurant_id']]);
    }

    public function uploadPhotoAction(): void
    {
        Auth::requireRole('admin');
        if (!is_post()) {
            redirect('restaurants/index');
        }
        Csrf::verify();
        $item = MenuItemModel::find(get_id());
        if (!$item) {
            notFound();
        }
        $errors = [];
        $photo = $this->savePhoto($errors);
        if (!$errors && $photo !== null) {
            MenuItemModel::update((int)$item['item_id'], $item['name'], (string)$item['price'], (bool)$item['available'], $photo);
            flash('success', 'Food photo uploaded successfully.');
        } else {
            flash('error', $errors['photo'] ?? 'Please select a food photo first.');
        }
        redirect('restaurants/show', ['id' => $item['restaurant_id']]);
    }

    private function savePhoto(array &$errors): ?string
    {
        if (!isset($_FILES['photo']) || ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null; // Photo is optional; the public menu displays a placeholder until one is uploaded.
        }

        $file = $_FILES['photo'];
        $uploadError = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError !== UPLOAD_ERR_OK) {
            $messages = [
                UPLOAD_ERR_INI_SIZE => 'The photo is larger than the server upload limit. Please choose a smaller photo (try under 2 MB).',
                UPLOAD_ERR_FORM_SIZE => 'The photo is too large for this form.',
                UPLOAD_ERR_PARTIAL => 'The photo was only partly uploaded. Please try again.',
                UPLOAD_ERR_NO_TMP_DIR => 'PHP upload folder is missing. Contact the person who installed XAMPP.',
                UPLOAD_ERR_CANT_WRITE => 'The server could not save the photo. Check folder permissions.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the photo upload.',
            ];
            $errors['photo'] = $messages[$uploadError] ?? 'Photo upload failed. Please try again.';
            return null;
        }

        if (!is_uploaded_file($file['tmp_name'] ?? '')) {
            $errors['photo'] = 'The selected photo did not reach the server. Please choose it again.';
            return null;
        }
        if ((int)($file['size'] ?? 0) > 4 * 1024 * 1024) {
            $errors['photo'] = 'Please choose a photo smaller than 4 MB.';
            return null;
        }
        if (!class_exists('finfo')) {
            $errors['photo'] = 'PHP Fileinfo is not enabled. Enable extension=fileinfo in XAMPP php.ini and restart Apache.';
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) {
            $errors['photo'] = 'Please upload an image in JPG, PNG or WEBP format.';
            return null;
        }

        $directory = BASE_PATH . '/assets/uploads/menu';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            $errors['photo'] = 'The menu photo folder could not be created. Check your project folder permissions.';
            return null;
        }
        if (!is_writable($directory)) {
            $errors['photo'] = 'The menu photo folder is not writable. Check permissions for assets/uploads/menu.';
            return null;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            $errors['photo'] = 'The server could not save the photo. Check assets/uploads/menu folder permissions.';
            return null;
        }
        return $filename;
    }

    private function validate(): array
    {
        return (new Validator($_POST))
            ->required('name', 'Name')->max('name', 'Name', 100)
            ->required('price', 'Price')->money('price', 'Price')
            ->errors;
    }
}

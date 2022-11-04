# Menu helper
A Laravel heper to create menu.

## Installation
Install throught composer `composer require plokko/menu-builder`


The package will be auto registered in laravel >=5.5;
**If you use laravel <5.5 follow the next two steps**

1. Add service provider to `config/app.php` in `providers` section
```php
plokko\MenuBuilder\MenuBuilderServiceProvider::class,
```

1. Register package facade in `config/app.php` in `aliases` section (optional)
```php
plokko\MenuBuilder\Facades\Menu::class,
```

## Usage
The MenuHelper package helps you define menu items via a fluent definition.

You can easly add items to the menu, instantiated via the *Menu* facade, via the "item" function;
you can easly define other menu or items proprieties via other functions like so:
```php
$menu = Menu::useTrans('menu.main')
    ->item('home')->route('home')->icon('home')
    
    ->item('clients')->route('clients.index')->icon('business')

    ->item('curtain-models')->route('curtain-models.index')->icon('blinds')

    ->whenHasRole('admin', fn($m) =>
        $m->item('users')->route('users.index')->icon('account_circle')->color('pink')
    );

$menuArray = $menu->toArray();
```

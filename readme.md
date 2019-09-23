[![Latest Version on Packagist][ico-version]][link-packagist]  [![Total Downloads][ico-downloads]][link-downloads]  [![Build Status][ico-travis]][link-travis]  [![StyleCI][ico-styleci]][link-styleci]  

![Elements](https://i.imgur.com/byzJHiI.png)

`// TODO: Write a description!`

## Installation  
  
Via Composer  
  
```bash  
$ composer require click/elements  
```  
  
### Laravel 5.5+  
  
Sit back and relax. You've done too much already. The   package will auto-register after composer runs. ⛵️  
  
### Laravel 5.4 & below  
  
Add this to your `config/app.php` file under `providers`.  
  
```  
Click\Elements\ElementsServiceProvider::class,  
```  
  
If you want to use the `Elements` facade then add this to your `config/app.php` file under `aliases`.  
  
```  
'Elements' => Click\Elements\Facades\Elements::class,  
```  
  
## Usage  
  
### Creating Element Types
  
```bash  
$ php artisan make:element MyElement
```  
  
A fresh element class will be dropped somewhere like `app\Elements\MyElement.php` looking something like this.  
  
```php  
<?php  
  
namespace App\Elements;  
  
use Click\Elements\Element;  
use Click\Elements\Schema;  
  
class MyElement extends Element  
{  
    /**  
     * Define the schema for the MyElement element.  
     *  
     * @param Schema $schema  
     * @return void  
     */  
    public function getDefinition(Schema $schema)  
    {  
        //$schema->string('title');  
        //$schema->boolean('inStock');  
        //$schema->integer('quantity');  
        //$schema->double('price');  
        //$schema->text('description');  
        //$schema->array('tags');  
        //$schema->json('settings');  
        //$schema->relation('author');  
        //$schema->timestamp('shippedAt');  
    }  
}  
```

You can generate elements for use with [two-way binding](#two-way-binding) by specifying a model when using the `make:element` command.

```bash
$ php artisan make:element MyElement --model=App\\MyModel
```
  
### Installing and Updating Elements

Once you've defined properties for a new element or updated an existing element, run the install command to commit changes to the database.

```bash  
$ php artisan elements:install  
```
  
**You need to do this whenever any changes are made to element schemas.**
  
You can bypass the need to run the command to commit changes  
to the database by adding this to your `.env` file. This is useful for local development.
  
```ini  
ELEMENTS_AUTO_INSTALL=true  
```

### Using Elements in your Application

Elements try to replicate the functionality of Eloquent models where possible to make the developer experience as familiar as possible. A custom query builder is provided which allows you to query properties and relationships as you'd expect.

```php
<?php

class Author extends Element
{
    public function getDefinition(Schema $schema)  
    {  
        $schema->string('name');
    }
}

class Book extends Element
{
    public function getDefinition(Schema $schema)  
    {  
        $schema->string('title');
        $schema->integer('published');
        $schema->relation('author');
    }
}

```
Creating new elements is much the same as Eloquent models, but assigning relationships is a little different.

```php
<?

$author = Author::create([
    'name' => 'Neal Stephenson'
]);

$book = Book::create([
    'title' => 'Snow Crash',
    'published' => 1992,
    'author' => $author
]);
```

Fetching elements is quite similar to Eloquent models too, but querying relationships is a little different.
```php
<?php

// Get all elements
$books = Book::all();

// Eager load a relation property
$books = Book::with('author')->get();

// Where property
$books = Book::where('published', '>', 1991)->get();

// Where relation property
$books = Book::where('author.name', 'like', '%neal%')->get();
```

### Two-way Binding  
  
You can enable an optional two-way binding to Eloquent models. Simply define a `getModel()` method and `$syncToModel` and `$syncFromModel` attributes on your element to have changes to either the element or model be replicated to the other.
  
```php  
<?php

use App\MyModel;
  
class MyElement extends Element  
{  
    use TwoWayBinding;
    
    // Update the model when the element is updated
    protected $syncToModel = true;

    // Update the element when the model is updated
    protected $syncFromModel = true;
  
    public function getModel()  
    {  
        return MyModel::class;  
    }  
}  
```
  
This behaviour is dependent on [eloquent events](https://laravel.com/docs/5.8/eloquent#events) being triggered on both your own and the internal element models to function property.
  
A call to  `YourModel::delete()` for example will not trigger any related elements to delete. See [deleting models](https://laravel.com/docs/5.7/eloquent#deleting-models) in the Laravel documentation for more info.

You can specify the mappings between model attributes and element properties by defining `mapForModel()` and `mapForElement()` methods on your element.

```php
<?php

class MyElement extends Element  
{
    public static function mapForModel(Element $element)
    {
        return [
            'model_attribute' => $element->elementProperty
        ];
    }
    
    public static function mapForElement(MyModel $model)
    {
        return [
            'elementProperty' => $model->model_attribute
        ];
    }
}  
```

Elements use a strict type system so make sure to cast each attribute to the expected type before assigning it to the property.
  
### Bring your own data
  
You likely have existing data in your application that you want to synchronise. To do this, create element classes for models you want to import, defining mappings in the `mapForElement()` method described above, and then run the import command to create elements from your models.

```bash
$ php artisan elements:import
```
  
## Change log  
  
Please see the [changelog](changelog.md) for more information on what has changed recently.  
  
## Testing  
  
```bash  
$ composer install && vendor/bin/phpunit  
```  
  
## Contributing  
  
Please see [contributing.md](contributing.md) for details and a todolist.  
  
## Security  
  
If you discover any security related issues, please email developers@clickdigitalsolutions.co.uk instead of using the issue tracker.  
  
## Credits  
  
- [Click][link-author]  
- [All Contributors][link-contributors]  
  
## License  
  
Proprietary. Please see the [license file](license.md) for more information.  
  
[ico-version]: https://img.shields.io/packagist/v/click/elements.svg?style=flat-square  
[ico-downloads]: https://img.shields.io/packagist/dt/click/elements.svg?style=flat-square  
[ico-travis]: https://img.shields.io/travis/click/elements/master.svg?style=flat-square  
[ico-styleci]: https://styleci.io/repos/12345678/shield  
  
[link-packagist]: https://packagist.org/packages/click/elements  
[link-downloads]: https://packagist.org/packages/click/elements  
[link-travis]: https://travis-ci.org/click/elements  
[link-styleci]: https://styleci.io/repos/12345678  
[link-author]: https://github.com/click  
[link-contributors]: ../../contributors
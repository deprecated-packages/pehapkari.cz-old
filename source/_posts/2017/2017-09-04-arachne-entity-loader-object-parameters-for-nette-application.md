---
id: 39
layout: post
title: "Arachne/EntityLoader - Object Parameters for Nette/Application"
perex: '''
    Ever wanted to get rid of <code>EntityManager::find($id)</code> as the first thing in your every presenter action?
    With <a href="https://github.com/Arachne/EntityLoader">Arachne/EntityLoader</a> you can.
    Of course it is not limited to Doctrine, you can easily use it with a different ORM library.
'''
author: 5
reviewed_by: [1, 25, 26]
lang: en
---

## The basics

Normally most of your presenters would look like this.

```php
use App\Entity\Product;
use Nette\Application\UI\Presenter;

class ProductPresenter extends Presenter
{
    public function actionDetail(int $id): void
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            $this->error();
        }

        // Do stuff with $product.
    }
}
```

With Arachne/EntityLoader it is much easier.

```php
use App\Entity\Product;
use Arachne\EntityLoader\Application\EntityLoaderPresenterTrait;
use Nette\Application\UI\Presenter;

class ProductPresenter extends Presenter
{
    use EntityLoaderPresenterTrait;

    public function actionDetail(Product $product): void
    {
        // ...
    }
}
```

Now how would you make a link to a action that needs object? Of course you can use the object if you have it. But if you have a case where you don't need the actual entities for anything and only have their IDs it will work as well.

```
{* All of these are fine. *}
<a n:href="Product:detail, product => $product">Product detail</a>
<a n:href="Product:detail, product => $product->getId()">Product detail</a>
<a n:href="Product:detail, product => $productId">Product detail</a>
```


## It works everywhere

It doesn't work just for actions but for signals as well.

```php
    public function handleDelete(Product $product): void
    {
        // ...
    }
```

Persistent parameters are no problem either.

```php
    /**
     * @var Product
     * @persistent
     */
    public $product;
```

**And all of it works in components as well.**


## Request Object with Entities

Unlike older solutions like [Zenify/DoctrineMethodsHydrator](https://github.com/DeprecatedPackages/DoctrineMethodsHydrator) EntityLoader will put the entities into your `Nette\Application\Request` object as well. This will make them available in more places than just your actions and signals. Like if you need to pass the product to a component.

```php
class ProductPresenter extends Presenter
{
    public function createComponentForm(): ProductForm
    {
        /** @var Product $product */
        $product = $this->getRequest()->getParameter('product');

        return $this->productFormFactory->create($product);
    }
}
```


## Scalar Types

For consistency it actually converts scalar types like integers as well. This is not too important as newer versions of Nette do that too but with EntityLoader they will be converted in the Request as well.


## Conclusion

Having objects in `Nette\Application\Request` may not look like a killer feature at first. The point is that having entities in Request also unleashes the full power of both [Arachne/ParameterValidation](https://github.com/Arachne/ParameterValidation) and [Arachne/SecurityVerification](https://github.com/Arachne/SecurityVerification). I'll go into details in the next article.

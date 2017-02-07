---
layout: post
title: "Jak funguje Dependency Injection v Symfony a v Nette"
perex: "V tomto článku si ukážeme základy Dependency Injection &ndash; jaký je rozdíl mezi Nette presenterem a Symfony controllerem. A jak přenést trochu chování Nette do Symfony."
tags: [nette, symfony]
author: 10
---

## Dependency Injection (DI) + Container

DI a container už bude asi většina čtenářů znát, takže jen rychlovka pro připomenutí:

1. DI slouží k předávání závislostí konstuktorem
2. Container je služba, která vytváří a poskytuje objekty

Spojením DI a containeru získáme tyto výhody:

- **Závislosti se kontrolují již při sestavení containeru**
- **Hned při pohledu na konstruktor je jasné, na čem třída závisí**
- **Eliminace skrytých závislostí**
	

```php
// Presenter/Controller
final class ...
{
    public function actionDefault()
    {
        $this->myClass->send();
    }
}
```

```php
// MyClass
class MyClass
{
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }
    
    public function send()
    {
        $this->mailer->send()
    }
}
```


## Jak to funguje v Nette?

**Presenter je v Nette registrovaný jako služba**. Takže i on je sestavovaný containerem a mohou mu být vloženy závislosti do konstruktoru. Pak by řetězec závislostí **Presenter > MyClass > Mailer** mohl vypadat nějak takhle:

```php
// Presenter
use Nette\Application\UI\Presenter;

final class TestPresenter Extends Presenter
{
    public function __construct(MyClass $myClass)
    {
        $this->myClass = $myClass;
    }

    public function actionDefault()
    {
        $this->myClass->send();
    }
}
```

```php
// MyClass
class MyClass
{
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send()
    {
        // Some logic
        $this->mailer->send();
    }
}
```

Každá třída má svou závislost a nepůjde získat z containeru aniž by svou závislost dostala. Všechny závislosti jsou přehledně vidět v konstruktoru a nikde není žádná skrytá závislost.


### Výsledek

- Závislosti se kontrolují již při sestavení containeru - **ANO**
- Hned při pohledu na konstruktor je jasné, na čem třída závisí - **ANO**
- Eliminace skrytých závislostí - **ANO**


## Jak to funguje v Symfony

**Controller v Symfony jako služba registrovaný není**, a tak mu není možné vložit jinou závislost konstruktorem. Místo toho 
existuje traita `ContainerAwareTrait`, která předává controlleru celý container. Pokud bychom měli stejnou situaci jako v předchozí části, pak by vypadala následovně:

```php
// Controller
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class TestController extends Controller
{
    public function indexAction()
    {
        $this->myClass = $this->container->get('myClass');
        $this->myClass->send();
    }
}
```

```php
// MyClass
class MyClass
{
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send()
    {
        // Some magic
        $this->mailer->send();
    }
}
```

Základní rozdíl je tento řádek: 

```php
$this->myClass = $this->container->get('myClass');
```

Na začátku jsme si definovali 3 úkoly, které chceme po spojení DI a containeru. Podívejme se, co jsme splnili:


### Výsledek

- Závislosti se kontrolují již při sestavení containeru - **NE**
  * Controller není službou v containeru, takže se jeho závislosti nekontrolují.
- Hned při pohledu na konstruktor je jasné, na čem třída závisí - **NE**
  * Musíme prohledat celou třídu a najít všechny řádky s `$this->container->get('whatever');` abychom našli všechny závislosti.
- Eliminace skrytých závislostí - **NE**
  * Existují závislosti na něčem co je potřeba, ale při vytvoření instance to ještě potřeba není.


## Controller jako služba

Naštěstí existuje řešení! Bundle [Symplify/ControllerAutowire](https://github.com/Symplify/ControllerAutowire), který automaticky **registruje controller jako službu do containeru**. Po instalaci se bude controller chovat stejně jako presenter v předchozí ukázce. 

Bude mu možné předat závislost konstruktorem a získáme tím všechny přednosti, které jsme chtěli po spojení DI a containeru. Navíc při použití traity [ControllerAwareTrait](https://github.com/Symplify/ControllerAutowire#used-to-frameworkbundles-controller-use-helpers-traits) fungují i všechny pomocné metody z FrameworkBundle.


```php
// Controller
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class TestController extends Controller
{
    public function __construct(MyClass $myClass)
    {
        $this->myClass = $myClass;
    }

    public function indexAction()
    {
        $this->myClass->send();
    }
}
```

```php
// MyClass
class MyClass
{
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send()
    {
        // Some logic
        $this->mailer->send();
    }
}
```

### Výsledek

- Závislosti se kontrolují již při sestavení containeru - **ANO**
- Hned při pohledu na konstruktor je jasné, na čem třída závisí - **ANO**
- Eliminace skrytých závislostí - **ANO**


### Zdroje

- [Nette - Dependency injection](https://doc.nette.org/cs/2.4/dependency-injection)
- [Symfony - Service container](http://symfony.com/doc/current/service_container.html)
- [domnikl/DesignPatternsPHP - Dependency Injection](https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/DependencyInjection)
- [Symplify/ControllerAutowire](https://github.com/Symplify/ControllerAutowire)

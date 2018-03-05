---
id: 63
layout: post
title: "Symfony 4: Vytváříme chytrý kontroler"
perex: '''
Co kdyby byl Symfony kontroler schopný automaticky najít správnou šablonu k požadované akci bez nutnosti opakovaně psát
její cestu? Co takhle mít možnost zasílat parametry do šablony z více míst a třeba i před renderovací metodou?
Symfony 4 je skvělý framework ale po chvíli práce s ním mi začaly chybět některé fičury, na které jsem byl zvyklý z
jiných frameworků, jako je například [Nette Framework](https://nette.org/cs/).
Rozhodl jsem se, že si je do Symfony musím dodělat. V tomto článku vám ukážu, jak jsem toho docílil.
'''
author: 34
lang: cs
---

Řekněme, že máme nějaký HomepageController s renderDefault metodou umístěný ve složce `src/controller`

````PHP
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


final class HomepageController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function renderDefault(): Response
    {
        $number = mt_rand(0, 100);
        return $this->render('default.twig', [
            'number' => $number,
        ]);
    }

}
````

a default.twig šablonu pro renderDefault akci ve složce templates.

````twig
Number: {{ number }}
````

Všechno funguje a vypadá v pořádku. No jo, jenomže co když budu náhodou potřebovat vložit parametr odjinud
než z renderDefault metody? To je v tuto chvíli nemožné..., ledaže bychom si vytvořili AbstractController, který nám to umožní.

````PHP
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


abstract class AbstractController extends Controller
{

    /**
     * @var array
     */
    private $templateParameters = [];


    protected function setTemplateParameters(array $parameters): void
    {
        $this->templateParameters = array_merge($this->templateParameters, $parameters);
    }


    protected function renderTemplate(string $template, array $parameters = [], Response $response = null): Response
    {
        $this->setTemplateParameters($parameters);
        return $this->render(
            $template, $this->templateParameters, $response
        );
    }

}
````

Zbývá už jen AbstractController podědit v HomepageControlleru, vytvořit setter metodu a zavolat ji v renderDefault metodě.

````PHP
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


final class HomepageController extends AbstractController
{

    /**
     * @Route("/", name="homepage")
     */
    public function renderDefault(): Response
    {
        $this->setRandomNumberIntoTemplate();
        return $this->renderTemplate('default.twig');
    }


    private function setRandomNumberIntoTemplate(): void
    {
        $number = mt_rand(0, 100);
        $this->setTemplateParameters([
            'number' => $number
        ]);
    }

}
````

Takto vytvořená metoda pro předávání parametrů do šablony je sice pěkná, ale kdybychom chtěli dané číslo vkládat
do každé šablony automaticky, je potřeba ji neustále a dokola volat v každé render metodě. V tuhle chvíli
by se hodila beforeRender metoda, tak si ji pojďme přidat do AbstractControlleru.

````PHP
// ...
protected function beforeRender(): void {}
// ...

protected function renderTemplate(string $template, array $parameters = [], Response $response = null): Response
{
    $this->beforeRender();
    $this->setTemplateParameters($parameters);

    return $this->render(
         $template, $this->templateParameters, $response
    );
}
````

Nyní jen stačí tuto metodu použít v HomepageControlleru.

````PHP
// ...
public function beforeRender(): void
{
    $this->setRandomNumberIntoTemplate();
}

// ...
/**
 * @Route("/", name="homepage")
 */
public function renderDefault(): Response
{
    return $this->renderTemplate('default.twig');
}
````

V HomepageControlleru je ale stále potřeba zapisovat cestu k šabloně. Většinou preferuji modulární strukturu aplikace
s šablonami umístěnými ve složce pojmenované po kontroleru zanořené v templates složce, která je ve stejné složce jako kontrolery.
Zní to trošku divně, takže uvedu jednoduchý příklad:
- HomepageController => `src/Modules/HomepageModule/Controller/HomepageController.php`
- Šablona => `src/Modules/HomepageModule/Controller/templates/Homepage/default.twig`

Povětšinou ještě modul dělím na admin a front ale pro tento článek je tato struktura dostačující. V dalším kroku je tedy potřeba
přesunout Homepage modul a jeho šablony do zmiňované adresářové struktury, a stejně tak přesunout AbstractController. Ten však bude
například ve složce CoreModule `src/Modules/CoreModule/Controller/AbstractController.php`.

Abychom to všechno zprovoznili, je potřeba provést několik úprav. Nejdříve upravíme AbstractController,
protože zde nastává největší změna.

````PHP
namespace App\Modules\CoreModule\Controller;

// ...

abstract class AbstractController extends Controller
{

    // ...
    protected function renderTemplate(array $parameters = [], Response $response = null): Response
    {
        preg_match(
            '/\:\:render(?<template>\S+)/',
            $this->get('request_stack')->getCurrentRequest()->attributes->get('_controller'),
            $matches
	      );

        // ...
        return $this->render(
           $this->getTemplatePath(strtolower($matches['template'])),
           // ...
        );
    }


    public function getTemplatePath(string $view): string
    {
        $reflector = new \ReflectionClass(get_called_class());
        $templatesDirectoryName = str_replace(
            'Controller',
            '',
            basename($reflector->getFileName(), '.php')
        );

        $moduleTemplatesDirectoryPath = str_replace (
            $this->getParameter('kernel.root_dir') . '/',
            '',
            dirname($reflector->getFileName())
        ). '/templates/' . $templatesDirectoryName;

        return $moduleTemplatesDirectoryPath . '/' . $view . '.twig';
    }

}
````

Přibylo volání preg_match funkce v renderTemplate metodě, a byla přidána metoda getTemplatePath.
Tato metoda roztokenuje jméno aktuálního kontroleru a render metody, a následně vrátí cestu k šabloně.

Za další je potřeba upravit HomepageController. Zde již není cesta k šabloně, protože ji nepotřebujeme.

````PHP
/**
 * @Route("/", name="homepage")
 */
public function renderDefault(): Response
{
    return $this->renderTemplate();
}
````

Nesmíme zapomenout nakonfigurovat Twig a anotace.

````YAML
// twig.yml
twig:
    paths: ['%kernel.project_dir%/src']
    debug: '%kernel.debug%'
    strict_variables: '%kernel.debug%'

// annotations.yml
controllers:
    resource: ../../src/Modules/
    type: annotation
````

Poslední co je potřeba upravit je cesta pro mapování kontrolerů.

````YAML
App\Modules\:
    resource: '../src/Modules'
    tags: ['controller.service_arguments'
````

Hotovo! Nyní už nemusíme psát cestu k šabloně, můžeme předávat parametry do šablon z více míst
a popřípadě je vkládat automaticky v beforeRender metodě.

Nevýhodou toho všeho je, že je potřeba dodržovat adresářovou strukturu, která je nastavena
v getTemplatePath metodě ve třídě AbstractController.

Budu rád za jakýkoliv váš feedback (klidně i negativní)!

*Originálně publikováno na [https://machy8.com/blog/symfony-4-creating-smart-controller](https://machy8.com/blog/symfony-4-creating-smart-controller)*

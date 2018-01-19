---
id: 58
layout: post
title: "SOLID principy: Princip otevřenosti a uzavřenosti"
perex: '''
Princip říká, že:

>>> Softwarové entity (třídy, moduly, funkce, atd.) by měly být otevřené pro rozšíření, ale uzavřené pro změnu.

'''
author: 30
related_items: [50,57]
---

Video (1:50)

[![Video na Youtube](/assets/images/posts/2018/solid-2/youtube.png)](http://www.youtube.com/watch?v=5e63vXtn-zc)

Řekněme, že máme takovýto kód, kde jsou dvě třídy, které vrací response a jedna, která slouží pro jeho vytáhnutí:

```php
<?php

class JsonResponse
{
    private $content = ["OK" => "true"];

    public function renderJson()
    {
        return json_encode($this->content);
    }
}

class XMLResponse
{
    private $content = ["OK" => "true"];

    public function renderXml()
    {
        $xml = new SimpleXMLElement('<main/>');
        array_walk_recursive($this->content, [$xml, 'addChild']);
        return $xml->asXML();
    }
}

class ResponseRender
{
    public function render($response)
    {
        if ($response instanceof JsonResponse) {
            return $response->renderJson();
        } elseif ($response instanceof XMLResponse) {
            return $response->renderXml();
        }
    }
}
```

Problém, který kód má je, že při vytvoření jakékoli další třídy ```XYResponse``` je potřeba upravit třídu ```ResponseRender```. Do budoucna nám to může udělat mnoho potíží, které sice v tomhle jednoduchém kódu nejsou na první pohled vidět, ale v komplexnějším kódu nám mohou hodně zavařit.

Bude mnohem lepší, pokud napíšeme třídu ```ResponseRender``` tak, aby už nemusela být více upravována. Nestane se nám tak, že při jedné změně budeme muset upravit kód na více místech, než by bylo nutné.

Dle principu tedy uzavřeme třídu ```ResponseRender``` změnám tak, že budeme požadovat konkrétní typ argumentu ```$response```, ať už interface nebo abstraktní třídu. Stále bude možné systém rozšířit o další typ ```Response```, ale už bez nutnosti úprav dalších tříd.

Zvolil jsem zde cestu vytvořením rozhraní ```Renderable```, který jasně definuje, že bude muset být implementována metoda ```render()``` vracející ```string```:
```php
<?php

interface Renderable
{
    public function render(): string;
}

class JsonResponse implements Renderable
{
    private $content = ["OK" => "true"];

    public function render(): string
    {
        return json_encode($this->content);
    }
}

class XMLResponse implements Renderable
{
    private $content = ["OK" => "true"];

    public function render(): string
    {
        $xml = new SimpleXMLElement('<main/>');
        array_walk_recursive($this->content, [$xml, 'addChild']);
        return $xml->asXML();
    }
}

class ResponseRender
{
    public function render(Renderable $response)
    {
        return $response->render();
    }
}
```

## Zdroje:
http://butunclebob.com/ArticleS.UncleBob.PrinciplesOfOod
https://web.archive.org/web/20060822033314/http://www.objectmentor.com/resources/articles/ocp.pdf
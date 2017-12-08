---
id: 50
layout: post
title: "SOLID principy – 4. díl: Princip odděleného rozhraní"
perex: '''
Princip odděleného rozhraní je definované Robertem C. Martinem původně pro Xerox a říká:

> Více specifických rozhraní je lepší než jedno obecné rozhraní.

Při jeho dodržování se kód stává více znovupoužitelný a užitečný. Pokud je více tříd nuceno implementovat rozhraní s metodami, které nepotřebují, je vhodné najít logický průnik (v čem se shodují) a rozhraní oddělit.
'''
author: 30
---

Video (1:29)

[![Video na Youtube](/assets/images/posts/2017/solid-4/solid-youtube.jpg)](http://www.youtube.com/watch?v=RkTMid_ccDo)

Na ukázku zde mám rozhraní ```IBag```, třídu ```Bag```, která jej implementuje a třídu ```Renderer```:

```php
<?php

interface IBag {
    public function setContent(string $content);
    public function appendContent(string $content);
    public function prependContent(string $content);
    public function getContent(string $content);
    public function loadFromFile(string $file);
    public function saveToFile(string $file);
    public function render(): string;
}

class Bag implements IBag
{
    private $content;

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function appendContent(string $content)
    {
        $this->content = $this->content . $content;
    }

    public function prependContent(string $content)
    {
        $this->content = $content . $this->content;
    }

    public function getContent(string $content)
    {
        return $content;
    }

    public function loadFromFile(string $file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException("File from argument not exists.");
        }

        $this->content = file_get_contents($file);
    }

    public function saveToFile(string $file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException("File from argument not exists.");
        }

        return file_put_contents($file, $this->content);
    }

    public function render(): string
    {
        return "Content: " . $this->content;
    }
}

class Renderer
{
    public function render(IBag $bag)
    {
        echo $bag->render();
    }
}
```

Větší interface ```IBag``` mohu rozdělit na tři logické rozhraní: Contentable, Fileable, Renderable. 

Třída ```Renderer``` tak už není nucena záviset na ```IBag```, ale postačí ```Renderable```. Třída ```Bag``` implementuje všechny tři rozhraní, ale další třídě může stačit už jenom třeba jedno.

```php
<?php

interface Renderable {
    public function render(): string;
}

interface Contentable
{
    public function setContent(string $content);
    public function appendContent(string $content);
    public function prependContent(string $content);
    public function getContent(string $content);
}

interface Fileable
{
    public function loadFromFile(string $file);
    public function saveToFile(string $file);
}

class Bag implements Renderable, Contentable, Fileable
{
    private $content;

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function appendContent(string $content)
    {
        $this->content = $this->content . $content;
    }

    public function prependContent(string $content)
    {
        $this->content = $content . $this->content;
    }

    public function getContent(string $content)
    {
        return $content;
    }

    public function loadFromFile(string $file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException("File from argument not exists.");
        }

        $this->content = file_get_contents($file);
    }

    public function saveToFile(string $file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException("File from argument not exists.");
        }

        return file_put_contents($file, $this->content);
    }

    public function render(): string
    {
        return "Content: " . $this->content;
    }
}

class Renderer
{
    public function render(Renderable $bag)
    {
        echo $bag->render();
    }
}
```

Jako u všech zásad je potřeba dbát na to, aby to nebylo přehnané. Aby nás pak nestrašily třídy se stovkou rozhraní. :)

## Zdroje:
https://web.archive.org/web/20150906155800/http://www.objectmentor.com/resources/articles/Principles_and_Patterns.pdf

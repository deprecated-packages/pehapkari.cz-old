---
id: 59
layout: post
title: "SOLID principy: Liskovové princip zastoupení"
perex: '''
Liskovové princip zastupitelnosti je definovám jako tahle na první podhled děsivá rovnice:

>>> Nechť Φ(x) je vlastnost prokazatelná objektu x typu T. 
Potom Φ(y) jsou pravdivé pro objekty y typu S, kde S je potomkem T.

Trochu lidskými slovy ale říká:

>>> Supertyp by měl být plně nahraditelný podtypem.


'''
author: 30
---

Video (1:46)

[![Video na Youtube](/assets/images/posts/2018/solid-3/youtube.png)](http://www.youtube.com/watch?v=mXGB_hC5084)

Což je zjednoduše to, že potomek třídy by mě zachovat stejný přístup k metodám, které má i rodičovská třída.

Řekněme, že máme takovýto kód:

```php
<?php

class Response
{
    protected $data;

    public function render()
    {
        return $this->data;
    }
}

class JsonResponse extends Response
{
    public function render($data)
    {
        return json_encode($data);
    }
}

class ResponseRender
{
    public function render(Response $response)
    {
        echo $response->render();
    }
}
```

Pokud bych do metody ```render()``` třídy ```ResponseRender``` jako parametr vložit instanci třídy ```JsonResponse```, skončilo by to chybou, protože ta požaduje v metodě ```render()``` data, což její rodičovská třída nevyžaduje. To znamená, že ```Response``` není plně nahraditelný třídou ```JsonResponse```.

Můžeme sice připravit podmínku, kde se typ ```JsonResponse``` bude kontrolovat, ale problém tím není zcela vyřešen, protože kdekoli jinde stále nebude ```Response``` zcela nahraditelná potomkem ```JsonResponse```. Navíc bychom tím porušili princip otevřenosti a uzavřenosti.

Správným řešením je sjednotit přístup k metodám tříd tak, aby byla rodičovská třída plně zastupitelná:

```php
<?php

class Response
{
    protected $data;

    public function render()
    {
        return $this->data;
    }
}

class JsonResponse extends Response
{
    public function render()
    {
        return json_encode($this->data);
    }
}

class ResponseRender
{
    public function render(Response $response)
    {
        echo $response->render();
    }
}
```

## Zdroje:
http://butunclebob.com/ArticleS.UncleBob.PrinciplesOfOod
https://web.archive.org/web/20151128004108/http://www.objectmentor.com/resources/articles/lsp.pdf
Liskovové přednáška: https://www.youtube.com/watch?v=dtZ-o96bH9A&feature=youtu.be&t=40m
http://www.engr.mun.ca/~theo/Courses/sd/5895-downloads/sd-principles-3.ppt.pdf
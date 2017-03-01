---
layout: post
title: "Šablony v PhpStormu"
perex: "Jak i usnadnit život šablonami v PhpStormu"
author: 15
---

### Proč šablony

Kód můžeme psát buď v IDE nebo v textovém editoru. IDE mají velkou výhodu v tom, že pomáhají s analýzou a kompletací kódu.
Takže programátor nemusí psát všechno, ale může si tvořit vlastní zkratky či využívat zkratky stávající. 
Zde budu rozebírat šablony pro IDE PhpStorm. PhpStorm je sice placený, ale studentskou licenci lze získat i za ISIC.

### Kdy používat šablony? 
Nejvíce se šabony hodí buď na velkých projektech, kde se část kódu často opakuje (controllery, DTO, entity, query objecty apod.) 
 nebo při časté tvorbě malých, velmi podobných projektů, kde se opakují části kódu, které nejde vyčlenit do nějakého sandboxu.

V PhpStormu jsou dva druhy šablon. [File templates](https://www.jetbrains.com/help/phpstorm/2016.3/file-and-code-templates.html)
 jsou soubory s nějakým předem daným obsahem. 
Defaultně existují file templates například tyto *HTML File*, *XHTML File*, *PHP File*, *PHP Class*.

Dále jsou k dispozici [live templates](https://www.jetbrains.com/help/phpstorm/2016.3/live-templates-2.html), 
které umožní definovat zkratku (pár písmen), a k ní odpovídající kód.
Po napsání zkratky a stisknutí `TAB` se místo zkratky vloží k ní patřící kód. 

## Live templates

Live templates lze spravovat v **File | Settings | Editor | Live templates**. Zajímavé na live templatech je to, 
že jsou context aware, tedy aktivují se pouze v určitém kontextu, a ne jinde. Jak lze vidět na obrázku.

<div class="text-center">
    <img src="/assets/images/posts/2017/phpstorm/contexts.png">
    <br>
    <em>
        Kontexty pro live template
    </em>
</div>

<br>

možné kontexty jsou například Javascript, HTML, PHP, SQL, a další. V rámci PHP pak můžeme mít zvlášť live templates pro 
komentáře, class members apod.
Tlačítko, kterým expandujeme zkratku do definovaného kusu kódu lze z defaultního `TAB` v tomto nastavení změnit.
Live templates lze také vyvolat zkratkou `CTRL+J`, která ukáže všechny dostupné live templates.
V template textu (kde je kód, který se má po expandování zkratky objevit), lze používat proměnné.
Defaultně jsou definované proměné $SELECTION$ a $END$. 
Proměnná $END$ určuje, kde se má objevit po expandování kurzor. 
Speciální druh **live templates** jsou **surround templates**.
Surround templates jsou templates, které se používají tak, že označím kus kódu či mám kurzor na nějakém řádku 
(to se pak chová jako označení celého řádku) a stisknu `CTRL+ALT+T`, a vyberu přískušný template. Ten pak vybranou část obalí.
To je dobré napřílad pro obalování nějakým upraveným try-catchem, překladem apod. 
$SELECTION$ se uplatí pouze u **surround templates** a říká, kde se má v kódu objeví vybraný text.

Dále si můžeme definovat vlastní proměnné, které lze použít například k tomu, abychom napsali jednu věc na více míst 
najednou. Po expanzi kódu nám IDE nabídne vyplnit obsah jednotlivých proměnných. 
Pro pohyb k dalším proměnným stačí stisknout `ENTER` nebo `TAB`.
Například si uděláme zkratku na tvorbu tovární funkce.
Abbreviation: `com`  
Template text:
```php
/*
 return $CLASS$
 */
protected function createComponent$NAME$()
{
	return new $CLASS$($ARGS$);$END$
}
```
Takže po stisku com a `TAB` můžeme vyplnit název třídy. Název třídy se pak píše najednou na obě místa, kde je proměná $CLASS$. 
Po stisku `TAB` pak vyplňujeme proměnou $NAME$ a pak proměnnou $ARGS$.

Další příklad využívá $SELECTION$:
Abbreviation: `tra`  
Template text:
```php
$this->translator->translate($SELECTION$)
```
To obalí vybraný výraz překladovou funkcí.

U víceřádkových live templates (zvláště u surround templates) se občas může rozházet formátování, 
to lze ošetřit zaškrtnutím "Reformat according to style" v editaci live templatu.


## File templates

File templates lze spravovat v **File | Settings | Editor | File and code templates**. Tam můžete buď přidávat 
šablony vlastní nebo upravovat stávající. Pro tvorbu šablon se zde používá jazyk [velocity](http://velocity.apache.org/).
Bohužel nelze využít všechny možnosti jazyku velocity v kombinaci s vyplňováním proměnných při tvorbě nového souboru, 
jako napříklady cykly.

Ve file templates lze také používat proměnné a jejich hodnoty můžeme vyplnit ve chvíli vytváření nového souboru ze šablony.
Například jednoduchý file template na Nette Presenter

```php
<?php

#parse("PHP File Header.php")

#if ($❴Module❵ != "")
namespace App\\$❴Module❵Module\Presenters;
#elseif ($❴Namespace❵ != "")
namespace $❴Namespace❵;
#else
namespace App;
#end

use Nette;
#if ($❴NAME❵ == 'BasePresenter')
use Nette\Application\UI;

class $❴NAME❵ extends UI\Presenter
#else

class $❴NAME❵ extends BasePresenter
#end
{

#if ($❴Startup❵ != "")
	public function startup()
	{
		parent::startup();
	}
	
#end
#if ($❴Action❵ != "")
	public function action$❴Action❵()
	{
		
	}

#end
#if ($❴Handle❵ != "")
	public function handle$❴Handle❵()
	{
		
	}

#end
#if ($❴BeforeRender❵ != "")
	public function beforeRender()
	{
		
	}

#end
#if ($❴NAME❵ != 'BasePresenter')
	public function renderDefault()
	{
		
	}

#end
#if ($❴Render❵ != "")

## This just capitalizes the first letter. This line is a comment.
#set ($Capitalized = $Render.substring(0,1).toUpperCase() + $Render.substring(1))
	public function render$❴Capitalized❵()
	{
		
	}

#end
}


```

Při vytváření souboru ze šablony se pak objeví možnost vyplnit obsah proměnných.
Ten lze samozřejmě ponechat i prázdný, není potřeba vyplňovat vždy vše.

<div class="text-center">
    <img src="/assets/images/posts/2017/phpstorm/presenter.png">
    <br>
    <em>
        Formulář pro tvorbu presenteru
    </em>
</div>

<br>

Tento template umožní specifikovat modul, a pokud ten je prázdný, použije se namespace. 
Stejně tak je tam již zakotvená logika pro dědění u BasePresenteru.
Také je možné presenter rovnou vygenerovat s nějakou action, render nebo handle metod, aby je člověk nemusel psát, 
pokud ví, že je bude potřebovat. 
Ale pokud pole zůstanou prázdná, prezenter se vygeneruje bez action, render i handle metod.

Hned v prvním řádku je include PhpDocu. 
Direktiva `#parse` je vlastně jenom velocity alternativa php `include`. 


Jazyk velocity má pro PHP jednu drobnou nevýhodu. Znak `$` totiž interpretuje jako začátek proměnné. Znak `$` tedy musíme 
escapovat, a to takto `$❴DS❵`. Pokud to neuděláme, tak se nám `$id` objeví ve formuláři při tvorbě souboru jako proměnná.
Takže pokud chceme použít například ve file templatu nějaké entity toto `private $id;`, musíme to napsat následovně 
`private $❴DS❵id;`.

Zajímavé je, že ve file templatech lze používat javovské operace pro práci se stringy.
Tyto operace lz však používat jen uvnitř direktivy `#set`.
To je vidět v řádku začínajícím `#set`. `#set` je v jazyce velocity deklarace proměnné. Proměnné se uvnitř `#set` používají 
 bez `{}`. Jakmile proměnnou v `#set` deklarujeme, dále ji opět používáme obalenou `{}`.
  Jak je vidět, proměnná `$Capitalized` má v sobě stejný obsah jako proměnná `$Render`, ale první písmeno má velké.
 Pomocí práce se stringy takto můžeme do file templatů zakotvit různé drobnosti, které bychom jinak museli upravovat ručně, 
 což stojí čas.
 Věci, které ve file templatech nejsou na první pohled zřejmé, můžeme okomentovat. 
 `##` totiž v jazyce velocity značí začátek komentáře a tento komentář se pak do textu nového souboru nepropíše.
Takto můžeme dokonce uvnitř templatů vykonávat [nahrazování pomocí regexů](https://intellij-support.jetbrains.com/hc/en-us/community/posts/206816669-How-to-use-Live-Templates-functions-in-File-Templates-). 

Dropná poznámka na konec: 
U editace file templates narazíte mimo jiné na `Enable Live Templates`. Toto pouze povolí využívání proměnných tak, 
že budeme moct jejich hodnoty vyplnit po vygenerování (a pohybovat se na další rychle pomocí `TAB`). 
Tyto nemají s live templaty jakožto kompletací kódu pod zkratkami, nic společného, což je na první pohled matoucí.

Jak využíváte šablony vy? Máte pro ně nějaký zajímavý usecase?

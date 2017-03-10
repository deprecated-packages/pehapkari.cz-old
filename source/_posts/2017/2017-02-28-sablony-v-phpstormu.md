---
layout: post
title: "Live a file templates v PhpStormu"
perex: "Jak si usnadnit život používáním live a file templates v PhpStormu"
author: 15
---

### Proč šablony

Kód můžeme psát buď v IDE nebo v textovém editoru. IDE mají velkou výhodu v tom, že pomáhají s analýzou a kompletací kódu, 
takže programátor nemusí psát všechno, ale může si tvořit vlastní zkratky či využívat zkratky stávající.  
Zde budu rozebírat live a file templaty pro IDE PhpStorm. PhpStorm je sice placený, což může být pro některé čtenáře problém.
Studenti mohou získat licenci zdarma za ISIC, a ostatní mohou využít možnost [early access program](https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Early+Access+Program).

### Druhy šablon

V PhpStormu jsou dva druhy šablon. [Live templates](https://www.jetbrains.com/help/phpstorm/2016.3/live-templates-2.html)
 umožňují definovat zkratku (pár písmen), a k ní odpovídající kód.
Po napsání zkratky a stisknutí `TAB` se místo zkratky vloží k ní patřící kód (tj. expanduje live template). 

Druhým typem šablon jsou [file templates](https://www.jetbrains.com/help/phpstorm/2016.3/file-and-code-templates.html), 
což jsou soubory s nějakým předem daným obsahem. 
Hned od začátku máme k dispozici například tyto file templates: *HTML File*, *XHTML File*, *PHP File*, *PHP Class*.

## Live templates

Live templates lze spravovat v **File | Settings | Editor | Live templates**. Zajímavé na live templatech je to, 
že jsou context aware, tedy aktivují se pouze v určitém kontextu, a ne jinde, jak je vidět na následujícím obrázku:

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
Tlačítko, kterým expandujeme zkratku do definovaného kusu kódu, lze v tomto nastavení změnit z výchozího `TAB` na nějaké jiné.  
Live templates lze také vyvolat zkratkou `CTRL+J`, která ukáže všechny dostupné live templates.

Jednoduchý příklad live template může vypadat takto:  
Abbreviation: `tra`  
Template text:
```php
$this->translator->translate($WHAT$)$END$
```

Výše uvedený template zajistí obalení vybraného výrazu překladovou metodou, jak můžeme vidět v gifu níže.

<div class="text-center">
    <img src="/assets/images/posts/2017/phpstorm/live_template.gif">
    <br>
    <em>
        Použití live template pro překlad
    </em>
</div>

<br>

Speciální druh **live templates** jsou **surround templates**, ty slouží k obalení vybraného kusu kódu nějakým live templatem.
Používají se tak, že označím kus kódu, nebo mám kurzor na nějakém řádku (to se pak chová jako označení celého řádku) 
a stisknu `CTRL+ALT+T`. To mi zobrazí dostupné surround templates, a já vyberu příslušný template, který pak vybranou část obalí.
To je dobré například pro obalování nějakým upraveným try-catchem, překladem apod.  

V template textu (kde je kód, který se má po expandování zkratky objevit) můžeme používat proměnné.
Předdefinované proměnné jsou $END$ a $SELECTION$.  
Proměnná $END$ určuje, kde se objeví kurzor poté, co vyplníme všechny proměnné.  
$SELECTION$ se uplatní pouze u surround templates a říká, kde se má po expanzi live templatu objevit vybraný text.
Zároveň slouží k označení surround templates, protože každý live template, ve kterém je proměnná $SELECTION$, 
je automaticky považovaný i za surround template.

To si můžeme ukázat na překladovém template. Ten lze použít i jako surround template, pokud proměnnou $WHAT$ zaměníme za $SELECTION$.

```php
$this->translator->translate($SELECTION$)$END$
```

Použití pak vypadá následovně:

<div class="text-center">
    <img src="/assets/images/posts/2017/phpstorm/surround_template.gif">
    <br>
    <em>
        Použití surround template pro překlad
    </em>
</div>

<br>

Dále si můžeme definovat vlastní proměnné. Ty můžeme použít například k tomu, abychom napsali jednu věc na více míst najednou. 
Po expanzi templatu nám IDE nabídne vyplnit obsah jednotlivých proměnných. 
Pro pohyb k dalším proměnným stačí stisknout `ENTER` nebo `TAB`.  
Teď si ukážeme live template na tvorbu fluent setteru. To je setter, který vypadá například takto:

```php
	/**
	 * @param $bar
	 * @return $this
	 */
	public function setBar($bar) {
		$this->bar = $bar;
		return $this;
	}
```

U takového setteru pro například proměnnou `$bar `chceme, aby v názvu metody bylo setBar, s velkým B, 
ale jinde aby byl název proměnné malými písmeny. Toho dosáhneme přes funkce pro live templates.  
Když klikneme na `Edit variables`, je nám nabídnuto proměnným přiřadit funkce závisející na jiných proměnných.
Nastavení takového live templatu můžeme vidět na následujícím obrázku:

<div class="text-center">
    <img src="/assets/images/posts/2017/phpstorm/live-template.png">
    <br>
    <em>
        Live template s kapitalizací
    </em>
</div>

<br>

Tedy po napsání `fset` a stisku `TAB` můžeme vyplnit název property, pro kterou má setter být.  
Například když napíšeme `bar`, tak ji to doplní do všech výskytů proměnné `$Name$` a v názvu metody bude `setBar`. 
O to se nám stará funkce capitalize(Name), která používá obsah proměnné `$Name$`.  
Je-li zaškrtnuté `Skip if defined`, znamená to, že nám PhpStorm nenabídne vyplnit hodnotu `$Cap$`, pokud je k ní přiřazena nějaká funkce.  
Když chceme v live templatech používat znak `$` těsně před názvem proměnné, musíme ho escapovat zdvojením, takto: `$$`.

U víceřádkových live templates (zvláště u surround templates) se občas může rozházet formátování, 
to ošetříme zaškrtnutím "Reformat according to style" v editaci live templatu.


## File templates

File templates lze spravovat ve **File | Settings | Editor | File and code templates**. 
Tam můžete buď přidávat vlastní šablony nebo upravovat stávající. Pro tvorbu šablon se zde používá jazyk [velocity](http://velocity.apache.org/).
Bohužel nelze využívat všech vlastností tohoto jazyka v kombinaci s vyplňováním proměnných při tvorbě nového souboru (např. cykly).

Ve file templates se také dají používat proměnné. Jejich hodnoty můžeme vyplnit ve chvíli vytváření nového souboru ze šablony.

Nyní si ukážeme jednoduchý příklad pro Nette class:
```php
<?php

#parse("PHP File Header.php")

#if ($❴Namespace❵ != "")
namespace $❴Namespace❵;
#else
namespace App;
#end
 
use Nette;

class $❴NAME❵
{
	use Nette\SmartObject;
	
	public function __construct()
	{
	
	}
	
}
```

A takto pak vyplníme proměnné při tvorbě nové třídy.

<div class="text-center">
    <img src="/assets/images/posts/2017/phpstorm/nette-class.png">
    <br>
    <em>
        Formulář pro tvorbu třídy
    </em>
</div>

<br>

V prvním řádku je include PhpDocu. Direktiva `#parse` je vlastně jenom velocity alternativa php konstruktu `include`. 

Díky větvení můžeme specifikovat namespace pro novou třídu, a pokud namespace nevyplníme, použije se defaultní, tedy `App`.
Proměnná `$❴NAME❵` má stejnou hodnotu jako File name.
Při vytváření souboru ze šablony se objeví možnost vyplnit obsah proměnných, jak je vidět na obrázku.
Proměnná File name je povinná, ale ostatní proměnné můžeme ponechat prázdné.

A nyní se podíváme na další příklad, kde si vyzkoušíme složitější konstrukty jazyka velocity.

Takto by mohl vypadat file template pro Nette presenter:

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

#if ($❴Action❵ != "")
	public function action$❴Action❵()
	{
		
	}

#end
#if ($❴NAME❵ != 'BasePresenter')
	public function renderDefault()
	{
		
	}

#end
#if ($❴Render❵ != "")

## Na následucíjím řádku kapitalizujeme obsah proměnné $Render. Tento řádek je pouze komentář.
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
Stejně tak je tam již zakotvena logika pro dědění u BasePresenteru.
Také je možné presenter rovnou vygenerovat s nějakou z action, render nebo handle metod, abychom je nemuseli psát ručně. 
Nicméně pokud pole zůstanou prázdná, presenter se vygeneruje bez nich.

Jazyk velocity má pro PHP jednu drobnou nevýhodu. Znak `$` totiž interpretuje jako začátek proměnné, 
a proto jej musíme escapovat jako `$❴DS❵`. Pokud to neuděláme, tak se nám `$id` objeví ve formuláři při tvorbě souboru jako proměnná.
Pokud tedy chceme použít například ve file templatu nějakého objektu toto: `private $id;`, musíme to napsat následovně: 
`private $❴DS❵id;`.

Zajímavé je, že ve file templatech lze používat javovské operace pro práci se stringy.
Tyto operace lze však používat jen uvnitř direktivy `#set`, což můžeme vidět ve výše uvedeném příkladu. 
`#set` je v jazyce velocity deklarace proměnné. Proměnné se uvnitř používají bez `{}`. 
Jakmile proměnnou deklarujeme, dále ji opět používáme obalenou `{}`.  

Jak je vidět, proměnná `$Capitalized` má v sobě stejný obsah jako proměnná `$Render`, ale první písmeno má velké. 
Pomocí práce se stringy takto můžeme do file templatů zakotvit různé drobnosti, které bychom jinak museli upravovat ručně, 
což stojí čas.
Takto můžeme dokonce uvnitř templatů vykonávat [nahrazování pomocí regulárních výrazů](https://intellij-support.jetbrains.com/hc/en-us/community/posts/206816669-How-to-use-Live-Templates-functions-in-File-Templates-).  
Věci, které ve file templatech nejsou na první pohled zřejmé, můžeme okomentovat. 
`##` totiž v jazyce velocity značí začátek komentáře a tento komentář se pak do textu nového souboru nepropíše.

Někdy je užitečné upravovat již existující file templates. Pokud používáme PHP 7, 
můžeme si například do `PHP File Header` přidat řádek `declare(strict_types=1);`.  

Drobná poznámka na konec: 
U editace file templates narazíte mimo jiné na `Enable Live Templates`. Toto pouze povolí využívání proměnných tak, 
že budeme moct jejich hodnoty vyplnit po vygenerování (a pohybovat se na další rychle pomocí `TAB`). 
Tyto nemají s live templaty jakožto kompletací kódu pod zkratkami nic společného, což je na první pohled matoucí.

Jak využíváte šablony vy? Máte pro ně nějaký zajímavý use case?

---
layout: post
title: "Live a file templates v PhpStormu"
perex: "Jak si usnadnit život používáním live a file templates v PhpStormu"
author: 15
---

### Proč šablony

Kód můžeme psát buď v IDE nebo v textovém editoru. IDE mají velkou výhodu v tom, že pomáhají s analýzou a kompletací kódu.
Takže programátor nemusí psát všechno, ale může si tvořit vlastní zkratky či využívat zkratky stávající. 
Zde budu rozebírat live a file templaty pro IDE PhpStorm. PhpStorm je sice placený, což může být pro některé čtenáře problém, 
ale pro studenty je možnost za ISIC lze získat licenci zdarma.

### Kdy používat šablony? 
Nejvíce se šabony hodí buď na velkých projektech, kde se část kódu často opakuje (controllery, DTO, entity, query objecty apod.), 
 nebo při časté tvorbě malých, velmi podobných projektů, kde se opakují části kódu, které nejde vyčlenit do nějakého sandboxu.

V PhpStormu jsou dva druhy šablon. [Live templates](https://www.jetbrains.com/help/phpstorm/2016.3/live-templates-2.html)
 umožňují definovat zkratku (pár písmen), a k ní odpovídající kód.
Po napsání zkratky a stisknutí `TAB` se místo zkratky vloží k ní patřící kód. 

Dále jsou k dispozici [file templates](https://www.jetbrains.com/help/phpstorm/2016.3/file-and-code-templates.html)
, což jsou soubory s nějakým předem daným obsahem. 
Defaultně existují například tyto file templates: *HTML File*, *XHTML File*, *PHP File*, *PHP Class*.
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
Speciální druh **live templates** jsou **surround templates**, ty slouží k obalení vybraného kusu kódu nějakým live templatem.
Používají se tak, že označím kus kódu či mám kurzor na nějakém řádku (to se pak chová jako označení celého řádku) 
a stisknu `CTRL+ALT+T`. To mi zobrazí dostupné surround templates, a já vyberu příslušný template. Ten pak vybranou část obalí.
To je dobré například pro obalování nějakým upraveným try-catchem, překladem apod.  

V template textu (kde je kód, který se má po expandování zkratky objevit) můžeme používat proměnné.
Defaultně jsou definované proměnné $END$ a $SELECTION$.  
Proměnná $END$ určuje, kde se má po expandování objevit kurzor.  
$SELECTION$ se uplatní pouze u surround templates a říká, kde se má v kódu objeví vybraný text.
Zároveň slouží k označení surround templates, protože každý live template, ve kterém je proměnná $SELECTION$, 
je automaticky považovaný i za surround template.

Dále si můžeme definovat vlastní proměnné. Ty můžeme použít například k tomu, abychom napsali jednu věc na více míst 
najednou. Po expanzi kódu nám IDE nabídne vyplnit obsah jednotlivých proměnných. 
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

U takového setteru pro například proměnnou `$bar `chceme, aby v názvu metody bylo setBar, s velkým B, ale jinde aby byl 
název proměnné malými písmeny. Toho dosáhneme přes funkce pro live templates.  
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

Takže po napsání `se` a stisku `TAB` můžeme vyplnit název property, pro kterou má setter být.  
Takže když napíšeme napříkat `bar`, tak ji to všude doplné a v názvu metody bude `setBar`. 
O to se nám stará funkce capitalize(Name), která používá obsah proměnné `$Name$`.  
Když je zaškrtnuté `Skip if defined`, znamená to, že nám PhpStorm nenabídne vyplnit hodnotu `$Cap$`, pokud je k ní přiřazena nějaká funkce.  
Když chceme v live templatech používat znak `$` těsně před názvem proměnné, musíme ho escapovat zdvojením, takto: `$$`.

Další příklad využívá $SELECTION$:  
Abbreviation: `tra`  
Template text:
```php
$this->translator->translate($SELECTION$)
```
To obalí vybraný výraz překladovou funkcí.

U víceřádkových live templates (zvláště u surround templates) se občas může rozházet formátování, 
to ošetříme zaškrtnutím "Reformat according to style" v editaci live templatu.


## File templates

File templates lze spravovat v **File | Settings | Editor | File and code templates**. Tam můžete buď přidávat 
šablony vlastní nebo upravovat stávající. Pro tvorbu šablon se zde používá jazyk [velocity](http://velocity.apache.org/).
Bohužel nejde využít všechny možnosti jazyku velocity v kombinaci s vyplňováním proměnných při tvorbě nového souboru, 
jako například cykly.

Ve file templates se také dají používat proměnné. Jejich hodnoty můžeme vyplnit ve chvíli vytváření nového souboru ze šablony.
Například jednoduchý file template na Nette Presenter:

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

## Na následucíjím řádku kapitalizujeme obsah proměnné $Render. Tenhle řádek je pouze komentář.
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
Ale pokud pole zůstanou prázdná, presenter se vygeneruje bez action, render i handle metod.

Hned v prvním řádku je include PhpDocu. Direktiva `#parse` je vlastně jenom velocity alternativa php `include`. 

Jazyk velocity má pro PHP jednu drobnou nevýhodu. Znak `$` totiž interpretuje jako začátek proměnné a proto jej musíme 
escapovat, takto: `$❴DS❵`. Pokud to neuděláme, tak se nám `$id` objeví ve formuláři při tvorbě souboru jako proměnná.
Takže pokud chceme použít například ve file templatu nějaké entity toto: `private $id;`, musíme to napsat následovně: 
`private $❴DS❵id;`.

Zajímavé je, že ve file templatech lze používat javovské operace pro práci se stringy.
Tyto operace lze však používat jen uvnitř direktivy `#set`. To je vidět v řádku pod komentářem. 
`#set` je v jazyce velocity deklarace proměnné. Proměnné se uvnitř používají bez `{}`. 
Jakmile proměnnou deklarujeme, dále ji opět používáme obalenou `{}`.  

Jak je vidět, proměnná `$Capitalized` má v sobě stejný obsah jako proměnná `$Render`, ale první písmeno má velké. 
Pomocí práce se stringy takto můžeme do file templatů zakotvit různé drobnosti, které bychom jinak museli upravovat ručně, 
což stojí čas.
Takto můžeme dokonce uvnitř templatů vykonávat [nahrazování pomocí regulárních výrazů](https://intellij-support.jetbrains.com/hc/en-us/community/posts/206816669-How-to-use-Live-Templates-functions-in-File-Templates-).  
Věci, které ve file templatech nejsou na první pohled zřejmé, můžeme okomentovat. 
`##` totiž v jazyce velocity značí začátek komentáře a tento komentář se pak do textu nového souboru nepropíše.

Drobná poznámka na konec: 
U editace file templates narazíte mimo jiné na `Enable Live Templates`. Toto pouze povolí využívání proměnných tak, 
že budeme moct jejich hodnoty vyplnit po vygenerování (a pohybovat se na další rychle pomocí `TAB`). 
Tyto nemají s live templaty jakožto kompletací kódu pod zkratkami nic společného, což je na první pohled matoucí.

Jak využíváte šablony vy? Máte pro ně nějaký zajímavý use case?

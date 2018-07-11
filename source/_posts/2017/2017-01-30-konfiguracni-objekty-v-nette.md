---
id: 17
title: "Konfigurační objekty v Nette"
perex: "Jak se poprat s předáním konfigurace službě z config.neon? A jak k tomu využít Nette DI?"
author: 12
tested: true
test_slug: NetteConfigObjects
tweet: "Urodilo se na blogu: Konfigurační objekty v #NetteFw #config"
---

## Jak se to běžně dělává?

Mějme hypotetickou třídu `InvoiceForm`. Formulář má vlastní šablonu, nemůžeme
tedy napsat pouhou továrnu, potřebujeme komponentu. A k ní továrnu. Navíc ale
chceme formuláři předat z `config.neon` nějaké výchozí hodnoty. Jak by takový
kód vypadal?

**Formulář**
```php
declare(strict_types = 1);

namespace App\Forms;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;


final class InvoiceForm extends Control
{
    /**
     * @var array
     */
    private $config;


    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function createComponentInvoiceForm(): Form
    {
        $form = new Form;

        $form->addText('maturity', 'Splatnost')
            ->setDefaultValue($this->config['defaultMaturity']);

        return $form;
    }

    public function render()
    {
        $this->getTemplate()->render(__DIR__ . '/InvoiceForm.latte');
    }
}
```

**Továrna**
```php
declare(strict_types = 1);

namespace App\Forms;


final class InvoiceFormFactory
{
    /**
     * @var array
     */
    private $config;


    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(): InvoiceForm
    {
        return new InvoiceForm($this->config);
    }
}
```

**Šablona**
```smarty
<form n:name="invoiceForm">
    {* naše vlastní vykreslení formuláře *}
</form>
```

V `config.neon` pak musíme továrnu formuláře zaregistrovat a předat jí potřebné parametry.

```yaml
parameters:
    invoicing:
        defaultMaturity: 7
        pdfDirectory: %appDir%/../invoices

services:
    - App\Forms\InvoiceFormFactory( %invoicing% )
```

## Jak to udělat lépe?

Místo běžného pole si na konfiguraci vytvoříme objekt, který bude konfiguraci
našemu formuláři zprostředkovávat.

```php
declare(strict_types = 1);

namespace App\Config;

use Nette\Utils\ArrayHash;


abstract class AbstractConfig extends ArrayHash
{
    public function __construct(array $arr)
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $this->$key = ArrayHash::from($value, TRUE);
            } else {
                $this->$key = $value;
            }
        }
    }
}
```

Z tohoto objektu pak podědíme konfiguraci pro náš formulář. Můžeme také do třídy
přidat metody, které budou s naší konfigurací pracovat. Např. metodu `getPdfPath()`,
kterou později využijeme v třídě na generování PDF. Konfigurační třída tedy není
jen jednorázová, předáme její pomocí fakturační konfiguraci více službám.

```php
declare(strict_types = 1);

namespace App\Config;


/**
 * @property int    $defaultMaturity
 * @property string $pdfDirectory
 */
final class InvoicingConfig extends AbstractConfig
{
    public function getPdfPath(int $invoiceId): string
    {
        return vsprintf('%s/%s.pdf', [$this->pdfDirectory, $invoiceId]);
    }
}
```

Nyní můžeme třídu `InvoiceForm` upravit, aby nového konfiguračního objektu
využila. Všimněte si také metody `setDefaultValue()`. Díky `ArrayHash` nyní
můžeme ke konfiguraci přistupovat jako k objektu.

```php
declare(strict_types = 1);

namespace App\Forms;

use App\Config\InvoicingConfig;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;


final class InvoiceFormNew extends Control
{
    /**
     * @var InvoicingConfig
     */
    private $config;


    public function __construct(InvoicingConfig $config)
    {
        $this->config = $config;
    }

    protected function createComponentInvoiceForm(): Form
    {
        $form = new Form;

        $form->addText('maturity', 'Splatnost')
            ->setDefaultValue($this->config->defaultMaturity);

        return $form;
    }

    public function render()
    {
        $this->getTemplate()->render(__DIR__ . '/InvoiceForm.latte');
    }
}
```

Můžeme se také zbavit vlastní implementace továrny a nahradit ji interfacem.
Nette nám pak továrnu vygeneruje samo.

```php
declare(strict_types = 1);

namespace App\Forms;


interface InvoiceFormFactoryInterface
{
    public function create(): InvoiceForm;
}
```

Nakonec vše zaregistrujeme v `config.neon`. Všimněte si také absence sekce `parameters`.

```yaml
services:
    - App\Config\InvoicingConfig({
        defaultMaturity: 7
        pdfDirectory: %appDir%/../invoices
    })
    - App\Forms\InvoiceFormFactoryInterface
```

Co na závěr? Tohle jistě není definitivní řešení. Dalo by se lecjak rozšířit.
Např. dodělat validace hodnot vstupujících do konfiguračních objektů. Tím by
však byl článek již moc složitý. Jeho pointou je ukázat alternativní přístup
předávání konfigurace službám. A jak to ve svých aplikacích děláte vy?

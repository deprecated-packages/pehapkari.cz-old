<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\NetteConfigObjects\Forms;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * @method Template getTemplate()
 */
final class InvoiceFormOld extends Control
{
    /**
     * @var mixed[]
     */
    private $config = [];

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function render(): void
    {
        $this->getTemplate()->render(__DIR__ . '/InvoiceForm.latte');
    }

    protected function createComponentInvoiceForm(): Form
    {
        $form = new Form;

        $form->addText('maturity', 'Splatnost')
            ->setDefaultValue($this->config['defaultMaturity']);

        return $form;
    }
}

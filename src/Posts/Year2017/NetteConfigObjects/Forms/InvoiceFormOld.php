<?php

declare(strict_types = 1);

namespace Pehapkari\Website\Posts\Year2017\NetteConfigObjects\Forms;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

final class InvoiceFormOld extends Control
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

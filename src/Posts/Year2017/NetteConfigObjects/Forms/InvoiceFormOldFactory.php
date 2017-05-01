<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\NetteConfigObjects\Forms;

final class InvoiceFormOldFactory
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(): InvoiceFormOld
    {
        return new InvoiceFormOld($this->config);
    }
}

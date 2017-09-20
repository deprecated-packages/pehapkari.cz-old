<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\NetteConfigObjects\Forms;

final class InvoiceFormOldFactory
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

    public function create(): InvoiceFormOld
    {
        return new InvoiceFormOld($this->config);
    }
}

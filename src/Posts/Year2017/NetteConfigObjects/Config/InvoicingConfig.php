<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\NetteConfigObjects\Config;

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

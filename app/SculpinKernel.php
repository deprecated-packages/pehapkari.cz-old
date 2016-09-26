<?php

use Sculpin\Bundle\SculpinBundle\HttpKernel\AbstractKernel;

class SculpinKernel extends AbstractKernel
{
    /**
     * {@inheritdoc}
     */
    protected function getAdditionalSculpinBundles()
    {
        return [];
    }
}

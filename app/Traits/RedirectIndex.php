<?php

namespace App\Traits;

trait RedirectIndex
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
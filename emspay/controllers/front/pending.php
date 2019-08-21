<?php

class emspayPendingModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('pending.tpl');
    }
}

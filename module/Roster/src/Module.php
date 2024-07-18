<?php
declare(strict_types=1);

namespace Roster;

class Module
{
    public function getConfig() : array
    {
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }
}
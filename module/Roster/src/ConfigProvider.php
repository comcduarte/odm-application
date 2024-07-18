<?php
declare(strict_types=1);

namespace Roster;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }
    
    public function getDependencies() : array
    {
        
    }
}
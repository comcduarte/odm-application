<?php
declare(strict_types=1);

namespace Session;

class Module
{
    const TITLE = 'Session';
    const VERSION = '0.0.1';
    
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        
        $config = include __DIR__ . '/../config/module.config.php';
        
        $config['router'] = $provider->getRouterConfig();
        $config['service_manager'] = $provider->getDependencyConfig();
        $config['acl'] = $provider->getAclConfig();
        $config['navigation'] = $provider->getNavigationConfig();
        
        return $config;
    }
}
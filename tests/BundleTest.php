<?php
use Miloun\Cosmo\Bundle;
use Miloun\Cosmo\CosmoException;
use PHPUnit\Framework\TestCase;

class BundleTest extends TestCase
{
    public function testHasBundle()
    {
        $this->assertTrue(Bundle::hasBundle('fa'));
        $this->assertFalse(Bundle::hasBundle('foo'));
    }

    public function testMissingResourceThrowsCosmoException()
    {
        $bundle = new Bundle('en', Bundle::LOCALE);
        $this->expectException(CosmoException::class);
        $bundle->get('__cosmo_missing_resource__');
    }
}

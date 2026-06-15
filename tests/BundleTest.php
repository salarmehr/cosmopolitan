<?php
use Miloun\Cosmo\Bundle;
use PHPUnit\Framework\TestCase;

class BundleTest extends TestCase
{
    public function testHasBundle()
    {
        $this->assertTrue(Bundle::hasBundle('fa'));
        $this->assertFalse(Bundle::hasBundle('foo'));
    }
}

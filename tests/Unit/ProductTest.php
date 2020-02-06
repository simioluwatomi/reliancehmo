<?php

namespace Tests\Unit;

use App\Products;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @internal
 *
 *
 * @coversNothing
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Products::create([
            'name'  => 'This is a sample product',
            'price' => '1000',
        ]);
    }

    /** @test */
    function a_product_has_a_name()
    {
        $this->assertEquals('This is a sample product', $this->product->name);
    }

    /** @test */
    function a_product_has_a_price()
    {
        $this->assertEquals('1000', $this->product->price);
    }
}

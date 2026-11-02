<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AlmacenamientoImpresora;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\SalidaAlmacenamientoImpresora;

class StockMinAccessorTest extends TestCase
{
    /**
     * Test stock_min accessor on AlmacenamientoImpresora
     */
    public function testStockMinOnAlmacenamientoImpresora()
    {
        $model = AlmacenamientoImpresora::with('cartuchos')->first();
        if ($model) {
            $this->assertNotNull($model->stock_min, 'stock_min should not be null on AlmacenamientoImpresora');
            $this->assertEquals($model->cartuchos->stock_min, $model->stock_min, 'stock_min should match cartucho stock_min');
        } else {
            $this->markTestSkipped('No AlmacenamientoImpresora records available for testing.');
        }
    }

    /**
     * Test stock_min accessor on DistribucionAlmacenamientoImpresora
     */
    public function testStockMinOnDistribucionAlmacenamientoImpresora()
    {
        $model = DistribucionAlmacenamientoImpresora::with('almacenamientoImpresora.cartuchos')->first();
        if ($model) {
            $this->assertNotNull($model->stock_min, 'stock_min should not be null on DistribucionAlmacenamientoImpresora');
            $this->assertEquals($model->almacenamientoImpresora->cartuchos->stock_min, $model->stock_min, 'stock_min should match cartucho stock_min');
        } else {
            $this->markTestSkipped('No DistribucionAlmacenamientoImpresora records available for testing.');
        }
    }

    /**
     * Test stock_min accessor on SalidaAlmacenamientoImpresora
     */
    public function testStockMinOnSalidaAlmacenamientoImpresora()
    {
        $model = SalidaAlmacenamientoImpresora::with('almacenamientoImpresora.cartuchos')->first();
        if ($model) {
            $this->assertNotNull($model->stock_min, 'stock_min should not be null on SalidaAlmacenamientoImpresora');
            $this->assertEquals($model->almacenamientoImpresora->cartuchos->stock_min, $model->stock_min, 'stock_min should match cartucho stock_min');
        } else {
            $this->markTestSkipped('No SalidaAlmacenamientoImpresora records available for testing.');
        }
    }
}

?>

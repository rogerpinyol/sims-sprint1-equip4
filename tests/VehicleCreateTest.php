<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/models/Vehicle.php';

class VehicleCreateTest extends TestCase
{
    public function testCrearVehicleNormalitzaUbicacioICridaInsert()
    {
        $data = [
            'vin' => 'WF0TEST000000001',
            'model' => 'Model X',
            'battery_capacity' => 75,
            'status' => 'available',
            'location' => 'POINT(41.3851 2.1734)'
        ];

            // Mock parcial: evitar executar el constructor (sense BD) i interceptar insert()
        $mock = $this->getMockBuilder(Vehicle::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['insert'])
                     ->getMock();

        $captured = null;
        $mock->expects($this->once())
             ->method('insert')
             ->willReturnCallback(function ($d) use (&$captured) {
                 $captured = $d;
                 return 1; // simulate inserted id
             });

        $result = $mock->create($data);

            // Asserts: es retorna un id i la càrrega per a insert s'ha normalitzat
        $this->assertSame(1, $result);
        $this->assertEquals('WF0TEST000000001', $captured['vin']);
        $this->assertEquals('Model X', $captured['model']);
        $this->assertEquals(75, $captured['battery_capacity']);
        $this->assertEquals('available', $captured['status']);
        $this->assertMatchesRegularExpression('/POINT\s*\(\s*2\.1734\d*\s+41\.3851\d*\)/', $captured['location']);
    }
}

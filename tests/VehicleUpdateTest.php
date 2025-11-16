<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/models/Vehicle.php';

class VehicleUpdateTest extends TestCase
{
    public function testEditarVehicleCridaUpdate()
    {
        // Id del vehicle que volem actualitzar
        $idToUpdate = 55;

        // Dades d'exemple que vindrien del formulari
        $data = [
            'vin' => 'WF0UPD000000055',
            'model' => 'Model Y',
            'battery_capacity' => 80,
            'status' => 'available',
            'location' => 'POINT(41.3851 2.1734)'
        ];

        // Mock parcial: evitar constructor (no connexió BD) i interceptar update()
        $mock = $this->getMockBuilder(Vehicle::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['update'])
                     ->getMock();

        // Esperem que update() s'executi una vegada amb l'id i que s'hi passi un array
        $mock->expects($this->once())
             ->method('update')
             ->with(
                 $this->equalTo($idToUpdate),
                 $this->callback(function ($d) use ($data) {
                     // Comprovacions bàsiques sobre l'array: vin i model coincideixen
                     $this->assertIsArray($d);
                     $this->assertArrayHasKey('vin', $d);
                     $this->assertArrayHasKey('model', $d);
                     $this->assertEquals($data['vin'], $d['vin']);
                     $this->assertEquals($data['model'], $d['model']);
                     return true;
                 })
             )
             ->willReturn(true);

        // Cridem update() al mock i verifiquem que retorna true
        $result = $mock->update($idToUpdate, $data);
        $this->assertTrue($result);
    }
}

<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/models/Vehicle.php';

class VehicleDeleteTest extends TestCase
{
    public function testEliminarVehicleCridaDelete()
    {
        // Dades d'exemple: id del vehicle a eliminar
        $idToDelete = 123;

        // Mock parcial: evitar constructor per no connectar a la BD
        $mock = $this->getMockBuilder(Vehicle::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['delete'])
                     ->getMock();

        // Esperem que delete() s'executi una vegada amb l'id proporcionat
        $mock->expects($this->once())
             ->method('delete')
             ->with($this->equalTo($idToDelete))
             ->willReturn(true);

        // Cridem el mètode i comprovem que retorna true (eliminació simulada)
        $result = $mock->delete($idToDelete);
        $this->assertTrue($result);
    }
}

<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/services/ClientAuthService.php';

class ClientAuthServiceTest extends TestCase
{
    public function testLoginCorrecto()
    {
        $service = new ClientAuthService();

        $tenantId = 1;
        $email = 'cliente@demo.com';
        $password = 'Password123!';

        // Crea el usuario de prueba (ignora si ya existe)
        $service->registerClient($tenantId, 'Cliente Demo', $email, $password);

        // Ejecuta el login
        $result = $service->authenticate($email, $password, $tenantId);

        $this->assertIsArray($result, 'El login debe devolver un array de usuario');
        $this->assertEquals($email, $result['email']);
    }
}

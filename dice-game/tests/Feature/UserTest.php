<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $anotherUser;
    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $clientRepository = new PassportClientRepository();
        $clientRepository->createPersonalAccessClient(
            null, 'Test Personal Access Client', 'http://localhost'
        );

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->rol = 'admin';
        $this->admin->save();

   
        $this->adminToken = $this->admin->createToken('AdminToken')->accessToken;

        $this->anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'anotheruser@example.com',
            'password' => bcrypt('anotheruserpassword'),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test] 
    public function test_user_can_be_registered()
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/api/players', [
            'name' => 'TestUsuario',
            'email' => 'test@prueba.com',
            'password' => '12345'
        ]);

        $response->assertStatus(201);

        $this->assertCount(3, User::all());

        $user = User::orderBy('name', 'desc')->first();

        $this->assertEquals($user->name, 'TestUsuario');
        $this->assertEquals($user->email, 'test@prueba.com');
    }
}

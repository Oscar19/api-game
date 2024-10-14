<?php

namespace Tests\Feature;

use App\Models\User;
use Database\seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
   use RefreshDatabase;

    protected $admin;
    protected $anotherUser;
    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        //$this->artisan('db:seed', ['--class' => RoleSeeder::class]);

        $clientRepository = new PassportClientRepository();
        $clientRepository->createPersonalAccessClient(
            null, 'Test Personal Access Client', 'http://localhost'
        );

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin1@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->rol = 'admin';
       // $this->admin->assignRole('admin');
        $this->admin->save();

   
        $this->adminToken = $this->admin->createToken('AdminToken')->accessToken;

        $this->anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'anotheruser@example.com',
            'password' => bcrypt('anotheruserpassword'),
            'rol' => 'jugador',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test] 
    public function test_userRegister()
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/api/players', [
            'name' => 'TestUsuario',
            'email' => 'test@prueba.com',
            'password' => '12345',
        ]);

        $response->assertStatus(201);

        $this->assertCount(3, User::all());

        $user = User::orderBy('name', 'desc')->first();

        $this->assertEquals($user->name, 'TestUsuario');
        $this->assertEquals($user->email, 'test@prueba.com');
    }

    #[\PHPUnit\Framework\Attributes\Test] 
    public function test_user_login(){
        $user = User::factory()->create([
            'email' => 'test@prueba.com',
            'password' => bcrypt($password = '12345'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
        ]);
    }
  
    public function test_user_login_error(){
        $user = User::factory()->create([
            'email' => 'test@prueba.com',
            'password' => bcrypt($password = '12345'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'abcde',
        ]);

        $response->assertStatus(401)
        ->assertJson([
                     'error' => 'Los datos introducidos no son correctos',
                     'success' => false,
        ]);
    }
    public function test_updateUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;//con esto tenemos al usuario logueado

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/players/{$user->id}", [
            'name' => 'Sergio',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
        ]);
        $this->assertEquals('Sergio',
        $user->fresh()->name);//comprobamos que ha cambiado
    }
    public function test_updateOtherUser(){
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/players/{$this->anotherUser->id}", [
            'name' => 'Xavi',
        ]);
        $response->assertStatus(403)
        ->assertJson([
            'message' => 'No puedes cambiar el nickname de otro usuario.',
        ]);
        $this->assertNotEquals('Xavi', $this->anotherUser->fresh()->name);
    } 
    public function test_updatenull(){
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/players/{$this->anotherUser->id}", [
            'name' => '',
        ]);
        $response->assertStatus(403)
        ->assertJson([
            'message' => 'No puedes cambiar el nickname de otro usuario.',
        ]);
        $this->assertNotEquals('', $this->anotherUser->fresh()->name);
    }
    //zona admin
   
    public function test_adminRanking(){
        $admin = User::factory()->create([
            'rol' => 'admin',  
        ]);
    
        $adminToken = $admin->createToken('AdminToken')->accessToken;
    
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
        ])->getJson('/api/players/ranking');
    
        $response->assertStatus(200);
       // dd($response->json()); // ver la respuesta del servidor
        $response->assertJsonStructure([
            0 => [ 
                '*' => [
                    'id',
                    'name',
                    'success_rate',
                ]
            ]
        ]);
        
    }
    public function test_winner()
    {

    $adminToken = $this->admin->createToken('AdminToken')->accessToken;

    // Crear jugadores
    $player1 = User::factory()->create([
        'name' => 'Jugador1',
        'rol' => 'jugador',
        'success_rate' => 100,
    ]);
    $player2 = User::factory()->create([
        'name' => 'Jugador2',
        'rol' => 'jugador',
        'success_rate' => 80,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $adminToken,
    ])->getJson('/api/players/ranking/winner');

    //dd($response->json()); 

   
    $response->assertStatus(200);

  
    $response->assertJson([
        'winner' => $player1->name,
        'success_rate' => 100,
        'message' => 'Este es el jugador con el mejor porcentaje de éxito',
    ]);
        
}
public function test_loser()
{
    $adminToken = $this->admin->createToken('AdminToken')->accessToken;

    // Crear jugadores
    $player1 = User::factory()->create([
        'name' => 'Jugador1',
        'rol' => 'jugador',
        'success_rate' => 100,
    ]);
    $player2 = User::factory()->create([
        'name' => 'Jugador2',
        'rol' => 'jugador',
        'success_rate' => 80,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $adminToken,
    ])->getJson('/api/players/ranking/loser');

    $response->assertStatus(200);

    $response->assertJson([
       
        'message' => 'Este es el jugador con el peor porcentaje de éxito',
    ]);
}


}

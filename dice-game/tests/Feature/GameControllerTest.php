<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\seeders\RoleSeeder;
use Tests\TestCase;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use App\Models\User;
use App\Models\Game;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $adminToken;
    protected $otherUser;
    protected $game;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuarios
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        // Crear un juego asociado al primer usuario
        Game::create([
            'user_id' => $this->user->id,
            'dice1' => 3,
            'dice2' => 4,
            'winner' => true,
        ]);
    }
    #[\PHPUnit\Framework\Attributes\Test]
  
    public function test_userGame()
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/api/players/' . $this->user->id . '/games');

        $response->assertStatus(200);

        $this->game = Game::where('user_id', $this->user->id)->first();
        $this->assertNotNull($this->game);
        $this->assertEquals($this->user->id, $this->game->user_id);
    }
    public function test_userDeleteGames()
    {
        
        $response = $this->actingAs($this->user, 'api')->deleteJson('/api/players/' . $this->user->id . '/games');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tiradas eliminadas',
            ]);
    }
   
   
    public function test_otherUsersGames()
    {
        $response = $this->actingAs($this->otherUser, 'api')->postJson('/api/players/' . $this->user->id . '/games');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'No estás autorizado para crear juegos para este usuario',
            ]);
    }


   public function test_otherUserDeleteGames()
    {
        $response = $this->actingAs($this->otherUser, 'api')->deleteJson('/api/players/' . $this->user->id . '/games');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No estás autorizado para borrar juegos para este usuario',
            ]);
    }
    public function test_displayGames()
    {
    
        $user = $this->user;
        $game1 = $user->games()->create([
            'dice1' => 3,
            'dice2' => 4,
            'winner' => true,
        ]);

        $game2 = $user->games()->create([
            'dice1' => 2,
            'dice2' => 5,
            'winner' => false,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/players/' . $user->id . '/games');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'player' => $user->name,
            ])
            ->assertJsonFragment([
                'dice1' => 3,
                'dice2' => 4,
                'winner' => 1,
            ])
            ->assertJsonFragment([
                'dice1' => 2,
                'dice2' => 5,
                'winner' => 0,
            ]);
    }

    


}



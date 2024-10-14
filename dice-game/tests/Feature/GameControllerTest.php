<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Game;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
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

}



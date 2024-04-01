<?php

namespace Tests\Feature\Admin;

use App\Enums\Roles;
use App\Models\User;
use Database\Seeders\PermissionAndRolesSeeder;
use Database\Seeders\UsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'John ',
            'surname' => 'Doe',
            'email' => 'admin@23admin.com',
            'phone' => '38064565444',
            'birthdate' => '1999-03-13',
            'password' => 'password123',
            'password_confirmation' => 'password123',

        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'admin@23admin.com']);

        $user = User::where('email', 'admin@23admin.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_cannot_register_with_invalid_data()
    {
        $userData = [

        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function afterRefreshingDatabase()
    {
        $this->seed(PermissionAndRolesSeeder::class);
        $this->seed(UsersSeeder::class);
    }

    protected function getUser(Roles $role = Roles::ADMIN): User
    {
        return User::role($role->value)->firstOrFail();

    }
}

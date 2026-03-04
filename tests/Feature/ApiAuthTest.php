<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_login_returns_all_warehouses()
    {
        $w1 = Warehouse::create(['name' => 'W1', 'code' => 'W1', 'is_active' => true]);
        $w2 = Warehouse::create(['name' => 'W2', 'code' => 'W2', 'is_active' => true]);

        $admin = User::factory()->create([
            'user_type' => 'admin',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $admin->warehouses()->attach([$w1->id, $w2->id]);

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token', 'user', 'warehouses'])
            ->assertJsonCount(2, 'warehouses');
    }

    public function test_staff_login_returns_single_warehouse()
    {
        $w = Warehouse::create(['name' => 'W', 'code' => 'W', 'is_active' => true]);

        $staff = User::factory()->create([
            'user_type' => 'staff',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $staff->warehouses()->attach([$w->id]);

        $response = $this->postJson('/api/login', [
            'email' => $staff->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token', 'user', 'warehouse'])
            ->assertJsonMissing(['warehouses']);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Enums\Roles;
use App\Models\Product;
use App\Models\User;
use App\Services\FileStorageService;
use Database\Seeders\PermissionAndRolesSeeder;
use Database\Seeders\UsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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

    public function test_allow_see_product_with_role_admin()
    {
        $products = Product::factory(2)->create();
        $response = $this->actingAs($this->getUser())
            ->get(route('admin.products.index'));

        $response->assertSuccessful();
        $response->assertViewIs('admin.products.index');
        $response->assertSeeInOrder($products->pluck('title')->toArray());
    }
    public function test_does_note_allow_see_categories_with_role_customer()
    {

        $response = $this->actingAs($this->getUser(Roles::CUSTOMER))
            ->get(route('admin.products.index'));

        $response->assertStatus(403);
    }
    public function test_create_product_with_valid_date()
    {
        $data = Product::factory()->make()->toArray();

        $response = $this->actingAs($this->getUser())
            ->post(route('admin.products.index'),$data);


        $response->assertStatus(302);


    }
    public function test_create_product_with_invalid_date()
    {
        $data = ['title' => 'a'];

        $response = $this->actingAs($this->getUser())
            ->post(route('admin.products.index'),$data);


        $response->assertStatus(302);


    }
    public function test_editing_product_with_valid_data()
    {
        $product = Product::factory()->create();


        $updatedName = 'Phone 15 pro';

        $response = $this->actingAs($this->getUser())
            ->put(route('admin.products.update', $product), [
                'title' => $updatedName,
                'SKU' => 'new_sku_value',
                'price' => 10.99,
                'quantity' => 100
            ]);

        $response->assertStatus(302);


        $updatedCategory = Product::find($product->id);
        $this->assertEquals($updatedName, $updatedCategory->title);

    }
    public function test_editing_product_with_invalid_data()
    {
        $product = Product::factory()->create();


        $response = $this->actingAs($this->getUser())
            ->put(route('admin.products.update', $product), [
                'title' => 'Phone 15 pro',
                'SKU' => '',
                'price' => 10.99,
                'quantity' => 100
            ]);

        $response->assertStatus(302);
    }


    public function test_create_product(): void
    {
        $file =UploadedFile::fake()->image('test_image.png');

        $data  = array_merge
        (Product::factory()->make()->toArray(),
        ['thumbnail' =>$file]
        );

        $this->mock(
            FileStorageService::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('upload')
                    ->andReturn('image_uploaded.png');
            }
        );

        $this->actingAs(User::role('admin')->first())
            ->post(route('admin.products.store'),$data);

      $this->assertDatabaseHas(Product::class,[
          'title' => $data['title'],
          'thumbnail' => 'image_uploaded.png'
      ]);

    }
    public function test_create_product_with_invalid_image_format(): void
    {

        $file =UploadedFile::fake()->create('test_file.txt');

        $data  = array_merge(
            Product::factory()->make()->toArray(),
            ['thumbnail' => $file]
        );

        $this->actingAs(User::role('admin')->first())
            ->post(route('admin.products.store'), $data)
            ->assertSessionHasErrors(['thumbnail']);
    }
    public function test_update_product_with_valid_date()
    {
        $product = Product::factory()->create();




        $response = $this->actingAs($this->getUser())
            ->put(route('admin.products.update',$product),[
                'title' => $product->title,

            ]);


        $response->assertStatus(302);
        $product->refresh();


    }
    public function test_update_product_with_invalid_date()
    {
        $product = Product::factory()->create();


        $response = $this->actingAs($this->getUser())
            ->put(route('admin.products.update',$product),[
                'title' => "",

            ]);

        $response->assertStatus(302);

    }

    public function test_remove_product()
    {

        $product = Product::factory()->create();

        $this->assertDatabaseHas(Product::class,
            [
                'title' => $product->title
            ]);

        $response = $this->actingAs($this->getUser())
            ->delete(route('admin.products.destroy', $product));

        $response->assertStatus(302);
        $response->assertRedirectToRoute('admin.products.index');


        $this->assertDatabaseMissing(Product::class,
            [
                'title' => $product->title
            ]);
    }

    public function test_remove_product_invalid()
    {

        $product = Product::factory()->create();


        $this->assertDatabaseHas(Product::class, ['title' => $product->title]);

        $response = $this->actingAs($this->getUser())
            ->delete(route('admin.products.destroy', $product));

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing(Product::class, ['title' => $product->title]);

        $response = $this->actingAs($this->getUser())
            ->delete(route('admin.products.destroy', $product));

        $response->assertStatus(404);
    }

        protected function getUser(Roles $role = Roles::ADMIN):User
    {
        return User::role($role->value)->firstOrFail();

    }
}

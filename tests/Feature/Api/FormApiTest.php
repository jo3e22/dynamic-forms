<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Form;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test creating a form from JSON schema
     */
    public function test_can_create_form_from_schema(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'name' => 'Test Competition',
            'description' => 'A test form',
            'schema' => [
                [
                    'name' => 'full_name',
                    'type' => 'text',
                    'label' => 'Full Name',
                    'required' => true,
                ],
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => 'Email',
                    'required' => true,
                ],
                [
                    'name' => 'age_group',
                    'type' => 'select',
                    'label' => 'Age Group',
                    'required' => true,
                    'options' => ['U12', 'U14', 'U16'],
                ],
            ],
        ];

        $response = $this->postJson('/api/forms', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'form' => [
                    'id',
                    'code',
                    'name',
                    'api_key',
                    'embed_url',
                    'api_endpoint',
                    'schema',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('forms', [
            'user_id' => $this->user->id,
            'source' => 'api',
        ]);
    }

    /**
     * Test retrieving form schema
     */
    public function test_can_get_form_schema(): void
    {
        $form = Form::factory()
            ->for($this->user)
            ->create([
                'schema' => [
                    [
                        'name' => 'test_field',
                        'type' => 'text',
                        'label' => 'Test',
                        'required' => true,
                    ],
                ],
            ]);

        $response = $this->getJson("/api/forms/{$form->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $form->id,
                'code' => $form->code,
            ])
            ->assertJsonPath('schema.0.name', 'test_field');
    }

    /**
     * Test submitting form via API
     */
    public function test_can_submit_form_via_api(): void
    {
        $form = Form::factory()
            ->for($this->user)
            ->create([
                'status' => Form::STATUS_OPEN,
                'schema' => [
                    [
                        'name' => 'full_name',
                        'type' => 'text',
                        'label' => 'Full Name',
                        'required' => true,
                    ],
                    [
                        'name' => 'email',
                        'type' => 'email',
                        'label' => 'Email',
                        'required' => true,
                    ],
                ],
            ]);

        $payload = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $response = $this->postJson(
            "/api/forms/{$form->id}/submissions",
            $payload,
            ['X-API-Client' => 'test-client']
        );

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'submission' => [
                    'id',
                    'code',
                    'submitted_at',
                ],
            ]);

        $this->assertDatabaseHas('submissions', [
            'form_id' => $form->id,
            'source' => 'api',
            'api_client' => 'test-client',
        ]);
    }

    /**
     * Test getting submissions for a form
     */
    public function test_can_get_form_submissions(): void
    {
        Sanctum::actingAs($this->user);

        $form = Form::factory()
            ->for($this->user)
            ->has(\App\Models\Submission::factory()->count(3))
            ->create();

        $response = $this->getJson("/api/forms/{$form->id}/submissions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                    ],
                ],
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ])
            ->assertJsonPath('pagination.total', 3);
    }

    /**
     * Test updating form schema
     */
    public function test_can_update_form_schema(): void
    {
        Sanctum::actingAs($this->user);

        $form = Form::factory()
            ->for($this->user)
            ->create([
                'api_key' => 'form_sk_test123',
            ]);

        $payload = [
            'name' => 'Updated Name',
            'schema' => [
                [
                    'name' => 'new_field',
                    'type' => 'text',
                    'label' => 'New Field',
                    'required' => false,
                ],
            ],
        ];

        $response = $this->patchJson(
            "/api/forms/{$form->id}",
            $payload,
            ['Authorization' => 'Bearer form_sk_test123']
        );

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $form->refresh();
        $this->assertNotNull($form->schema);
        $this->assertEquals('new_field', $form->schema[0]['name']);
    }

    /**
     * Test form validation
     */
    public function test_form_submission_validates_fields(): void
    {
        $form = Form::factory()
            ->create([
                'status' => Form::STATUS_OPEN,
                'schema' => [
                    [
                        'name' => 'email',
                        'type' => 'email',
                        'label' => 'Email',
                        'required' => true,
                    ],
                ],
            ]);

        // Missing required field
        $response = $this->postJson("/api/forms/{$form->id}/submissions", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Invalid email
        $response = $this->postJson("/api/forms/{$form->id}/submissions", [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test API key authentication
     */
    public function test_can_access_submissions_with_api_key(): void
    {
        $form = Form::factory()
            ->for($this->user)
            ->has(\App\Models\Submission::factory())
            ->create([
                'api_key' => 'form_sk_test123',
            ]);

        $response = $this->getJson(
            "/api/forms/{$form->id}/submissions",
            ['Authorization' => 'Bearer form_sk_test123']
        );

        $response->assertStatus(200);
    }
}

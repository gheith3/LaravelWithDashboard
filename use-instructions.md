# Laravel 13 + Filament 5 Project Template

A production-ready Laravel 13 starter template combining a Filament 5 admin dashboard, a Livewire 3 public website, and a JWT-authenticated REST API. Pick the layers you need — the architecture supports all three together or any subset.

---

## Template Variants

| Variant | Layers active |
|---|---|
| Dashboard + Website | Filament admin + Livewire public pages |
| Dashboard + API | Filament admin + REST API (JWT) |
| Dashboard + Website + API | All three layers |

To remove a layer you don't need:
- **Remove Website** — delete `app/Livewire/`, `resources/views/livewire/`, `resources/views/layouts/`, `routes/web.php` routes, and the `post-card` anonymous component.
- **Remove API** — delete `app/Http/Controllers/Api/`, `app/Http/Requests/Api/`, `app/Http/Resources/Api/`, `app/Http/Middleware/ForceJsonResponse.php`, `app/Services/Auth*`, `app/Models/RefreshToken.php`, `routes/api.php`, and the `tests/Feature/Api/` folder.
- **Remove Website + API** — remove both above; keep only Filament.

> **The Post, Comment, and Tag models/resources are working examples** that demonstrate the full convention: migration → model → service → Filament resource → API controller → Pest tests. Delete them when starting a real project.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13, PHP 8.4 |
| Admin dashboard | Filament 5 |
| Public website | Livewire 3, Tailwind CSS v4 |
| REST API auth | JWT (`php-open-source-saver/jwt-auth`) |
| Database | PostgreSQL 16 (SQLite for tests) |
| File storage | AWS S3 / MinIO |
| Cache & queues | Redis (Predis) |
| Real-time | Pusher / Laravel Echo |
| Permissions | Spatie Laravel Permission + Filament Shield |
| Activity log | Spatie Laravel Activitylog |
| Schedule monitor | Spatie Laravel Schedule Monitor |
| Settings | Spatie Laravel Settings |
| Translations | Spatie Laravel Translatable |
| Search | Laravel Scout |
| PDF export | Barryvdh DomPDF |
| QR codes | SimpleSoftwareIO Simple QrCode |
| Excel export | Maatwebsite Excel |
| Images | Intervention Image |
| Email | Resend |
| API docs | Knuckleswtf Scribe |
| Testing | Pest 4 |
| Containerisation | Docker + Nginx + PHP-FPM + Supervisor |
| CI/CD | GitHub Actions |

---

## Project Structure

```
backend/
├── app/
│   ├── Console/Commands/        # Artisan commands (e.g. BackupDatabase)
│   ├── Enums/                   # Backed PHP enums
│   ├── Exceptions/
│   │   └── ServiceException.php # Thrown by services, caught by global handler
│   ├── Filament/
│   │   ├── Pages/               # Standalone Filament pages (Settings, Logs)
│   │   ├── Resources/           # One folder per resource
│   │   │   └── Posts/
│   │   │       ├── PostResource.php
│   │   │       ├── Pages/       # ListPosts, CreatePost, EditPost, ViewPost
│   │   │       ├── RelationManagers/
│   │   │       ├── RelationPages/
│   │   │       ├── Schemas/     # PostForm, PostInfolist (decoupled from resource)
│   │   │       └── Tables/      # PostsTable (decoupled from resource)
│   │   └── Traits/
│   ├── helpers.php              # Global helper functions (registered via composer.json)
│   ├── Http/
│   │   ├── Controllers/Api/     # Thin controllers — delegate to services
│   │   ├── Middleware/          # ForceJsonResponse (sets Accept: application/json)
│   │   ├── Requests/Api/        # FormRequest validation per endpoint
│   │   └── Resources/Api/       # JsonResource transformers for API responses
│   ├── Livewire/Website/        # Full-page Livewire components for public site
│   │   └── Components/          # Shared Livewire components (Navbar, Footer)
│   ├── Models/                  # Eloquent models
│   ├── Policies/                # Authorization policies (one per model)
│   ├── Providers/
│   │   └── Filament/
│   │       └── DashboardPanelProvider.php
│   ├── Services/                # Business logic layer
│   ├── Settings/                # Spatie Settings classes
│   └── Traits/
│       ├── ApiResponse.php      # success() / paginate() / error() response helpers
│       ├── HasActiveColumn.php  # active() / disabled() query scopes
│       └── HasUuidWithlog.php   # UUID primary keys + activity logging
├── database/
│   ├── factories/               # One factory per model
│   ├── migrations/
│   └── seeders/
├── lang/en/
│   └── api.php                  # All API-facing messages (grouped by domain)
├── resources/
│   ├── css/app.css              # Tailwind CSS v4 (uses @import 'tailwindcss')
│   ├── js/app.js
│   └── views/
│       ├── components/website/  # Anonymous Blade components
│       ├── filament/            # Custom Filament Blade views
│       ├── layouts/app.blade.php
│       └── livewire/website/
├── routes/
│   ├── api.php                  # Versioned API routes (/api/v1/...)
│   ├── console.php
│   └── web.php                  # Public website routes
├── tests/
│   ├── Feature/Api/             # API feature tests (Pest)
│   ├── Pest.php                 # Test configuration + shared helpers
│   └── TestCase.php
├── docker/                      # Nginx, PHP-FPM, Supervisor configs
├── Dockerfile
└── phpunit.xml
```

---

## Getting Started with This Template

### 1. Use the template

```bash
# Clone or copy the template
git clone <repo-url> my-new-project
cd my-new-project/backend

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
npm run build
```

### 2. Rename project-specific values

Search and replace these across the entire repository:

| Placeholder | Replace with |
|---|---|
| `LaravelApiWithDashboard` | Your project name (PascalCase) |
| `laravel_api_dashboard` | Your DB name (snake_case) |
| `laravel-api-dashboard` | Your project slug (kebab-case) |

Files that need renaming:
- `.env` / `.env.example` — `APP_NAME`, `DB_DATABASE`
- `docker-compose.yml` / `docker-compose.prod.yml` — service names, container names (`${PROJECT_NAME}`)
- `.github/workflows/*.yml` — image names, registry paths

### 3. Remove example domain

Delete the Post/Comment/Tag example before building your domain:

```bash
# Models
rm app/Models/Post.php app/Models/Comment.php app/Models/Tag.php app/Models/RefreshToken.php

# Filament resources
rm -rf app/Filament/Resources/Posts app/Filament/Resources/Comments

# API layer
rm -rf app/Http/Controllers/Api/Post* app/Http/Controllers/Api/Comment* app/Http/Controllers/Api/Tag*
rm -rf app/Http/Requests/Api/Post app/Http/Requests/Api/Comment
rm    app/Http/Resources/Api/Post* app/Http/Resources/Api/Comment* app/Http/Resources/Api/Tag*
rm    app/Services/PostService.php app/Services/CommentService.php

# Livewire website (if keeping website, rebuild; if removing, delete whole folder)
rm -rf app/Livewire/Website

# Factories, migrations, seeders
rm database/factories/PostFactory.php database/factories/CommentFactory.php database/factories/TagFactory.php
rm database/migrations/2026_05_16_*  database/migrations/2026_05_17_*

# Tests
rm -rf tests/Feature/Api
```

---

## Docker

Two compose files are provided:

| File | Purpose |
|---|---|
| `docker-compose.yml` | Local development (exposes ports directly) |
| `docker-compose.prod.yml` | Production (works behind Nginx Proxy Manager via `npm-network`) |

**Services included:** PHP-FPM app, Nginx, PostgreSQL, Redis, MinIO (S3-compatible storage).

### Local development

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

### Production (Portainer / VPS)

1. Copy `.env.prod.example` to `.env.prod` and fill in secrets.
2. Deploy `docker-compose.prod.yml` via Portainer or:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.prod up -d
```

The app container runs migrations automatically on startup via `docker/entrypoint.sh`.

---

## GitHub Actions CI/CD

Two workflows are pre-configured:

| Workflow | Trigger | Image tag |
|---|---|---|
| `build-and-push-test.yml` | Push to `test` | `:test` |
| `build-and-push-prod.yml` | Push to `production` | `:latest` |


---

## Standards — Adding a New Domain

Follow this sequence every time. The Post domain is the reference implementation.

### 1. Migration

- Use `uuid` primary key on all tables.
- Use `foreignUuid()->constrained()->cascadeOnDelete()` (never bare `foreign()`).
- Add `$table->softDeletes()` to all main entity tables.
- Add `$table->timestamps()` to all tables.
- Add indexes on columns used in `WHERE` clauses.

```php
Schema::create('products', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('created_by_id')->constrained('users')->cascadeOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->index('is_active');
});
```

### 2. Model

- Use `HasUuidWithlog` (gives UUID primary key + Spatie activity logging).
- Use `HasActiveColumn` (gives `active()` and `disabled()` query scopes) when has column `is_active`.
- Use `HasFactory` and `SoftDeletes`.
- Define `$fillable`, `$casts`, all relationships, and any `#[Scope]` methods.
- Set `created_by_id` via the `boot()` creating event if the model is user-owned.
- add scop scop function like :
```php
   #[Scope]
    public function byStatus(Builder $query, PostStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    #[Scope]
    public function byCreator(Builder $query, string $user_id): Builder
    {
        return $query->where('created_by_id', $user_id);
    }

    #[Scope]
    public function bySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }
```


```php
class Product extends Model
{
    use HasUuidWithlog, HasActiveColumn, HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'is_active', 'created_by_id'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Product $product) {
            if (empty($product->created_by_id)) {
                $product->created_by_id = auth()->id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
```

### 3. Enum

Use backed PHP enums for any column with a fixed set of values. Implement Filament's `HasLabel`, `HasColor`, `HasDescription` contracts.

```php
enum ProductStatus: string implements HasLabel, HasColor, HasDescription
{
    case Draft     = 'draft';
    case Published = 'published';

    public function getLabel(): string    { return match($this) { ... }; }
    public function getColor(): string    { return match($this) { ... }; }
    public function getDescription(): string { return match($this) { ... }; }
}
```

### 4. Factory

Always include all non-nullable foreign keys in the factory definition so tests can create records without needing an authenticated user.

```php
public function definition(): array
{
    return [
        'name'          => fake()->unique()->word(),
        'slug'          => fn (array $a) => Str::slug($a['name']),
        'is_active'     => true,
        'created_by_id' => User::factory(),
    ];
}
```

### 5. Service

- All write operations (create, update, delete) are wrapped in `try/catch`.
- On catch: `Log::error()` with class, message, file, line, and relevant IDs, then `throw new ServiceException(__('api.errors.server'))`.
- `findBy*` methods that produce 404 responses are **not** wrapped — let `ModelNotFoundException` propagate to the global handler.
- `findBy*` methods that should return 401/403 throw `new ServiceException(__('api.errors.*'), 401)` directly.

```php
class ProductService
{
    // Not wrapped — ModelNotFoundException → global 404 handler
    public function findPublished(string $slug): Product
    {
        return Product::query()->bySlug($slug)->active()->firstOrFail();
    }

    public function create(User $user, array $data): Product
    {
        try {
            $data['created_by_id'] = $user->id;
            return Product::create($data);
        } catch (\Throwable $e) {
            Log::error('ProductService::create failed', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'user_id'   => $user->id,
            ]);
            throw new ServiceException(__('api.errors.server'));
        }
    }
}
```

### 6. API Controller

Controllers are thin — they validate (via FormRequest), delegate to the service, and return a response using the `ApiResponse` trait.

```php
class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProductService $productService) {}

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create(auth('api')->user(), $request->validated());
        return $this->success(new ProductResource($product), __('api.products.created'), 201);
    }
}
```

### 7. API Response messages

Add a key group to `lang/en/api.php` for every new domain:

```php
'products' => [
    'retrieved' => 'Products retrieved',
    'show'      => 'Product retrieved',
    'created'   => 'Product created',
    'updated'   => 'Product updated',
    'deleted'   => 'Product deleted',
],
```

### 8. API Routes

Add routes to `routes/api.php` inside the `v1` prefix group. Apply throttle middleware to every group:

```php
// Public
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/products',        [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
});

// Authenticated
Route::middleware(['auth:api', 'throttle:20,1'])->group(function () {
    Route::post('/products',               [ProductController::class, 'store']);
    Route::match(['PUT', 'PATCH'], '/products/{slug}', [ProductController::class, 'update']);
    Route::delete('/products/{slug}',      [ProductController::class, 'destroy']);
});
```

> Use `Route::match(['PUT', 'PATCH'], ...)` for update endpoints so multipart form-data uploads work via `?_method=PUT`.

### 9. Filament Resource

Organise each resource in its own folder following the existing Posts resource structure:

```
app/Filament/Resources/Products/
├── ProductResource.php
├── Pages/
│   ├── ListProducts.php
│   ├── CreateProduct.php
│   ├── EditProduct.php
│   └── ViewProduct.php
├── Schemas/
│   ├── ProductForm.php      # Form schema (used in Create + Edit)
│   └── ProductInfolist.php  # Infolist schema (used in View)
└── Tables/
    └── ProductsTable.php    # Table schema (used in List)
```

Register the resource in `app/Providers/Filament/DashboardPanelProvider.php`.

### 10. Policy

Create a policy by running console command:

```bash
php artisan shield:generate --all --panel=dashboard --option=policies_and_permissions
```

### 11. Tests

**Every API endpoint must have a corresponding Pest test.** Tests live in `tests/Feature/Api/`.

```php
// tests/Feature/Api/ProductTest.php

describe('GET /api/v1/products', function () {
    it('returns paginated active products', function () {
        Product::factory()->count(3)->published()->create();
        Product::factory()->draft()->create();

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'meta']);
    });
});

describe('POST /api/v1/products', function () {
    it('creates a product for the authenticated user', function () {
        [, $token] = userWithToken();

        $this->withToken($token)
            ->postJson('/api/v1/products', ['name' => 'Widget', 'content' => '...'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Widget');
    });

    it('returns 401 without authentication', function () {
        $this->postJson('/api/v1/products', [])->assertStatus(401);
    });
});
```

Minimum tests per endpoint:

| Endpoint | Required tests |
|---|---|
| `GET /resource` | returns data, filters work, pagination/meta correct |
| `GET /resource/{id}` | returns record, 404 for unknown/wrong-status |
| `POST /resource` | creates record (201), 401 without auth, 422 on validation failure |
| `PUT /resource/{id}` | updates own record, 404 for another user's record, 401 without auth |
| `DELETE /resource/{id}` | soft-deletes own record, 404 for another user's record, 401 without auth |

Run tests:

```bash
php artisan test --testsuite=Feature

# Single file
php artisan test tests/Feature/Api/ProductTest.php
```

---

## Pest Testing Conventions

All tests are written with [Pest 4](https://pestphp.com/). Every developer adding an API endpoint must follow these conventions so the test suite stays consistent and readable.

### File & folder layout

```
tests/
├── Feature/
│   └── Api/
│       ├── AuthTest.php          # one file per resource / domain
│       ├── PostTest.php
│       ├── CommentTest.php
│       └── TagTest.php
├── Unit/                         # pure unit tests (no HTTP, no DB)
├── Pest.php                      # global config, traits, shared helpers
└── TestCase.php
```

- One test file per resource (named `{Domain}Test.php`).
- API feature tests always live under `tests/Feature/Api/`.
- Unit tests (service logic with no HTTP) go under `tests/Unit/`.

---

### `Pest.php` — shared configuration

```php
// tests/Pest.php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Apply RefreshDatabase to every Feature test automatically.
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// ── Shared helpers ────────────────────────────────────────────────────────────

/**
 * Create a user and return their JWT access token alongside the model.
 *
 * @return array{0: User, 1: string}
 */
function userWithToken(array $attributes = []): array
{
    $user  = User::factory()->create($attributes);
    $token = auth('api')->login($user);

    return [$user, $token];
}
```

Rules:
- Global helpers belong in `Pest.php`, not duplicated across test files.
- Add a docblock to every helper — the return type annotation `array{0: User, 1: string}` gives IDE support for destructuring.
- Keep helpers small and focused. If a helper needs more than ~10 lines, it should become a trait or a dedicated class.

---

### `describe()` — grouping tests

Group tests by **HTTP method + URL**, exactly as the route is defined.

```php
// ✅ correct
describe('POST /api/v1/auth/login', function () { ... });
describe('GET /api/v1/posts/{slug}', function () { ... });

// ❌ wrong — too vague, mixes concerns
describe('Auth tests', function () { ... });
describe('PostController', function () { ... });
```

Rules:
- One `describe()` block per endpoint.
- Never nest `describe()` more than one level deep.
- Order blocks top-to-bottom in the same order as routes in `api.php`.

---

### `it()` — naming tests

The name is the documentation. Write it as a plain-English sentence that completes "it ...":

```php
// ✅ correct — full sentence, describes observable behaviour
it('returns a new token pair and rotates the refresh token');
it('returns 401 for an expired refresh token');
it('soft-deletes the authenticated user\'s own post');
it('excludes inactive posts from the listing');
it('stores an optional phone number with the comment');

// ❌ wrong — too vague, uses test jargon, or duplicates the method name
it('test login');
it('works correctly');
it('store()');
it('testReturns401');
```

Naming rules:
- Always start with a **verb**: `returns`, `creates`, `updates`, `deletes`, `stores`, `excludes`, `fails`, `rejects`, `sets`, `sends`.
- Describe the **outcome** from the caller's perspective, not the implementation.
- Include the **condition** when it matters: `for an expired token`, `without authentication`, `when content is too short`.
- Use apostrophe escaping (`\'`) for possessives rather than rewording: `the authenticated user\'s own post`.
- No abbreviations, no camelCase, no underscores.

---

### Test structure — Arrange / Act / Assert

Every test follows the **AAA** pattern with a blank line between each phase:

```php
it('filters posts by tag slug', function () {
    // Arrange
    $user  = User::factory()->create();
    $tag   = Tag::factory()->create(['slug' => 'php']);
    $post  = Post::factory()->for($user, 'creator')->published()->create();
    $other = Post::factory()->for($user, 'creator')->published()->create();
    $post->tags()->attach($tag);

    // Act & Assert  (HTTP tests combine these — one request, chained assertions)
    $this->getJson('/api/v1/posts?tag=php')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $post->id);
});
```

Rules:
- Add a blank line between **Arrange** and **Act/Assert**.
- For HTTP tests, the request and its assertions are always chained — never store the response in a variable just to call one assertion on it.
- Store the response in a variable only when you need to reuse the data (e.g., extract a `refresh_token` to call a second request).

```php
// ✅ correct — store response only when reusing data
$loginRes        = $this->postJson('/api/v1/auth/login', [...]);
$oldRefreshToken = $loginRes->json('data.refresh_token');

$this->postJson('/api/v1/auth/refresh', ['refresh_token' => $oldRefreshToken])
    ->assertOk();

// ✅ correct — chain when only one request
$this->getJson('/api/v1/tags')
    ->assertOk()
    ->assertJsonCount(2, 'data');

// ❌ wrong — unnecessary variable
$response = $this->getJson('/api/v1/tags');
$response->assertOk();
```

---

### Assertions — use the most specific one

| Situation | Use |
|---|---|
| Check status code 200 | `->assertOk()` |
| Check status code 201 | `->assertStatus(201)` |
| Check any other status | `->assertStatus(404)` |
| Check a specific JSON value | `->assertJsonPath('data.slug', 'my-post')` |
| Check top-level JSON keys/values | `->assertJson(['success' => true, 'message' => '...'])` |
| Check JSON array length | `->assertJsonCount(3, 'data')` |
| Check JSON key presence/shape | `->assertJsonStructure(['data' => ['id', 'name']])` |
| Check DB row exists | `$this->assertDatabaseHas('posts', ['title' => 'X'])` |
| Check DB row absent | `$this->assertDatabaseMissing('posts', ['id' => $id])` |
| Check exact row count | `$this->assertDatabaseCount('refresh_tokens', 1)` |
| Check soft-deleted row | `$this->assertSoftDeleted('posts', ['id' => $post->id])` |
| Check value is null | `expect($user->fresh()->last_login_at)->toBeNull()` |
| Check value is not null | `expect($user->fresh()->last_login_at)->not->toBeNull()` |
| Check two values differ | `expect($newToken)->not->toBe($oldToken)` |

Avoid `assertSee()`, `assertContains()`, or manual `json_decode()` — use the dedicated JSON assertion methods instead.

---

### Variable naming

```php
// Single model — use the model name in singular
$user = User::factory()->create();
$post = Post::factory()->for($user, 'creator')->published()->create();
$tag  = Tag::factory()->create(['slug' => 'php']);

// Distinguish roles — use semantic names
$owner = User::factory()->create();   // the post creator
$other = User::factory()->create();   // an unrelated authenticated user

// Tokens — consistent names
[$user, $token]  = userWithToken();   // when you need both
[, $token]       = userWithToken();   // when you only need the token
```

Never use generic names like `$u`, `$p`, `$r`, `$data`.

---

### Factories — use states and relationships

```php
// ✅ use factory states
Post::factory()->published()->create();
Post::factory()->draft()->create();
Comment::factory()->accepted()->create();

// ✅ use for() to set relationships without coupling to auth()
Post::factory()->for($user, 'creator')->published()->create();
Comment::factory()->for($post)->accepted()->create();

// ✅ use count() for multiple records
Post::factory()->count(3)->for($user, 'creator')->published()->create();

// ❌ wrong — raw attributes instead of states
Post::factory()->create(['status' => PostStatus::Published]);

// ❌ wrong — assumes auth() is set; fragile in tests
Post::factory()->create();   // if factory has no created_by_id default
```

Always include FK columns (especially `created_by_id`) in the factory `definition()` so records can be created without a logged-in user.

---

### `beforeEach()` — shared setup

Use `beforeEach()` sparingly. Only use it for state that **every** test in the block needs:

```php
describe('GET /api/v1/posts/{slug}/comments', function () {
    // ✅ correct — all tests in this block need a published post
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->post = Post::factory()->for($this->user, 'creator')->published()->create();
    });

    it('returns only accepted comments', function () {
        Comment::factory()->count(2)->for($this->post)->accepted()->create();

        $this->getJson("/api/v1/posts/{$this->post->slug}/comments")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('returns 404 for a draft post', function () {
        // This test overrides the shared post — beforeEach was the wrong choice here
    });
});
```

When only **some** tests need shared setup, inline it. Do not use `beforeEach()` just to avoid repeating two lines.

---

### What to test — and what not to

**Test:**
- Every success path (correct status code, correct shape, correct DB state).
- Every auth boundary (`401` without token, `404` for another user's resource).
- Every validation rule that has a meaningful effect (`422` on missing required field, `422` on too-short content).
- Business rules: status filters, soft-deletes visible vs. hidden, token rotation.

**Do not test:**
- Laravel framework internals (routing, middleware plumbing).
- Third-party package behaviour (Filament rendering, Spatie permission resolution).
- Implementation details (which private method was called, which query ran).
- The same assertion twice in different tests.

---

### `phpunit.xml` — test environment

```xml
<php>
    <env name="APP_ENV"       value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE"   value=":memory:"/>
    <env name="BCRYPT_ROUNDS" value="4"/>          <!-- faster password hashing -->
    <env name="CACHE_STORE"   value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="JWT_SECRET"    value="test-only-secret-long-enough-for-hs256"/>
    <env name="FILESYSTEM_DISK" value="local"/>
</php>
```

Rules:
- Tests always run against SQLite `:memory:` — never against the real database.
- `BCRYPT_ROUNDS=4` is mandatory; without it, password hashing in factories is ~10× slower.
- `JWT_SECRET` must be set; without it, every JWT operation throws.
- Any new env variable required by application code must be added here with a safe test value.

---

### Running tests

```bash
# Full suite
php artisan test

# Feature tests only
php artisan test --testsuite=Feature

# Single file
php artisan test tests/Feature/Api/PostTest.php

# Stop on first failure
php artisan test --stop-on-failure

# With coverage (requires Xdebug or PCOV)
php artisan test --coverage
```

---

## API Response Format

All responses use the `ApiResponse` trait and follow this shape:

**Success:**
```json
{
  "success": true,
  "message": "Products retrieved",
  "data": [ ... ]
}
```

**Paginated:**
```json
{
  "success": true,
  "message": "Products retrieved",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 48,
    "from": 1,
    "to": 10
  }
}
```

**Error:**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": { "name": ["The name field is required."] }
}
```

---

## Authentication Flow (API)

```
POST /api/v1/auth/register   → { access_token, refresh_token, expires_in, user }
POST /api/v1/auth/login      → { access_token, refresh_token, expires_in, user }

# access_token expires (JWT TTL configured in config/jwt.php)
POST /api/v1/auth/refresh    → { access_token, refresh_token, ... }  (rotates refresh token)

POST /api/v1/auth/logout     → {}   (invalidates refresh token + JWT)
GET  /api/v1/auth/me         → { user }   (requires valid access_token)
```

- The `refresh_token` is a 64-character random string stored hashed in the `refresh_tokens` table (30-day TTL).
- The `access_token` is a short-lived JWT. Pass it as `Authorization: Bearer <token>`.
- Refresh does **not** require a valid access_token — it only needs the refresh token in the request body.

---

## Shared Traits Reference

| Trait | What it provides |
|---|---|
| `HasUuidWithlog` | UUID primary key (`HasUuids`) + Spatie activity logging (`LogsActivity`) |
| `HasActiveColumn` | `active()`, `disabled()`, `whereActiveIs(bool)` query scopes |
| `ApiResponse` | `success()`, `paginate()`, `error()` JSON response methods with logging |

---

## Environment Variables Checklist

When deploying a new project from this template, configure these in `.env`:

```dotenv
APP_NAME=
APP_URL=
APP_KEY=                        # php artisan key:generate

DB_CONNECTION=pgsql
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

JWT_SECRET=                     # php artisan jwt:secret

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_ENDPOINT=                   # MinIO endpoint for local/self-hosted

REDIS_HOST=
REDIS_PASSWORD=

MAIL_MAILER=resend
RESEND_API_KEY=

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
```

---

## Commands

```bash
# Generate JWT secret (once per project)
php artisan jwt:secret

# Publish JWT config
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"

# Generate Filament Shield policies
php artisan shield:generate --all --panel=dashboard --option=policies_and_permissions

# Run migrations + seed
php artisan migrate --seed

# Re-generate autoload (needed after adding files to composer.json autoload)
composer dump-autoload

# Generate API documentation (Scribe)
php artisan scribe:generate

# Run all tests
php artisan test

# Run only feature tests
php artisan test --testsuite=Feature
```

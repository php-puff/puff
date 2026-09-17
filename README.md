# Puff

> PHP Unison Fiber Framework — a modular, Fiber-native framework for PHP 8.2+.

Puff is a collection of focused Composer packages assembled by `puff/puff`, the application skeleton and composition root. It provides an event-loop runtime, Fiber-aware dependency injection, PSR HTTP messages, compiled routing, HTTP and WebSocket servers, authentication, database integrations, and application services without Swoole, Amp, Workerman, or Symfony HttpFoundation.

## Highlights

- Native PHP Fibers, futures, timers, channels, deferred callbacks, and an event loop.
- Independent process groups for HTTP and WebSocket applications.
- Puff-owned PSR-7 and PSR-17 HTTP messages, streams, uploads, and URI objects.
- Compiled static and dynamic routing with request-local parameters.
- PSR-11 DI container with Fiber-scoped request services and automatic cleanup.
- Composer discovery for providers, configuration, applications, and CLI commands.
- Optional JWT/PASETO authentication, events, sessions, cookies, Redis, views, databases, migrations, jobs, and seeds.

## Requirements

- PHP 8.2 or newer.
- Composer 2.
- `pcntl` and `posix` for multi-process workers on Unix-like systems.
- `fileinfo`, `mbstring`, and `simplexml` for the default HTTP application.

The process supervisor depends on `pcntl_fork()` and POSIX signals, so it is not natively supported on Windows.

## Install and Run

```bash
composer install
cp .env.example .env
./puff run
```

The workspace uses Composer path repositories; installed packages are symlinked under `vendor/puff`, so local component changes are immediately visible.

Set a secure JWT signing key before using the token endpoint:

```env
JWT__SECRET=replace-with-a-random-secret-of-at-least-32-bytes
```

For automatic development restart:

```bash
./puff run --watch
composer start -- --watch
```

### Docker

The Docker image installs local Puff components through Composer path repositories.
Build it from the workspace root, not from the `puff/` application directory:

```bash
cd /Users/DC/Workshop/puff
docker build -f puff/Dockerfile -t puff .
docker run --rm -p 8620:8620 puff
```

## Architecture

```text
Composer metadata
      │
      ▼
Discovery ──► providers ──► Fiber-aware DI container
      │                             │
      └────► application modules ◄──┘
                                      │
                                      ▼
                           process supervisor
                              │           │
                              ▼           ▼
                       HTTP workers   WebSocket workers
                              │           │
                              └──► event loop ◄──┘
                                       │
                                    Fibers
```

Each request has its own Fiber scope. The HTTP dispatcher clears that scope after producing a response, preventing request-scoped services from leaking into another request.

Fiber concurrency is cooperative: only event-loop-aware work yields without blocking a worker. Use Puff asynchronous clients for concurrent network I/O; ordinary blocking I/O still blocks its worker.

## Packages

### Foundation

| Package | Purpose |
|---|---|
| `puff/application` | Application lifecycle, discovery, and process supervision |
| `puff/async` | Fibers, event loop, futures, timers, channels, wait groups, and Fiber-local context |
| `puff/di` | PSR-11 DI container with singleton and Fiber-scoped bindings |
| `puff/config` | PHP configuration, root `.env` overlays, dot notation, and config publishing |
| `puff/console` | CLI registry, parsing, discovery, and source generation |
| `puff/pipeline` | Container-aware pipelines |
| `puff/support` | Dependency-free array, string, number, and validation helpers |
| `puff/event` | Synchronous, type-safe in-process domain events |

### Networking and HTTP

| Package | Purpose |
|---|---|
| `puff/server` | Fiber TCP and UDP server primitives |
| `puff/client` | Fiber-aware TCP/TLS client transport |
| `puff/http` | PSR-7 messages, PSR-17 factories, streams, uploads, URI, and trusted-proxy parsing |
| `puff/routing` | Compiled routes, groups, typed parameters, route pipelines, 404 and 405 handling |
| `puff/http-server` | HTTP/1.1 server application, keep-alive, dispatch, and static resources |
| `puff/websocket-server` | WebSocket application, frames, lifecycle, and event routes |
| `puff/http-client` | Fiber-aware PSR-18 HTTP client |

### Application services

| Package | Purpose |
|---|---|
| `puff/cache` | Memory, file, APCu, and null cache stores |
| `puff/cookie` | Fiber-scoped cookie jar and response middleware |
| `puff/session` | Fiber-safe session codecs and memory/file/Redis stores |
| `puff/redis` | Non-blocking Redis client |
| `puff/database` | Fiber-scoped PDO/query core with Eloquent, Cycle, and Think adapters |
| `puff/migration` | ORM-independent schema migrations and DDL compilers |
| `puff/seed` | Explicit database seed runner |
| `puff/file` | Native filesystem helpers |
| `puff/view` | Renderer abstraction and optional view engines |
| `puff/logger` | Lightweight PSR-3 logger |
| `puff/i18n` | Translation loading, locale detection, and cache commands |
| `puff/crypt` | Authenticated encryption utilities |
| `puff/jwt` | JWT issuance, verification, and Bearer authentication pipeline |
| `puff/paseto` | PASETO v3/v4 issuance, verification, and authentication pipeline |
| `puff/job` | Fiber-based cron scheduler |

Packages are optional unless included by the skeleton's `composer.json`. A package may publish configuration and register providers or commands through Composer metadata.

## Configuration

Shared runtime settings are in `config/config.php`:

```php
return [
    'timezone' => 'Asia/Shanghai',
    'charset' => 'utf-8',
    'runtime' => dirname(__DIR__) . '/runtime',
    'workers' => 1,
];
```

`config/server.php` is a flat list of server instances. Each entry has a `type`, `addr`, route files, and type-specific options:

```php
return [
    [
        'type' => 'http',
        'addr' => '0.0.0.0:8620',
        'routes' => [dirname(__DIR__) . '/app/routes.php'],
        'resource' => dirname(__DIR__) . '/www',
        'pipeline' => [Pipeline\Api::class, Pipeline\Page::class],
        'trusted_proxies' => [],
    ],
];
```

The root `.env` file overlays existing configuration keys. Double underscores represent nested keys, for example `JWT__SECRET`, `LOGGER__LEVEL`, or `DATABASE__DEFAULT`.

## HTTP, Routing, and Authentication

Routes use a compact DSL. Dynamic route parameters use `(name:type)` syntax:

```php
Router::group([
    'id' => 'public',
    'prefix' => '/',
    'namespace' => 'App\\Controller',
], static function (): void {
    Router::get('/ping', 'Puff@ping')->id('ping');
    Router::get('/hello/(name:str)', 'Puff@hello')->id('hello');
    Router::post('/articles', 'Article@create')->id('articles.create');
});
```

Built-in types include `str`, `int`, `num`, `uuid`, `hex`, `hash`, `any`, and `*`. Static routes use indexed lookup; dynamic patterns are compiled during boot and matches are request-local.

Controllers may return `Puff\Http\Response`, strings, arrays, or objects. Arrays and objects are serialized as JSON by the dispatcher:

```php
public function show(string $id): array
{
    return ['id' => $id, 'status' => 'ok'];
}
```

Puff HTTP messages are immutable: `withHeader()` and all `with*()` methods return a new instance.

### Skeleton endpoints

| Method | Path | Notes |
|---|---|---|
| `GET` | `/` | Welcome page |
| `GET` | `/ping` | Health response |
| `GET` | `/event?user=user-1` | Domain event dispatch example |
| `GET` | `/token?subject=user-1` | JWT issuance example |
| `GET` | `/api/*` | JWT Bearer-protected request and async examples |
| `GET` | `/data/page?page=1&size=20` | Query pagination example |

Get a token and call a protected endpoint:

```bash
TOKEN=$(curl -s 'http://127.0.0.1:8620/token?subject=user-1' | \
  php -r 'echo json_decode(stream_get_contents(STDIN), true)["access_token"];')

curl http://127.0.0.1:8620/api/status \
  -H "Authorization: Bearer ${TOKEN}"
```

`Puff\Jwt\Pipeline` verifies the token and stores claims in the `jwt` request attribute. `Pipeline\Auth` can require claims that were produced by JWT or PASETO middleware.

## Pipelines, Pagination, and Async

Global pipelines are configured per HTTP server. Route groups can add their own stages:

```php
Router::group([
    'prefix' => 'api',
    'pipeline' => [Puff\Jwt\Pipeline::class],
], static function (): void {
    // Protected routes.
});
```

`Pipeline\Api` normalizes output and adds CORS headers. `Pipeline\Page` paginates an unexecuted Puff query or Cycle-style select, using `page` and `size`; it returns `data` and `meta.page`, `meta.size`, `meta.total`, and `meta.last`.

```php
$first = puff_async(static function (): string {
    puff_delay(0.050);
    return 'first';
});

$second = puff_async(static function (): string {
    puff_delay(0.030);
    return 'second';
});

$results = [puff_await($first), puff_await($second)];
```

Other helpers include `puff_defer()`, `puff_channel()`, `puff_wait_group()`, and `puff_id()`. Runnable examples are available under `/api/async/*`.

## Domain Events

`puff/event` is synchronous and in-process. Use it to decouple one completed business action from independent side effects; it is not a queue, broker, retry mechanism, or cross-process transport.

The skeleton dispatches `App\Event\TokenIssued` after a JWT is signed. Its listener logs only the token subject, never the access token.

```php
final readonly class TokenIssued
{
    public function __construct(public string $subject)
    {
    }
}

$events->dispatch(new TokenIssued($subject));
$events->listen(TokenIssued::class, WriteTokenIssuedLog::class);
```

Listeners are resolved through DI in registration order. Exceptions stop dispatch and propagate to the caller. Use `puff/job` from a listener when a side effect needs asynchronous or scheduled execution.

Generate a generic event class with a readonly `data` payload:

```bash
./puff event UserRegistered
# event/UserRegistered.php, namespace Event
```

## Database, Migrations, and WebSocket

`puff/database` supplies Fiber-scoped PDO connections and a parameterized query API. Optional ORM adapters register when their packages are installed:

```bash
composer require illuminate/database:^12.0
composer require cycle/annotated:^4.0
composer require topthink/think-orm:^4.0
```

Puff does not force a shared model base class. The skeleton maps `Database\Model` and `Database\Entity` directly to `database/Model` and `database/Entity`.

Migrations are independent of the selected ORM:

```bash
./puff migration create create_users_table
./puff migration create add_email_to_users --table=users
./puff migration run
./puff migration status
./puff migration rollback --step=1
```

WebSocket servers use the same `config/server.php` list:

```php
[
    'type' => 'websocket',
    'addr' => '0.0.0.0:8791',
    'routes' => [dirname(__DIR__) . '/app/websocket.php'],
    'allowed_origins' => [],
    'protocols' => [],
],
```

The bundled WebSocket routes demonstrate event-based messages such as `message.echo`, as well as path and lifecycle handling.

## CLI

Run `./puff` to list commands installed by the current Composer dependency set. Commands are contributed by their owning components, so a component that is not installed does not expose a command.

| Command | Owner | Purpose |
|---|---|---|
| `call` | application skeleton | Call a class method or function |
| `controller` | application skeleton | Generate an `App\Controller` class |
| `service` | application skeleton | Generate a `Service` class |
| `event` | `puff/event` | Generate an `Event` class |
| `entity` | `puff/database` | Generate a Cycle entity from database DDL |
| `i18n` | `puff/i18n` | Flush or reload translation cache |
| `migration` | `puff/migration` | Create, run, inspect, or roll back migrations |
| `pipeline` | `puff/pipeline` | Generate a pipeline class |
| `seed` | `puff/seed` | Create or execute seed files |

Quality and benchmark commands:

```bash
composer test
composer lint
composer analyse
composer benchmark
```

## Project Structure

```text
puff/
├── app/
│   ├── Controller/      HTTP and WebSocket controllers
│   ├── Event/           Application domain events
│   ├── Listener/        Domain-event listeners
│   ├── Provider/        Application service providers
│   ├── routes.php       HTTP routes
│   └── websocket.php    WebSocket routes
├── config/
│   ├── config.php       Shared runtime settings
│   └── server.php       HTTP and WebSocket instances
├── database/
│   ├── Entity/          Cycle entities
│   ├── Model/           Eloquent or Think models
│   ├── migrations/      Schema migrations
│   └── seed/            Seed files
├── event/               Generated Event namespace
├── pipeline/            Application pipeline classes
├── runtime/             Logs, caches, and runtime data
├── tests/               Application tests and benchmark
├── www/                 Front controller and static resources
├── puff                 CLI and server launcher
└── composer.json
```

## Security Notes

- Configure a unique `JWT__SECRET`; never commit production secrets.
- Trusted proxy handling is disabled unless CIDRs are explicitly configured.
- HTTP messages validate headers, Host values, forwarded chains, uploads, and methods.
- Request-scoped services are cleared after dispatch.
- Do not log JWTs, PASETOs, passwords, or other credentials.
- Use a trusted TLS reverse proxy until native TLS is configured for the server layer.
- Avoid blocking I/O inside request Fibers.

## Stability and License

Components currently use `dev-main` constraints with `minimum-stability: dev` and `prefer-stable: true`; public APIs may change before stable releases.

Puff is released under the [MIT License](LICENSE).

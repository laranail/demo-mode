# Per-visitor sandbox

`sandbox.strategy` controls how isolated each visitor's experience is.

| Strategy | Isolation | Extra deps |
|---|---|---|
| `shared` *(default)* | one dataset for everyone; periodic global reset | — |
| `transactional` | per-request; writes roll back (see `demo.ephemeral`) | — |
| `scoped` | row-level per session via a `sandbox_id` | — |
| `tenant` | delegate to an existing `stancl/tenancy` install | `stancl/tenancy` |

## Scoped (row-level)

Add a nullable `demo_sandbox_id` column to the tables you want isolated and use the
trait; apply the `demo.sandbox` middleware to the routes:

```php
use Simtabi\Laranail\Demo\Mode\Concerns\BelongsToDemoSandbox;

class Note extends Model
{
    use BelongsToDemoSandbox;
}
```

Each visitor gets a `sandbox_id` (session + cookie, TTL `sandbox.ttl`). Rows created
in a demo session are stamped with it, and queries are automatically scoped to it — so
two visitors never see each other's data. `Demo::withoutGuards()` / bypassed actors see
all rows.

## Tenant

We do **not** auto-provision a tenant per visitor (tenancy identifies tenants by
domain/path, not session). Instead, point demo-mode at your existing tenancy setup and
let it isolate data; this strategy is a documented integration hook bound only when
`stancl/tenancy` is present.

> Full disposable per-instance demos (à la InstaWP/TasteWP) that spin up a whole app per
> visitor are out of scope for a single package.

---

[← Docs index](../../README.md#documentation)

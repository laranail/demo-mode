# Security & threat model

Demo mode is a **guard layer, not a security boundary**. Treat a public demo as
hostile-input territory and harden the environment too.

## What the guards do

- Block writes (HTTP, Eloquent, and — opt-in — every write statement).
- Prevent privilege escalation via `protected_attributes` (e.g. lock the demo user's
  email/password, block role changes).
- Route side effects to safe sinks (mail → log) so the demo can't send real email.
- Reset data on a schedule / on demand so one visitor can't spoil it for the next.

## What they do not do

- They do not sandbox the OS, filesystem, or network.
- `ephemeral` only rolls back **database** writes — not files, mail, queue, or non-DB
  sessions.
- Bypass by IP/role is only as strong as your auth and proxy configuration (set
  `TrustProxies` correctly so `request()->ip()` is trustworthy).

## Hardening checklist

- Use a **least-privilege** (ideally read-only) database user for the demo, and consider
  `write.strict_connection` / `write.readonly-connection`.
- Run the demo in an **isolated environment** with throwaway data — never production data.
- Gate the on-demand reset route (`reset.on_demand.gate`) or keep it rate-limited.
- Keep `reset.allow_production` **off**.
- Scope file uploads to a disk that the file reset purges.

## Reporting

See [SECURITY.md](../SECURITY.md) — disclosures go to `opensource@simtabi.com`.

[← Docs index](../README.md#documentation)

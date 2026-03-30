# Admin Scaffold – Summary of Changes
Summary of the refactor from a domain-specific PAPeR/GRM app into a reusable admin scaffold.

---
## Scaffold conversion
- Removed domain modules (profiles/structures/grievances/library and their API/controllers/views/models).
- Collapsed the database migration history into a minimal scaffold schema:
  - `database/migration_000_initial.php`
  - `database/migration_001_platform.php`
- Removed seed scripts and domain CLI helpers (left only `cli/migrate.php` and `cli/send_queued_emails.php`).

---
## Admin UI and navigation
- Branding defaults now use **Admin App** (instead of PAPeR-specific wording).
- User dropdown label changed to **My account**.
- In-app help + admin guide views were updated to describe the generic scaffold (extension points, conventions, where to add modules).

---
## Notifications
- Kept the notification UI and persistence:
  - Page: `/notifications`
  - Bell dropdown: `GET /api/notifications`
- `App\UserNotificationSettings` is scaffold-only by default; extend it when you add module-specific events.
- Email delivery still uses SMTP settings and queues messages into `email_queue`, delivered by `php cli/send_queued_emails.php` via cron.

---
## API skeleton kept for extensions
- Authentication:
  - `POST /api/auth/login`, `GET /api/auth/me`, `POST /api/auth/logout`
- Scaffold endpoints:
  - `GET /api/dashboard`
  - `GET /api/notifications`
  - `GET /api/history` (currently focused on `entity_type=user`)
- Extend `App\Controllers\Api\*` + register routes in `public/index.php`.

---
## Documentation
- Updated `DEVELOPMENTGUIDE.md` and scaffold-facing help pages to match the current minimal DB schema and modules.

---
*For the latest structure and conventions, see `DEVELOPMENTGUIDE.md`.*

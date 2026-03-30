# API Contract (Third-Party Frontend)

This document defines the response envelope and practical integration rules for `/api/*` endpoints.

## Response Envelope

All API endpoints return JSON in this shape:

```json
{
  "success": true,
  "data": {},
  "error": null
}
```

Error responses:

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "API_ERROR",
    "message": "Human-readable message",
    "details": {}
  }
}
```

Notes:
- `details` is optional.
- `data` may be object, array, scalar, or null.

## Authentication

- Session auth is supported (browser-based).
- Bearer auth is supported for API:
  - `Authorization: Bearer <token>`
  - Token is obtained from `POST /api/auth/login`
- Unauthenticated API responses return `401`.
- Forbidden/capability failures return `403`.

## Status Codes (common)

- `200` success
- `201` created
- `400` bad request / validation
- `401` unauthenticated
- `403` forbidden
- `404` not found
- `429` login throttling
- `500` internal error

## Domain Rules Relevant to Frontend

### Grievance escalation

- Escalation timer does **not** reset on note-only updates if status/level did not change.
- Timer resets only on real status/level transitions.
- For project-specific progress levels:
  - If project has custom levels, those are used.
  - If not, defaults (`Level 1/2/3`) are used.

### Progress-level labels

- API is project-aware for progress-level names.
- Mixed-project dashboard responses may include display labels prefixed by project name for clarity.

## Operational Helpers

- Re-map existing records to project-specific level IDs:
  - `php cli/remap_progress_levels.php`
  - `php cli/remap_progress_levels.php --project=<id>`

## Seeder Paths

- Project-specific levels scenario:
  - `php database/seed_profiles_structures_with_project_levels.php`
- Default-only levels scenario:
  - `php database/seed_profiles_structures_with_default_levels.php`


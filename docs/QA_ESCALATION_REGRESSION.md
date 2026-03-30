# QA Regression Checklist: Grievance Escalation

Goal: verify escalation timing does not reset on note-only updates, and only resets on real status/level transitions.

Scope: `grievance/list` escalation badge + `needs_escalation=1` filter.

## Quick Setup (one-time per run)

1. Ensure at least one `grievance_progress_levels` row has `days_to_address > 0`.
2. Pick a test grievance in `in_progress` with a valid `progress_level`.
3. Optional but recommended: set a simulated date in Development so QA is deterministic (`App\DevClock` behavior).

## Test Case 1: Note-only update should NOT clear escalation

Expected: if grievance is already overdue, escalation badge remains visible after posting a note with same status/level.

Steps:
1. Open a grievance currently `in_progress` and overdue for its current level.
2. Confirm escalation badge is visible on `/grievance/list`.
3. Submit status update form with:
   - same `status = in_progress`
   - same `progress_level`
   - non-empty note
4. Refresh `/grievance/list`.

Pass criteria:
- Escalation badge still shows (`Should be escalated to ...` or `Should be closed`).
- Grievance still appears when `Needs escalation / close` filter is set to `Yes`.

## Test Case 2: Real level change should reset escalation timer

Expected: when `progress_level` changes (still `in_progress`), escalation clears until threshold is exceeded again.

Steps:
1. Take an overdue `in_progress` grievance.
2. Change to another valid `progress_level`.
3. Refresh `/grievance/list`.

Pass criteria:
- Escalation badge is not shown immediately after transition.
- `Needs escalation / close` filter no longer includes this grievance right after transition.

## Test Case 3: Status change away from in_progress should clear escalation

Expected: escalation is only evaluated for `in_progress`.

Steps:
1. Take an overdue `in_progress` grievance.
2. Change status to `closed` (or `open`).
3. Refresh `/grievance/list`.

Pass criteria:
- Escalation badge is absent.
- Grievance is excluded from `Needs escalation / close`.

---

## SQL Spot Checks (copy/paste)

Replace `:gid` with grievance id.

### A) Timeline with transition markers

```sql
SELECT
  id,
  grievance_id,
  status,
  progress_level,
  created_at,
  CASE
    WHEN LAG(status) OVER w IS NULL THEN 1
    WHEN LAG(status) OVER w <> status THEN 1
    WHEN COALESCE(LAG(progress_level) OVER w, 0) <> COALESCE(progress_level, 0) THEN 1
    ELSE 0
  END AS is_transition
FROM grievance_status_log
WHERE grievance_id = :gid
WINDOW w AS (PARTITION BY grievance_id ORDER BY created_at, id)
ORDER BY created_at, id;
```

Use this to confirm note-only entries are not transitions (`is_transition = 0`).

### B) Start of current in-progress segment (reference timestamp)

```sql
SELECT
  g.id AS grievance_id,
  g.status,
  g.progress_level,
  (
    SELECT MIN(s1.created_at)
    FROM grievance_status_log s1
    WHERE s1.grievance_id = g.id
      AND s1.status = 'in_progress'
      AND s1.progress_level = g.progress_level
      AND NOT EXISTS (
        SELECT 1
        FROM grievance_status_log s2
        WHERE s2.grievance_id = s1.grievance_id
          AND (
            s2.created_at > s1.created_at
            OR (s2.created_at = s1.created_at AND s2.id > s1.id)
          )
          AND (
            s2.status <> 'in_progress'
            OR COALESCE(s2.progress_level, 0) <> COALESCE(s1.progress_level, 0)
          )
      )
  ) AS current_segment_started_at
FROM grievances g
WHERE g.id = :gid;
```

Use this to verify the segment start does not move forward after note-only updates.

### C) Overdue check for current level

```sql
SELECT
  g.id AS grievance_id,
  pl.name AS level_name,
  pl.days_to_address,
  DATEDIFF(CURDATE(), DATE((
    SELECT MIN(s1.created_at)
    FROM grievance_status_log s1
    WHERE s1.grievance_id = g.id
      AND s1.status = 'in_progress'
      AND s1.progress_level = g.progress_level
      AND NOT EXISTS (
        SELECT 1
        FROM grievance_status_log s2
        WHERE s2.grievance_id = s1.grievance_id
          AND (
            s2.created_at > s1.created_at
            OR (s2.created_at = s1.created_at AND s2.id > s1.id)
          )
          AND (
            s2.status <> 'in_progress'
            OR COALESCE(s2.progress_level, 0) <> COALESCE(s1.progress_level, 0)
          )
      )
  ))) AS days_in_current_level
FROM grievances g
LEFT JOIN grievance_progress_levels pl ON pl.id = g.progress_level
WHERE g.id = :gid
  AND g.status = 'in_progress';
```

Interpretation:
- overdue if `days_in_current_level > days_to_address`


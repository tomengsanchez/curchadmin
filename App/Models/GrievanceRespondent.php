<?php
namespace App\Models;

use Core\Database;

class GrievanceRespondent
{
    protected static function db(): \PDO
    {
        return Database::getInstance();
    }

    public static function find(int $id): ?object
    {
        $stmt = self::db()->prepare('SELECT * FROM grievance_respondents WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_OBJ) ?: null;
    }

    /** Paginated list of grievance respondents with latest grievance date and count. */
    public static function listPaginated(string $search, int $page, int $perPage): array
    {
        $db = self::db();
        $limit = max(1, min(100, $perPage));
        $offset = max(0, ($page - 1) * $limit);

        $where = '';
        $params = [];
        if ($search !== '') {
            $where = "WHERE (
                TRIM(COALESCE(r.full_name, '')) LIKE ?
                OR TRIM(COALESCE(r.first_name, '')) LIKE ?
                OR TRIM(COALESCE(r.middle_name, '')) LIKE ?
                OR TRIM(COALESCE(r.last_name, '')) LIKE ?
                OR TRIM(COALESCE(p.full_name, '')) LIKE ?
                OR TRIM(COALESCE(p.first_name, '')) LIKE ?
                OR TRIM(COALESCE(p.middle_name, '')) LIKE ?
                OR TRIM(COALESCE(p.last_name, '')) LIKE ?
                OR TRIM(COALESCE(r.mobile_number, '')) LIKE ?
                OR TRIM(COALESCE(r.email, '')) LIKE ?
            )";
            $term = '%' . $search . '%';
            $params = [$term, $term, $term, $term, $term, $term, $term, $term, $term, $term];
        }

        $sql = "SELECT
                    r.id,
                    r.is_paps,
                    r.profile_id,
                    r.first_name,
                    r.middle_name,
                    r.last_name,
                    r.full_name,
                    p.full_name AS paps_full_name,
                    p.first_name AS paps_first_name,
                    p.middle_name AS paps_middle_name,
                    p.last_name AS paps_last_name,
                    r.gender,
                    r.mobile_number,
                    r.email,
                    COUNT(g.id) AS grievance_count,
                    MAX(g.date_recorded) AS latest_grievance_date
                FROM grievance_respondents r
                LEFT JOIN profiles p ON p.id = r.profile_id
                LEFT JOIN grievances g ON g.respondent_id = r.id
                $where
                GROUP BY r.id
                ORDER BY latest_grievance_date DESC, r.id DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $countSql = "SELECT COUNT(*) FROM grievance_respondents r $where";
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $limit,
            'total_pages' => (int) ceil($total / $limit),
        ];
    }

    /**
     * Create or update respondent row from grievance form payload.
     * For PAPS-linked grievances, fill name fields from profile when empty.
     */
    public static function upsertFromGrievanceData(array $data, ?int $existingId = null): int
    {
        $isPaps = !empty($data['is_paps']) ? 1 : 0;
        $profileId = (int) ($data['profile_id'] ?? 0) ?: null;

        $firstName = trim((string) ($data['respondent_first_name'] ?? ''));
        $middleName = trim((string) ($data['respondent_middle_name'] ?? ''));
        $lastName = trim((string) ($data['respondent_last_name'] ?? ''));

        if ($isPaps && $profileId) {
            $profile = Profile::find($profileId);
            if ($profile) {
                if ($firstName === '') $firstName = trim((string) ($profile->first_name ?? ''));
                if ($middleName === '') $middleName = trim((string) ($profile->middle_name ?? ''));
                if ($lastName === '') $lastName = trim((string) ($profile->last_name ?? ''));
            }
        }

        $fullName = trim(implode(' ', array_values(array_filter([$firstName, $middleName, $lastName], fn($v) => $v !== ''))));
        if ($fullName === '') {
            $fullName = trim((string) ($data['respondent_full_name'] ?? ''));
        }

        $payload = [
            $isPaps,
            $profileId,
            $firstName,
            $middleName,
            $lastName,
            $fullName,
            trim((string) ($data['gender'] ?? '')),
            trim((string) ($data['gender_specify'] ?? '')),
            trim((string) ($data['valid_id_philippines'] ?? '')),
            trim((string) ($data['id_number'] ?? '')),
            json_encode(self::ensureArray($data['vulnerability_ids'] ?? [])),
            json_encode(self::ensureArray($data['respondent_type_ids'] ?? [])),
            trim((string) ($data['respondent_type_other_specify'] ?? '')),
            trim((string) ($data['home_business_address'] ?? '')),
            trim((string) ($data['mobile_number'] ?? '')),
            trim((string) ($data['email'] ?? '')),
            trim((string) ($data['contact_others_specify'] ?? '')),
        ];

        if ($existingId !== null && $existingId > 0 && self::find($existingId)) {
            $sql = 'UPDATE grievance_respondents SET
                is_paps = ?, profile_id = ?, first_name = ?, middle_name = ?, last_name = ?, full_name = ?,
                gender = ?, gender_specify = ?, valid_id_philippines = ?, id_number = ?,
                vulnerability_ids = ?, respondent_type_ids = ?, respondent_type_other_specify = ?,
                home_business_address = ?, mobile_number = ?, email = ?, contact_others_specify = ?
                WHERE id = ?';
            $stmt = self::db()->prepare($sql);
            $stmt->execute(array_merge($payload, [$existingId]));
            return $existingId;
        }

        $sql = 'INSERT INTO grievance_respondents (
                is_paps, profile_id, first_name, middle_name, last_name, full_name,
                gender, gender_specify, valid_id_philippines, id_number,
                vulnerability_ids, respondent_type_ids, respondent_type_other_specify,
                home_business_address, mobile_number, email, contact_others_specify
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($payload);
        return (int) self::db()->lastInsertId();
    }

    private static function ensureArray($v): array
    {
        if (is_array($v)) return array_map('intval', array_filter($v));
        if (is_string($v)) {
            $d = json_decode($v, true);
            return is_array($d) ? array_map('intval', array_filter($d)) : [];
        }
        return [];
    }
}

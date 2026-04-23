<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

class CustomReportQueryBuilder
{
    protected static array $sources = [

        'trainees' => [
            'label'   => 'Trainees',
            'table'   => 'users',
            'where'   => [['role', '=', 'trainee']],
            'joins'   => [],
            'columns' => [
                'id'         => ['ID',             'users.id'],
                'name'       => ['Full Name',       'users.name'],
                'email'      => ['Email',           'users.email'],
                'created_at' => ['Date Registered', "DATE_FORMAT(users.created_at,'%Y-%m-%d')"],
            ],
        ],

        'trainers' => [
            'label'   => 'Trainers',
            'table'   => 'users',
            'where'   => [['role', '=', 'trainer']],
            'joins'   => [],
            'columns' => [
                'id'         => ['ID',        'users.id'],
                'name'       => ['Full Name',  'users.name'],
                'email'      => ['Email',      'users.email'],
                'created_at' => ['Date Added', "DATE_FORMAT(users.created_at,'%Y-%m-%d')"],
            ],
        ],

        'enrollments' => [
            'label'   => 'Enrollments',
            'table'   => 'enrollments',
            'where'   => [],
            'joins'   => [
                "LEFT JOIN users   ON users.id   = enrollments.trainee_id AND users.role = 'trainee'",
                "LEFT JOIN courses ON courses.id = enrollments.course_id",
            ],
            'columns' => [
                'id'            => ['Enrollment ID',  'enrollments.id'],
                'trainee_name'  => ['Trainee',        'users.name'],
                'trainee_email' => ['Trainee Email',  'users.email'],
                'course_name'   => ['Course',         'courses.name'],
                'course_code'   => ['Course Code',    'courses.code'],
                'course_level'  => ['Level',          'courses.level'],
                'status'        => ['Status',         'enrollments.status'],
                'enrolled_at'   => ['Enrolled At',    "DATE_FORMAT(enrollments.enrolled_at,'%Y-%m-%d')"],
            ],
        ],

        'attendance' => [
            'label'   => 'Attendance',
            'table'   => 'attendances',
            'where'   => [],
            'joins'   => [
                "LEFT JOIN enrollments ON enrollments.id = attendances.enrollment_id",
                "LEFT JOIN users       ON users.id       = enrollments.trainee_id AND users.role = 'trainee'",
                "LEFT JOIN courses     ON courses.id     = enrollments.course_id",
            ],
            'columns' => [
                'id'           => ['Record ID', 'attendances.id'],
                'trainee_name' => ['Trainee',   'users.name'],
                'course_name'  => ['Course',    'courses.name'],
                'date'         => ['Date',      "DATE_FORMAT(attendances.date,'%Y-%m-%d')"],
                'status'       => ['Status',    'attendances.status'],
            ],
        ],

        'assessments' => [
            'label'   => 'Assessments',
            'table'   => 'assessments',
            'where'   => [],
            'joins'   => [
                "LEFT JOIN enrollments ON enrollments.id = assessments.enrollment_id",
                "LEFT JOIN users       ON users.id       = enrollments.trainee_id AND users.role = 'trainee'",
                "LEFT JOIN users AS tr ON tr.id          = assessments.trainer_id",
                "LEFT JOIN courses     ON courses.id     = enrollments.course_id",
            ],
            'columns' => [
                'id'           => ['Assessment ID', 'assessments.id'],
                'trainee_name' => ['Trainee',       'users.name'],
                'trainer_name' => ['Trainer',       'tr.name'],
                'course_name'  => ['Course',        'courses.name'],
                'score'        => ['Score',         'assessments.score'],
                'result'       => ['Result',        'assessments.result'],
                'remarks'      => ['Remarks',       'assessments.remarks'],
                'assessed_at'  => ['Assessed At',   "DATE_FORMAT(assessments.assessed_at,'%Y-%m-%d')"],
            ],
        ],

        'certificates' => [
            'label'   => 'Certificates',
            'table'   => 'certificates',
            'where'   => [],
            'joins'   => [
                "LEFT JOIN enrollments ON enrollments.id = certificates.enrollment_id",
                "LEFT JOIN users       ON users.id       = enrollments.trainee_id AND users.role = 'trainee'",
                "LEFT JOIN users AS tr ON tr.id          = certificates.trainer_id",
                "LEFT JOIN courses     ON courses.id     = enrollments.course_id",
            ],
            'columns' => [
                'id'                 => ['Certificate ID',  'certificates.id'],
                'certificate_number' => ['Certificate No.', 'certificates.certificate_number'],
                'trainee_name'       => ['Trainee',         'users.name'],
                'trainer_name'       => ['Trainer',         'tr.name'],
                'course_name'        => ['Course',          'courses.name'],
                'issued_at'          => ['Issued At',       "DATE_FORMAT(certificates.issued_at,'%Y-%m-%d')"],
                'expires_at'         => ['Expires At',      "DATE_FORMAT(certificates.expires_at,'%Y-%m-%d')"],
            ],
        ],
    ];

    protected static array $operators = [
        '='       => '=',
        '!='      => '!=',
        '>'       => '>',
        '<'       => '<',
        '>='      => '>=',
        '<='      => '<=',
        'like'    => 'LIKE',
        'between' => 'BETWEEN',
    ];

    public static function schema(): array
    {
        $out = [];
        foreach (self::$sources as $key => $def) {
            $cols = [];
            foreach ($def['columns'] as $colKey => [$label]) {
                $cols[] = ['key' => $colKey, 'label' => $label];
            }
            $out[] = ['key' => $key, 'label' => $def['label'], 'columns' => $cols];
        }
        return $out;
    }

    public function run(array $config, ?int $limit = null): array
    {
        $source  = $config['source']   ?? null;
        $columns = $config['columns']  ?? [];
        $filters = $config['filters']  ?? [];
        $sortBy  = $config['sort_by']  ?? null;
        $sortDir = strtolower($config['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if (! $source || ! isset(self::$sources[$source])) {
            throw new \InvalidArgumentException("Invalid data source: {$source}");
        }

        $def           = self::$sources[$source];
        $availableCols = $def['columns'];

        if (empty($columns)) {
            $columns = array_keys($availableCols);
        } else {
            $columns = array_filter($columns, fn($c) => isset($availableCols[$c]));
        }

        if (empty($columns)) {
            throw new \InvalidArgumentException('No valid columns selected.');
        }

        $selects = [];
        $labels  = [];
        foreach ($columns as $colKey) {
            [$label, $expr]  = $availableCols[$colKey];
            $selects[]       = DB::raw("{$expr} as `{$colKey}`");
            $labels[$colKey] = $label;
        }

        // Embed JOINs directly into the FROM clause — avoids joinRaw() which
        // only exists on the Eloquent builder, not the base query builder.
        $joinSql = ! empty($def['joins']) ? ' ' . implode(' ', $def['joins']) : '';
        $query   = DB::table(DB::raw("`{$def['table']}`{$joinSql}"))->select($selects);

        // Hard WHERE constraints (e.g. role = 'trainee')
        foreach ($def['where'] as [$col, $op, $val]) {
            $query->where($col, $op, $val);
        }

        // User-defined filters
        foreach ($filters as $filter) {
            $colKey   = $filter['column']   ?? null;
            $operator = $filter['operator'] ?? '=';
            $value    = $filter['value']    ?? '';
            $value2   = $filter['value2']   ?? '';

            if (! $colKey || ! isset($availableCols[$colKey])) continue;
            if (! isset(self::$operators[$operator]))           continue;

            [, $expr] = $availableCols[$colKey];
            $sqlOp    = self::$operators[$operator];

            if ($operator === 'like') {
                $query->whereRaw("{$expr} LIKE ?", ["%{$value}%"]);
            } elseif ($operator === 'between') {
                if ($value !== '' && $value2 !== '') {
                    $query->whereBetween(DB::raw($expr), [$value, $value2]);
                }
            } else {
                if ($value !== '') {
                    $query->whereRaw("{$expr} {$sqlOp} ?", [$value]);
                }
            }
        }

        // Sort
        if ($sortBy && isset($availableCols[$sortBy])) {
            [, $sortExpr] = $availableCols[$sortBy];
            $query->orderByRaw("{$sortExpr} {$sortDir}");
        }

        // Limit
        if ($limit !== null) {
            $query->limit($limit);
        }

        // Fetch and remap column keys to human-readable labels
        return $query->get()->map(function ($row) use ($labels) {
            $out = [];
            foreach ($labels as $key => $label) {
                $out[$label] = $row->$key ?? '';
            }
            return $out;
        })->toArray();
    }
}
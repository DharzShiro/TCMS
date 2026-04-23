<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reports\CustomReportQueryBuilder;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;

class CustomReportController extends Controller
{
    public function __construct(
        protected CustomReportQueryBuilder $builder,
        protected ReportExportService      $exporter,
    ) {}

    // ── Builder UI ────────────────────────────────────────────────────────────

    public function index()
    {
        $plan   = tenancy()->tenant->subscription ?? 'basic';
        $schema = CustomReportQueryBuilder::schema();

        return view('tenants.admin.reports.custom-builder', compact('plan', 'schema'));
    }

    // ── Live preview (AJAX / JSON) ────────────────────────────────────────────

    public function preview(Request $request)
    {
        $plan = tenancy()->tenant->subscription ?? 'basic';

        // Standard plan: cap preview at 100 rows; Premium: 500
        $previewLimit = $plan === 'premium' ? 500 : 100;

        try {
            $config = $this->parseConfig($request);
            $rows   = $this->builder->run($config, $previewLimit);

            return response()->json([
                'success' => true,
                'count'   => count($rows),
                'columns' => !empty($rows) ? array_keys($rows[0]) : [],
                'rows'    => array_slice($rows, 0, $previewLimit),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $plan   = tenancy()->tenant->subscription ?? 'basic';
        $format = $request->input('format', 'csv');

        try {
            $config = $this->parseConfig($request);

            // Standard plan: 3,000 record cap enforced inside ReportExportService
            $limit = $plan === 'standard' ? 3000 : null;
            $rows  = $this->builder->run($config, $limit);

            $source   = $config['source'] ?? 'report';
            $filename = 'custom_report_' . $source . '_' . now()->format('Ymd_His');
            $title    = 'Custom Report — ' . ucfirst($source);

            return $this->exporter->export($rows, $filename, $format, $title, $plan);

        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['export' => $e->getMessage()]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function parseConfig(Request $request): array
    {
        return [
            'source'   => $request->input('source'),
            'columns'  => $request->input('columns', []),
            'filters'  => $request->input('filters', []),
            'sort_by'  => $request->input('sort_by'),
            'sort_dir' => $request->input('sort_dir', 'asc'),
        ];
    }
}
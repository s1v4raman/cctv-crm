<?php

namespace App\Http\Controllers;

use App\Support\ListActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The "..." menu on every record list: Mass Delete, Mass Update, Mass Email and Export.
 * Each record goes through its own single-record action (see ListActions).
 */
class ListActionController extends Controller
{
    public function delete(Request $request, string $list): RedirectResponse
    {
        [$config, $records] = $this->picked($request, $list);
        abort_unless(isset($config['delete']) && ListActions::allowed($config['delete'], $request->user()), 403);

        $skipped = [];
        foreach ($records as $record) {
            // newQuery keeps the soft-delete scope, so a record moved to the bin counts as deleted
            $reason = ListActions::replay($config['delete'], $record, [], fn () => !$record->newQuery()->whereKey($record->getKey())->exists());
            if ($reason !== null) {
                $skipped[] = $reason;
            }
        }

        return back()->with('status', $this->summary($config['noun'], $records->count() - count($skipped), 'deleted', $skipped));
    }

    public function update(Request $request, string $list): RedirectResponse
    {
        $config = ListActions::get($list) ?? abort(404);
        $field = $config['update'][$request->input('field')] ?? abort(422, 'Choose a field to update.');
        abort_unless(ListActions::allowed($field['route'], $request->user()), 403);

        $request->validate(['value' => ['required', 'in:' . implode(',', array_map('strval', array_keys(($field['options'])())))]]);
        [, $records] = $this->picked($request, $list);

        $column = $request->input('field');
        $value = (string) $request->input('value');
        $skipped = [];
        foreach ($records as $record) {
            $reason = ListActions::replay($field['route'], $record, [$column => $value], fn () => (string) $record->fresh()?->{$column} === $value);
            if ($reason !== null) {
                $skipped[] = $reason;
            }
        }

        $label = $field['options']()[$value] ?? $value;
        return back()->with('status', $this->summary($config['noun'], $records->count() - count($skipped), 'updated (' . strtolower($field['label']) . ': ' . $label . ')', $skipped));
    }

    /** Email addresses of the picked records, for the Mass Email dialog (it opens the user's mail app). */
    public function emails(Request $request, string $list): JsonResponse
    {
        [$config, $records] = $this->picked($request, $list);
        abort_unless(isset($config['email']) && $request->user()->isOffice(), 403);

        $emails = $records->map(fn ($record) => trim((string) ($config['email'])($record)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

        return response()->json([
            'emails' => $emails->unique()->values(),
            'missing' => $records->count() - $emails->count(),
        ]);
    }

    /** The picked records, or the whole list when none are picked, as CSV. */
    public function export(Request $request, string $list): StreamedResponse
    {
        $config = ListActions::get($list) ?? abort(404);
        // Whole tables: technicians' lists only show their own work, so this is for the office
        abort_unless(ListActions::allowed($config['index'], $request->user()) && $request->user()->isOffice(), 403);

        $ids = collect((array) $request->input('ids', []))->filter(fn ($id) => ctype_digit((string) $id))->all();
        $query = $config['model']::query()->when($ids, fn ($q) => $q->whereIn('id', $ids))->orderBy('id');
        $name = str($config['noun'])->plural()->slug() . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads names in any language
            $headings = null;
            foreach ($query->lazy(500) as $record) {
                // attributesToArray leaves out hidden fields such as passwords and tokens
                $row = collect($record->attributesToArray())->map(fn ($v) => is_array($v) ? json_encode($v) : $v);
                if ($headings === null) {
                    $headings = $row->keys()->all();
                    fputcsv($out, array_map(fn ($h) => str($h)->headline(), $headings));
                }
                fputcsv($out, array_map(fn ($h) => $row[$h] ?? '', $headings));
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: array, 1: Collection} */
    private function picked(Request $request, string $list): array
    {
        $config = ListActions::get($list) ?? abort(404);
        abort_unless(ListActions::allowed($config['index'], $request->user()), 403);

        $data = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer',
        ]);

        return [$config, $config['model']::whereIn('id', $data['ids'])->get()];
    }

    private function summary(string $noun, int $done, string $verb, array $skipped): string
    {
        $text = $done . ' ' . str($noun)->plural($done) . ' ' . $verb . '.';
        if ($skipped) {
            $reasons = collect($skipped)->countBy()->map(fn ($n, $reason) => rtrim($reason, '.') . ($n > 1 ? " ({$n})" : ''))->implode('; ');
            $text .= ' ' . count($skipped) . ' skipped: ' . $reasons . '.';
        }

        return $text;
    }
}

<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Shared bits for record list pages: rows per page from the footer, and the Sort menu.
 * $sorts maps a sort key to [column, direction]; the first entry is the default.
 */
trait ListsRecords
{
    protected function listPerPage(Request $request, int $default = 20): int
    {
        $n = (int) $request->get('per_page');

        return in_array($n, [10, 20, 50, 100], true) ? $n : $default;
    }

    /** @param array<string, array{0: string, 1: string}> $sorts */
    protected function applyListSort(Builder $query, Request $request, array $sorts): string
    {
        $key = array_key_exists($request->get('sort'), $sorts) ? $request->get('sort') : array_key_first($sorts);
        [$column, $direction] = $sorts[$key];
        $query->orderBy($column, $direction)->orderBy($query->getModel()->getQualifiedKeyName(), 'desc');

        return $key;
    }
}

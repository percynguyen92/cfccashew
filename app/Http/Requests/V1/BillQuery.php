<?php

declare(strict_types=1);

namespace App\Http\Requests\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Models\Bill;

class BillQuery extends FormRequest
{
    /**
     * A map of allowed filter operators to their Eloquent query builder methods.
     *
     * @var array<string, string>
     */
    protected array $operatorMap = [
        'eq' => '=',
        'ne' => '!=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'like' => 'like',
    ];

    /**
     * Fields that can be searched using the global or scoped search.
     *
     * @var array<int, string>
     */
    protected array $searchable = ['billNumber', 'seller', 'buyer'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.*.eq' => ['sometimes', 'string'],
            'filter.*.ne' => ['sometimes', 'string'],
            'filter.*.lt' => ['sometimes', 'string'],
            'filter.*.lte' => ['sometimes', 'string'],
            'filter.*.gt' => ['sometimes', 'string'],
            'filter.*.gte' => ['sometimes', 'string'],
            'filter.*.like' => ['sometimes', 'string'],
            'search' => ['sometimes', 'string'],
            'scope' => ['sometimes', 'array'],
            'scope.*' => ['sometimes', 'string'],
            'fields' => ['sometimes', 'string'],
            'sort' => ['sometimes', 'string'],
            'include' => ['sometimes', 'string'],
        ];
    }

    /**
     * Apply the query parameters to the given Eloquent builder.
     *
     * @param  Builder<Bill>  $query
     * @return Builder<Bill>
     */
    public function apply(Builder $query): Builder
    {
        // Apply filters
        if ($this->has('filter')) {
            $this->applyFilters($query);
        }

        // Global search across common fields
        if ($this->filled('search')) {
            $this->applyGlobalSearch($query);
        }

        // Scoped search for specific fields
        if ($this->has('scope')) {
            $this->applyScopeSearch($query);
        }

        // Field selection
        if ($this->has('fields')) {
            $this->applyFields($query);
        }

        // Apply includes (eager loading)
        if ($this->has('include')) {
            $this->applyIncludes($query);
        }

        // Apply sorting
        if ($this->has('sort')) {
            $this->applySorting($query);
        }

        return $query;
    }

    private function applyFilters(Builder $query): void
    {
        $allowedFilters = ['billNumber', 'seller', 'buyer'];

        foreach ($this->input('filter') as $field => $operators) {
            if (! in_array($field, $allowedFilters) || ! is_array($operators)) {
                continue;
            }

            foreach ($operators as $operator => $value) {
                if (array_key_exists($operator, $this->operatorMap)) {
                    $query->where($field, $this->operatorMap[$operator], $value);
                }
            }
        }
    }

    private function applyIncludes(Builder $query): void
    {
        //$allowedIncludes = ['containers', 'cuttingTests'];
        $allowedIncludes = ['containers', 'cuttingTests', 'containers.cuttingTest'];
        $includes = array_intersect($allowedIncludes, explode(',', $this->input('include', '')));
        $query->with($includes);
    }

    private function applySorting(Builder $query): void
    {
        $allowedSorts = ['id', 'billNumber', 'seller', 'buyer', 'createdAt', 'updatedAt'];
        $sorts = explode(',', $this->input('sort', 'id'));

        foreach ($sorts as $sort) {
            $direction = Str::startsWith($sort, '-') ? 'desc' : 'asc';
            $field = ltrim($sort, '-');

            if (in_array($field, $allowedSorts)) {
                $column = $field === 'createdAt' ? 'created_at' : ($field === 'updatedAt' ? 'updated_at' : $field);
                $query->orderBy($column, $direction);
            }
        }
    }

    private function applyGlobalSearch(Builder $query): void
    {
        $term = $this->input('search');

        $query->where(function (Builder $query) use ($term) {
            foreach ($this->searchable as $index => $field) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $query->{$method}($field, 'like', '%' . $term . '%');
            }
        });
    }

    private function applyScopeSearch(Builder $query): void
    {
        foreach ($this->input('scope') as $field => $value) {
            if (in_array($field, $this->searchable)) {
                $query->where($field, 'like', '%' . $value . '%');
            }
        }
    }

    private function applyFields(Builder $query): void
    {
        $allowed = array_merge(['id'], $this->searchable, ['createdAt', 'updatedAt']);
        $columnMap = [
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $fields = array_intersect($allowed, explode(',', $this->input('fields', '')));
        $columns = [];

        foreach ($fields as $field) {
            $columns[] = $columnMap[$field] ?? $field;
        }

        if (! empty($columns)) {
            if (! in_array('id', $columns)) {
                $columns[] = 'id';
            }

            $query->select($columns);
        }
    }}
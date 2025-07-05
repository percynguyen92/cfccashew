<?php

declare(strict_types=1);

namespace App\Http\Requests\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BillQuery extends FormRequest
{
    /**
     * A map of allowed filter operators to their Eloquent query builder methods.
     *
     * @var array<string, string>
     */
    protected readonly array $operatorMap = [
        'eq' => '=',
        'ne' => '!=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'like' => 'like',
    ];

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
        $allowedIncludes = ['containers', 'cuttingTests'];
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
}
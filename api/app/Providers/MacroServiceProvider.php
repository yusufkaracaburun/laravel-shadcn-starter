<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\Month;
use Carbon\WeekDay;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

final class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerDbColumns();
        $this->registerCollectionMacros();
        $this->registerQueryMacros();
        $this->registerRequestMacros();
        //        $this->registerResponseMacros();
        $this->registerStringMacros();
    }

    private function registerDbColumns(): void
    {
        Blueprint::macro('money', fn ($column) => $this->decimal($column, 12, 5));
    }

    /**
     * $salesData = Order::whereMonth('created_at', now()->month)
     * ->pluck('total')
     * ->statistics();
     */
    private function registerCollectionMacros(): void
    {
        Collection::macro('statistics', function (): ?array {
            $values = $this->values()->sort();
            $count = $values->count();

            if ($count === 0) {
                return null;
            }

            $mean = $values->avg();
            $median = $count % 2 === 0
                ? ($values[$count / 2 - 1] + $values[$count / 2]) / 2
                : $values[floor($count / 2)];

            $variance = $values->map(fn ($x): float|int => ($x - $mean) ** 2)->avg();
            $stdDev = sqrt($variance);

            $q1Index = floor($count * 0.25);
            $q3Index = floor($count * 0.75);

            return [
                'count' => $count,
                'min' => $values->min(),
                'max' => $values->max(),
                'mean' => round($mean, 2),
                'median' => round($median, 2),
                'std_dev' => round($stdDev, 2),
                'variance' => round($variance, 2),
                'q1' => $values[$q1Index],
                'q3' => $values[$q3Index],
                'iqr' => $values[$q3Index] - $values[$q1Index],
            ];
        });
    }

    /**
     * $recentOrders = Order::whereRecent('created_at', 14)->get();
     * $businessHourTickets = Ticket::whereBusinessHours('created_at')
     * ->where('priority', 'high')
     * ->get();
     * $q4Sales = Sale::whereQuarter('sale_date', 4, 2024)->sum('amount');
     * $periodRevenue = Payment::whereDateBetween('paid_at', '2024-01-01', '2024-12-31')
     * ->sum('amount');
     */
    private function registerQueryMacros(): void
    {
        Builder::macro('filterDay', function ($column, $day, $month = null, $year = null) {
            $year ??= now()->year;
            $month ??= now()->month;

            return $this->whereYear($column, $year)
                ->whereMonth($column, $month)
                ->whereDay($column, $day);
        });
        Builder::macro('filterWeek', function ($column, $week, $year = null) {
            $year ??= now()->year;

            return $this->whereYear($column, $year)
                ->whereWeek($column, $week);
        });
        Builder::macro('filterDateBetween', function ($column, DateTimeInterface|WeekDay|Month|string|int|float|null $start, DateTimeInterface|WeekDay|Month|string|int|float|null $end = null) {
            $end ??= now();

            return $this->whereDate($column, '>=', Date::parse($start))
                ->whereDate($column, '<=', Date::parse($end));
        });
        Builder::macro('filterRecent', fn ($column, $days = 7) => $this->whereDate($column, '>=', now()->subDays($days)));
        Builder::macro('filterMonth', function ($column, $month = null) {
            $month ??= now()->month;

            return $this->whereMonth($column, $month);
        });
        Builder::macro('filterQuarter', function ($column, $quarter, $year = null) {
            $year ??= now()->year;
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;

            return $this->whereYear($column, $year)
                ->whereMonth($column, '>=', $startMonth)
                ->whereMonth($column, '<=', $endMonth);
        });
    }

    /**
     * public function store(Request $request)
     * {
     * $request->validatePhone();
     * $request->validateDateRange('start_date', 'end_date');
     * $request->validateCurrency('price', 0.01, 999999.99);
     *
     * Only get filled values, ignoring empty strings and nulls
     * $data = $request->onlyFilled(['name', 'description', 'notes']);
     *
     * Check if any optional field is present
     * if ($request->hasAny(['coupon_code', 'promo_code', 'discount_code'])) {
     * Apply discount logic
     * }
     * }
     */
    private function registerRequestMacros(): void
    {
        Request::macro('validatePhone', fn ($field = 'phone') => $this->validate([
            $field => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:20'],
        ]));
        Request::macro('validateDateRange', fn ($startField = 'start_date', $endField = 'end_date') => $this->validate([
            $startField => ['required', 'date'],
            $endField => ['required', 'date', 'after_or_equal:'.$startField],
        ]));
        Request::macro('validateCurrency', function ($field, $min = 0, $max = null) {
            $rules = [
                $field => [
                    'required',
                    'numeric',
                    'min:'.$min,
                    'regex:/^\d+(\.\d{1,2})?$/',
                ],
            ];

            if ($max !== null) {
                $rules[$field][] = 'max:'.$max;
            }

            return $this->validate($rules);
        });
        Request::macro('hasAny', fn (array $keys) => collect($keys)->contains(fn ($key) => $this->has($key)));
        Request::macro('onlyFilled', fn (array $keys) => collect($this->only($keys))
            ->filter(fn ($value): bool => filled($value))
            ->toArray());
    }

    /**
     * $users = User::searchIn(['name', 'email', 'phone'], 'john')->get();
     * $products = Product::whereLike('description', 'premium')
     * ->active()
     * ->get();
     * $posts = Post::searchIn(['title', 'content', 'tags'], $searchTerm)
     * ->whereRecent('published_at', 30)
     * ->paginate(20);
     */
    private function registerStringMacros(): void
    {
        EloquentBuilder::macro('whereLike', fn ($column, $value) => $this->where($column, 'LIKE', "%{$value}%"));
        EloquentBuilder::macro('orWhereLike', fn ($column, $value) => $this->orWhere($column, 'LIKE', "%{$value}%"));
        EloquentBuilder::macro('searchIn', fn (array $columns, $term) => $this->where(function ($query) use ($columns, $term): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $query->{$method}($column, 'LIKE', "%{$term}%");
            }
        }));
        EloquentBuilder::macro('withTrashed', fn () => $this->withTrashed());
        EloquentBuilder::macro('active', fn ($column = 'status', $value = 'active') => $this->where($column, $value));
        EloquentBuilder::macro('inactive', fn ($column = 'status', $value = 'inactive') => $this->where($column, $value));
    }
}

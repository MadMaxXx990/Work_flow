<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        /**
         * @active('path/pattern*')
         * Returns 'active' if the current URL matches the given pattern.
         * Used in the sidebar nav links.
         *
         * Example:  class="nav-link @active('admin/employees*')"
         */
        Blade::directive('active', function (string $expression): string {
            // Strip quotes from the expression
            $pattern = trim($expression, "'\"");
            return "<?php echo request()->is('{$pattern}') ? 'active' : ''; ?>";
        });
    }
}

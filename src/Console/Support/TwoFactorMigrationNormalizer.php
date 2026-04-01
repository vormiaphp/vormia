<?php

namespace Vormia\Console\Support;

use Illuminate\Support\Facades\File;

/**
 * Rewrites app migrations that add Fortify-style two-factor columns to `users` so each
 * column is added only when missing (Schema::hasColumn), avoiding duplicate column errors.
 */
final class TwoFactorMigrationNormalizer
{
    /**
     * @param  callable(string): void  $log  Optional line logger (e.g. $this->line from a command)
     * @return int Number of migration files patched
     */
    public function patchApplicationMigrations(?callable $log = null): int
    {
        $log ??= static function (string $msg): void {};

        $patched = 0;

        foreach ($this->candidateMigrationPaths() as $path) {
            $content = File::get($path);

            if (! $this->shouldPatch($content)) {
                continue;
            }

            File::put($path, $this->safeMigrationContents());
            $patched++;
            $log('   Patched: '.basename($path).' (columns are added only if missing)');
        }

        if ($patched === 0) {
            $log('   No unsafe two-factor user migrations found (or already idempotent).');
        }

        return $patched;
    }

    /**
     * @return list<string>
     */
    private function candidateMigrationPaths(): array
    {
        $dir = database_path('migrations');
        if (! is_dir($dir)) {
            return [];
        }

        $paths = [];
        foreach (glob($dir.'/*.php') ?: [] as $path) {
            $b = strtolower(basename($path));
            if (str_contains($b, 'two_factor') && str_contains($b, 'user')) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function shouldPatch(string $content): bool
    {
        if (
            str_contains($content, "hasColumn('users', 'two_factor_secret')")
            || str_contains($content, 'hasColumn("users", "two_factor_secret")')
        ) {
            return false;
        }

        $targetsUsersTable = str_contains($content, "Schema::table('users'")
            || str_contains($content, 'Schema::table("users"');

        if (! $targetsUsersTable || ! str_contains($content, 'two_factor_secret')) {
            return false;
        }

        return true;
    }

    private function safeMigrationContents(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable();
            }

            if (! Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable();
            }

            if (! Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('users', 'two_factor_secret') ? 'two_factor_secret' : null,
                Schema::hasColumn('users', 'two_factor_recovery_codes') ? 'two_factor_recovery_codes' : null,
                Schema::hasColumn('users', 'two_factor_confirmed_at') ? 'two_factor_confirmed_at' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
PHP;
    }
}

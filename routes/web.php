<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ============================================================
// DEPLOYMENT ROUTES — DELETE AFTER USE
// Secret key: shamsung_deploy_2026
// ============================================================

$deployKey = 'shamsung_deploy_2026';

Route::get('/deploy/migrate/{key}', function (string $key) use ($deployKey) {
    if ($key !== $deployKey) {
        abort(403, 'Forbidden');
    }

    $output = shell_exec('cd ' . base_path() . ' && php artisan migrate --force 2>&1');

    return response('<pre>' . htmlspecialchars($output) . '</pre>');
});

Route::get('/deploy/composer/{key}', function (string $key) use ($deployKey) {
    if ($key !== $deployKey) {
        abort(403, 'Forbidden');
    }

    $output = shell_exec('cd ' . base_path() . ' && composer install --no-dev --optimize-autoloader 2>&1');

    return response('<pre>' . htmlspecialchars($output) . '</pre>');
});

Route::get('/deploy/seed-technician/{key}', function (string $key) use ($deployKey) {
    if ($key !== $deployKey) {
        abort(403, 'Forbidden');
    }

    (new Database\Seeders\TechnicianSeeder())->run();

    return response('<pre>✅ TechnicianSeeder ran successfully.
Email:    tech@shamsung.com
Password: password123</pre>');
});

Route::get('/deploy/fix-technician-passwords/{key}', function (string $key) use ($deployKey) {
    if ($key !== $deployKey) {
        abort(403, 'Forbidden');
    }

    $results = [];
    foreach ([4, 5] as $id) {
        $tech = App\Models\Technician::find($id);
        if ($tech) {
            $tech->password = 'password123';
            $tech->save();
            $ok = Illuminate\Support\Facades\Hash::check('password123', $tech->fresh()->password);
            $results[] = "ID {$id} ({$tech->email}): " . ($ok ? '✅ fixed' : '❌ still broken');
        } else {
            $results[] = "ID {$id}: ⚠️ not found";
        }
    }

    return response('<pre>' . implode("\n", $results) . '</pre>');
});

// Reset any technician's password by email
// Usage: /deploy/reset-tech-password/{key}/{email}/{new_password}
Route::get('/deploy/reset-tech-password/{key}/{email}/{new_password}', function (string $key, string $email, string $new_password) use ($deployKey) {
    if ($key !== $deployKey) {
        abort(403, 'Forbidden');
    }

    $tech = App\Models\Technician::where('email', $email)->first();

    if (! $tech) {
        return response('<pre>❌ No technician found with email: ' . htmlspecialchars($email) . '</pre>');
    }

    $tech->password = $new_password; // hashed cast bcrypts automatically
    $tech->save();

    $ok = Illuminate\Support\Facades\Hash::check($new_password, $tech->fresh()->password);

    return response('<pre>' . ($ok ? '✅ Success' : '❌ Failed') . '
Email:    ' . $tech->email . '
Password: ' . htmlspecialchars($new_password) . '
ID:       ' . $tech->id . '</pre>');
});

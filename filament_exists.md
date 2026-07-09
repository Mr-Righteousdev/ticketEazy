# Adding Filament Panel Builder to an Existing Laravel + Livewire App

**Applies to:** Filament v5, Laravel 11/12, Livewire 4
**Context:** This app already had `filament/filament: ^5.0` installed as a Composer dependency, but the panel was never set up. These steps assume you already have Livewire, Tailwind, and auth (Fortify/Jetstream/Breeze etc.) running.

---

## 1. Install the Panel Builder

Filament v5 ships with `filament/filament"^5.0"`. If you haven't already, require it:

```bash
composer require filament/filament:"^5.0"
```

Then run the panel install command:

```bash
php artisan filament:install --panels
```

This does three things:
- Creates `app/Providers/Filament/AdminPanelProvider.php` — the panel configuration
- Registers it in `bootstrap/providers.php` (Laravel 11+) or `config/app.php` (Laravel 10)
- Publishes frontend assets to `public/`

**WARNING:** Do NOT run `php artisan filament:install --scaffold` — that overwrites your existing `app.css`, `vite.config.js`, and layout files. The `--panels` flag is safe for existing apps.

---

## 2. Verify Registration

Check `bootstrap/providers.php` contains:

```php
return [
    // ...
    App\Providers\Filament\AdminPanelProvider::class,
    // ...
];
```

If not, add it manually.

---

## 3. Implement FilamentUser on User Model

Filament requires your User model to implement `FilamentUser` with a `canAccessPanel()` method.

**`app/Models/User.php`:**

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    // ...

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }
}
```

This gates the panel — only users with the `admin` role (using Spatie Permission) can access `/admin`. Adjust the check to match your auth system.

---

## 4. Configure the Panel

Edit `app/Providers/Filament/AdminPanelProvider.php`:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()                          // optional — Filament's own login page
        ->authGuard('web')                 // use your app's auth guard
        ->colors(['primary' => Color::Amber])
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
        ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
        ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
        // ... middleware ...
        ->authMiddleware([Authenticate::class]);
}
```

Key points:
- `->authGuard('web')` — makes Filament use the same guard as your app (Fortify, etc.)
- `canAccessPanel()` on the User model handles role gating, not the middleware chain
- `->login()` is optional — you can use it as a fallback, or remove it and rely entirely on your app's login redirect

---

## 5. Remove Route Conflicts

If your `routes/web.php` had a route like:

```php
Route::view('/admin', 'dashboard')->name('admin.dashboard');
```

**Remove it.** Filament's panel registers its own routes at `/admin` via the service provider. A conflicting route will take precedence and break the panel.

---

## 6. Build Resources

Generate resources from your existing models:

```bash
php artisan make:filament-resource Event --generate
php artisan make:filament-resource TicketType --generate
php artisan make:filament-resource User --generate
```

The `--generate` flag reads the model's schema and auto-creates form fields, table columns, filters, and actions. You can then customize the generated files at `app/Filament/Resources/`.

---

## Key Takeaway

Filament's panel builder (`filament/filament`) coexists peacefully with other auth systems (Fortify, Jetstream, Breeze, etc.) and Livewire apps. The secret is:

1. Run `php artisan filament:install --panels` only (never `--scaffold` on an existing app)
2. Implement `FilamentUser` with `canAccessPanel()` for access control
3. Set `->authGuard('web')` to match your app's guard
4. Don't manually register `/admin` routes in `web.php`

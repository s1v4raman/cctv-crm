<?php

namespace App\Support;

use App\Models\AmcContract;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\RmaClaim;
use App\Models\ServiceTicket;
use App\Models\Site;
use App\Models\SiteSurvey;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Worker;
use App\Models\InstalledEquipment;
use App\Models\Invoice;
use App\Models\InstallationJob;
use App\Models\JobCompletionReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Mass actions for the record lists (the "..." menu: Mass Delete, Mass Update, Mass Email, Export).
 *
 * A mass change never writes records itself: every record goes through the same single-record
 * action as its own Delete or status button, so the role rules, checks ("only drafts can be
 * deleted"), audit logs and customer alerts stay exactly as they are. Lists are keyed by the
 * key the page passes to <x-list-page>.
 */
class ListActions
{
    private static array $cache = [];

    public static function lists(): array
    {
        $technicians = fn () => User::where('role', 'technician')->orderBy('name')->pluck('name', 'id')->all();
        $labels = fn (array $keys) => collect($keys)->mapWithKeys(fn ($k) => [$k => Str::headline($k)])->all();

        return [
            'leads' => [
                'model' => Lead::class, 'noun' => 'lead', 'index' => 'leads.index',
                'delete' => 'leads.destroy',
                'update' => [
                    'status' => ['label' => 'Status', 'route' => 'leads.status', 'options' => fn () => $labels(['new', 'contacted', 'quoted', 'won', 'lost'])],
                ],
                'email' => fn (Lead $m) => $m->email,
            ],
            'quotations' => [
                'model' => Quotation::class, 'noun' => 'quotation', 'index' => 'quotations.index',
                'delete' => 'quotations.destroy',
                'update' => [
                    'status' => ['label' => 'Status', 'route' => 'quotations.markSent', 'options' => fn () => ['sent' => 'Sent (drafts only)']],
                ],
                'email' => fn (Quotation $m) => $m->lead?->email,
            ],
            'tickets' => [
                'model' => ServiceTicket::class, 'noun' => 'ticket', 'index' => 'service-tickets.index',
                'delete' => 'service-tickets.destroy',
                'update' => [
                    'status' => ['label' => 'Status', 'route' => 'service-tickets.updateStatus', 'options' => fn () => ServiceTicket::statusOptions()],
                    'assigned_technician_id' => ['label' => 'Technician', 'route' => 'service-tickets.assign', 'options' => $technicians],
                ],
                'email' => fn (ServiceTicket $m) => $m->lead?->email,
                'create' => ['New AMC contract' => 'amcs.create', 'New RMA claim' => 'rma.create'],
            ],
            'amcs' => [
                'model' => AmcContract::class, 'noun' => 'AMC contract', 'index' => 'amcs.index',
                'delete' => 'amcs.destroy',
                'update' => [
                    'status' => ['label' => 'Status', 'route' => 'amcs.updateStatus', 'options' => fn () => $labels(['pending', 'active', 'expired', 'cancelled'])],
                ],
                'email' => fn (AmcContract $m) => $m->lead?->email,
                'create' => ['Log service ticket' => 'service-tickets.create'],
            ],
            'projects' => [
                'model' => Project::class, 'noun' => 'project', 'index' => 'projects.index',
                'delete' => 'projects.destroy',
                'update' => [
                    'status' => ['label' => 'Status', 'route' => 'projects.updateStatus', 'options' => fn () => $labels(['draft', 'pending_approval', 'approved', 'in_progress', 'completed', 'on_hold', 'rejected', 'cancelled'])],
                    'lead_technician_id' => ['label' => 'Lead technician', 'route' => 'projects.assign-technician', 'options' => $technicians],
                ],
                'email' => fn (Project $m) => $m->lead?->email,
                'create' => ['New site' => 'sites.create', 'New purchase order' => 'purchase-orders.create'],
            ],
            'sites' => [
                'model' => Site::class, 'noun' => 'site', 'index' => 'sites.index',
                'delete' => 'sites.destroy',
                'email' => fn (Site $m) => $m->lead?->email,
                'create' => ['New site survey' => 'site-surveys.create'],
            ],
            'surveys' => [
                'model' => SiteSurvey::class, 'noun' => 'site survey', 'index' => 'site-surveys.index',
                'delete' => 'site-surveys.destroy',
                'email' => fn (SiteSurvey $m) => $m->lead?->email,
                'create' => ['New lead' => 'leads.create'],
            ],
            'purchase-orders' => [
                'model' => PurchaseOrder::class, 'noun' => 'purchase order', 'index' => 'purchase-orders.index',
                'delete' => 'purchase-orders.destroy',
                'update' => [
                    'status' => ['label' => 'Status', 'route' => 'purchase-orders.updateStatus', 'options' => fn () => $labels(['draft', 'ordered', 'cancelled'])],
                ],
                'email' => fn (PurchaseOrder $m) => $m->supplier?->email,
                'create' => ['New supplier' => 'suppliers.create', 'New product' => 'products.create'],
            ],
            'products' => [
                'model' => Product::class, 'noun' => 'product', 'index' => 'products.index',
                'delete' => 'products.destroy',
                'create' => ['New supplier' => 'suppliers.create', 'New purchase order' => 'purchase-orders.create'],
            ],
            'suppliers' => [
                'model' => Supplier::class, 'noun' => 'supplier', 'index' => 'suppliers.index',
                'delete' => 'suppliers.destroy',
                'email' => fn (Supplier $m) => $m->email,
                'create' => ['New product' => 'products.create', 'New purchase order' => 'purchase-orders.create'],
            ],
            'equipment' => [
                'model' => InstalledEquipment::class, 'noun' => 'equipment record', 'index' => 'equipment.index',
                'delete' => 'equipment.destroy',
                'email' => fn (InstalledEquipment $m) => $m->lead?->email,
                'create' => ['New RMA claim' => 'rma.create', 'Log service ticket' => 'service-tickets.create'],
            ],
            'rma' => [
                'model' => RmaClaim::class, 'noun' => 'RMA claim', 'index' => 'rma.index',
                'delete' => 'rma.destroy',
                'email' => fn (RmaClaim $m) => $m->lead?->email,
                'create' => ['Add equipment' => 'equipment.create'],
            ],
            'workers' => [
                'model' => Worker::class, 'noun' => 'worker', 'index' => 'workers.index',
                'delete' => 'workers.destroy',
                'update' => [
                    // Approving a sign-up takes no input; the record shows approved afterwards
                    'registration_status' => ['label' => 'Sign-up', 'route' => 'workers.approve', 'options' => fn () => ['approved' => 'Approved']],
                ],
                'create' => ['Weekly wages' => 'wages.index', 'Worker attendance' => 'workers.attendance.index'],
            ],
            'users' => [
                'model' => User::class, 'noun' => 'user', 'index' => 'admin.users.index',
                'delete' => 'admin.users.destroy',
                'email' => fn (User $m) => $m->email,
                'create' => ['Add worker' => 'workers.create'],
            ],
            'invoices' => [
                'model' => Invoice::class, 'noun' => 'invoice', 'index' => 'invoices.index',
                'email' => fn (Invoice $m) => $m->quotation?->lead?->email ?? $m->installationJob?->lead?->email,
            ],
            'jobs' => [
                'model' => InstallationJob::class, 'noun' => 'job', 'index' => 'jobs.index',
                'email' => fn (InstallationJob $m) => $m->lead?->email ?? $m->quotation?->lead?->email,
                'create' => ['Log service ticket' => 'service-tickets.create', 'New AMC contract' => 'amcs.create'],
            ],
            'jcr' => [
                'model' => JobCompletionReport::class, 'noun' => 'job completion report', 'index' => 'jcr.index',
                'email' => fn (JobCompletionReport $m) => $m->lead?->email,
            ],
        ];
    }

    public static function get(string $list): ?array
    {
        return static::lists()[$list] ?? null;
    }

    /**
     * What this user may do on a list: null when the list has no mass actions or the user can't open it.
     * Field options are only built here, for the page that shows them.
     */
    public static function forUser(string $list, ?User $user): ?array
    {
        $key = $list . ':' . ($user?->id ?? 0);
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        $config = static::get($list);
        if (!$config || !$user || !static::allowed($config['index'], $user)) {
            return static::$cache[$key] = null;
        }

        $fields = [];
        foreach ($config['update'] ?? [] as $field => $def) {
            if (static::allowed($def['route'], $user)) {
                $fields[] = [
                    'key' => $field,
                    'label' => $def['label'],
                    'options' => collect(($def['options'])())->map(fn ($label, $value) => ['value' => (string) $value, 'label' => $label])->values()->all(),
                ];
            }
        }

        // Create shortcuts are for the office: some create pages also check the role inside
        $create = [];
        foreach ($user->isOffice() ? ($config['create'] ?? []) : [] as $label => $route) {
            if (static::allowed($route, $user)) {
                $create[$label] = route($route);
            }
        }

        $actions = [
            'list' => $list,
            'noun' => $config['noun'],
            'delete' => isset($config['delete']) && static::allowed($config['delete'], $user),
            'fields' => $fields,
            // Mass Email and Export read whole tables, so they are for office users
            'email' => isset($config['email']) && $user->isOffice(),
            'export' => $user->isOffice(),
            'create' => $create,
        ];
        // Rows get tick boxes when there is something to do with picked records
        $actions['pick'] = $actions['delete'] || $fields || $actions['email'] || $actions['export'];

        return static::$cache[$key] = $actions;
    }

    /** Whether the user passes the role middleware on a named route (admin, office, internal...). */
    public static function allowed(string $routeName, User $user): bool
    {
        $route = Route::getRoutes()->getByName($routeName);
        if (!$route) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            $name = is_string($middleware) ? Str::before($middleware, ':') : '';
            $ok = match ($name) {
                'web', 'auth', 'verified' => true,
                'admin' => $user->isAdmin(),
                'office' => $user->isOffice(),
                'internal' => $user->isInternal(),
                'technician' => $user->isTechnician(),
                // Any other gate on these routes is unknown here, so the mass action is not offered
                default => false,
            };
            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    /**
     * Run one record through a named route's own controller action, as if its button were pressed.
     * Returns null when it went through, or the reason it didn't.
     */
    public static function replay(string $routeName, Model $record, array $input, callable $succeeded): ?string
    {
        $route = Route::getRoutes()->getByName($routeName);
        [$controllerClass, $method] = [$route->getControllerClass(), $route->getActionMethod()];
        $controller = app($controllerClass);
        $parameter = $route->parameterNames()[0];

        $original = app('request');
        $request = Request::create(route($routeName, $record), $route->methods()[0], $input);
        $request->setUserResolver($original->getUserResolver());
        $request->setLaravelSession($original->session());
        $bound = (clone $route)->bind($request);
        $bound->setParameter($parameter, $record);
        $request->setRouteResolver(fn () => $bound);

        // Plain Request and the record go in by name; form requests resolve from the bound request
        $arguments = [];
        foreach ((new \ReflectionMethod($controller, $method))->getParameters() as $p) {
            $type = $p->getType() instanceof \ReflectionNamedType ? $p->getType()->getName() : null;
            if ($type && is_a($record, $type)) {
                $arguments[$p->getName()] = $record;
            } elseif ($type === Request::class) {
                $arguments[$p->getName()] = $request;
            }
        }

        $session = $original->session();
        $session->forget(['status', 'success', 'error', 'errors']);
        app()->instance('request', $request);

        try {
            app()->call([$controller, $method], $arguments);
        } catch (ValidationException $e) {
            return collect($e->errors())->flatten()->first() ?? 'not valid';
        } catch (HttpExceptionInterface $e) {
            return $e->getMessage() !== '' ? $e->getMessage() : 'not allowed';
        } catch (Throwable $e) {
            report($e);
            return 'could not be changed';
        } finally {
            app()->instance('request', $original);
        }

        if ($succeeded()) {
            $session->forget(['status', 'success', 'error', 'errors']);
            return null;
        }

        // The action refused and said why on the way back
        $errors = $session->get('errors');
        $reason = $session->get('error')
            ?? ($errors ? collect($errors->getMessages())->flatten()->first() : null)
            ?? $session->get('status')
            ?? 'not changed';
        $session->forget(['status', 'success', 'error', 'errors']);

        return $reason;
    }
}

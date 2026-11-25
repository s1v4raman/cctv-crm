<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MobileScannerSyncController extends Controller
{
    /**
     * Create a new server-generated mobile pairing session (10 min TTL, one active pairing per user).
     */
    public function createPairing(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        // Invalidate any previous active pairing for this user
        $previousToken = Cache::get("scanner_user_pairing_{$user->id}");
        if (is_string($previousToken) && $previousToken !== '') {
            Cache::forget("scanner_pairing_{$previousToken}");
            Cache::forget("scanner_queue_{$previousToken}");
            Cache::forget("scanner_connected_{$previousToken}");
        }

        $token = Str::random(40);
        $ttl = now()->addMinutes(10);

        Cache::put("scanner_pairing_{$token}", [
            'user_id'    => $user->id,
            'created_at' => now()->toIso8601String(),
        ], $ttl);

        Cache::put("scanner_user_pairing_{$user->id}", $token, $ttl);

        $baseUrl = url('/mobile-scanner/' . $token);
        if (app()->environment('local')) {
            $lanIp = self::detectLanIp();
            $port = $request->getPort();
            $portSuffix = ($port && !in_array((int) $port, [80, 443], true)) ? (':' . $port) : '';
            $scheme = $request->getScheme();
            $baseUrl = "{$scheme}://{$lanIp}{$portSuffix}/mobile-scanner/{$token}";
        }

        return response()->json([
            'token'      => $token,
            'url'        => $baseUrl,
            'expires_in' => 600,
        ]);
    }

    /**
     * Display the mobile scanner interface for smartphones (requires auth & pairing ownership).
     */
    public function show(Request $request, $token = null)
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        $target = $request->query('target', 'serial_number');
        $label = $request->query('label', 'Hardware Serial / MAC');

        $pairing = $token ? Cache::get("scanner_pairing_{$token}") : null;
        if (!$pairing || (int) ($pairing['user_id'] ?? 0) !== (int) $user->id) {
            return response()
                ->view('mobile-scanner', [
                    'token'        => $token ?? '',
                    'target'       => $target,
                    'label'        => $label,
                    'pairingError' => 'This mobile scanner pairing link is invalid, expired, or belongs to another user. Please generate a fresh QR code from your workstation.',
                ], 403)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        return response()
            ->view('mobile-scanner', [
                'token'        => $token,
                'target'       => $target,
                'label'        => $label,
                'pairingError' => null,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Mobile phone pushes a detected barcode/MAC to the session queue.
     */
    public function push(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:64'],
            'code'  => ['required', 'string', 'min:1', 'max:255'],
        ]);

        $token = $validated['token'];
        $pairing = Cache::get("scanner_pairing_{$token}");
        if (!$pairing || (int) ($pairing['user_id'] ?? 0) !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or unauthorized pairing session.',
            ], 403);
        }

        $code = trim($validated['code']);
        $label = trim((string) $request->input('label', ''));

        $queue = Cache::get("scanner_queue_{$token}", []);
        if (!is_array($queue)) {
            $queue = [];
        }

        $queue[] = [
            'code'       => $code,
            'label'      => $label,
            'scanned_at' => now()->toIso8601String(),
        ];

        $ttl = now()->addMinutes(10);
        Cache::put("scanner_queue_{$token}", $queue, $ttl);
        Cache::put("scanner_connected_{$token}", true, $ttl);

        return response()->json([
            'success'      => true,
            'message'      => 'Scanned code sent to laptop successfully',
            'code'         => $code,
            'queued_count' => count($queue),
        ]);
    }

    /**
     * Analyze uploaded photo for barcodes/QR using server-side Python OpenCV engine.
     */
    public function analyzeImage(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'token'        => 'required|string|max:64',
            'image'        => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'image_base64' => 'nullable|string',
        ]);

        $token = $request->input('token');
        $pairing = Cache::get("scanner_pairing_{$token}");
        if (!$pairing || (int) ($pairing['user_id'] ?? 0) !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or unauthorized pairing session.',
            ], 403);
        }

        $tempPath = null;
        $isTempCreated = false;

        if ($request->hasFile('image')) {
            $tempPath = $request->file('image')->getRealPath();
        } elseif ($request->filled('image_base64')) {
            $data = $request->input('image_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $data)) {
                $data = substr($data, strpos($data, ',') + 1);
            }
            $data = base64_decode($data);
            if ($data) {
                $tempPath = tempnam(sys_get_temp_dir(), 'scan_');
                file_put_contents($tempPath, $data);
                $isTempCreated = true;
            }
        }

        if (!$tempPath || !file_exists($tempPath)) {
            return response()->json(['success' => false, 'message' => 'No image received'], 400);
        }

        try {
            $scriptPath = base_path('app/Scripts/detect_barcode.py');
            $pythonCmd = 'python ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($tempPath);
            $output = @shell_exec($pythonCmd);

            $result = json_decode((string) $output, true);

            if ($result && !empty($result['success']) && !empty($result['composite_code'])) {
                $code = $result['composite_code'];

                $queue = Cache::get("scanner_queue_{$token}", []);
                if (!is_array($queue)) {
                    $queue = [];
                }
                $queue[] = [
                    'code'       => $code,
                    'label'      => '',
                    'scanned_at' => now()->toIso8601String(),
                ];

                $ttl = now()->addMinutes(10);
                Cache::put("scanner_queue_{$token}", $queue, $ttl);
                Cache::put("scanner_connected_{$token}", true, $ttl);

                return response()->json([
                    'success'   => true,
                    'code'      => $code,
                    'parsed'    => $result['parsed'] ?? null,
                    'raw_codes' => $result['raw_codes'] ?? [],
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Server-side barcode analysis error: ' . $e->getMessage());
        } finally {
            if ($isTempCreated && $tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No barcode detected by server engine',
        ]);
    }

    /**
     * Laptop polls this endpoint to receive all queued scanned codes and clear them.
     */
    public function poll(Request $request, $token): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$token) {
            return response()->json(['status' => 'error', 'message' => 'Token missing'], 400);
        }

        $pairing = Cache::get("scanner_pairing_{$token}");
        if (!$pairing || (int) ($pairing['user_id'] ?? 0) !== (int) $user->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid or unauthorized pairing session.',
            ], 403);
        }

        $queue = Cache::get("scanner_queue_{$token}", []);
        if (is_array($queue) && count($queue) > 0) {
            Cache::forget("scanner_queue_{$token}");
            $latest = $queue[count($queue) - 1];

            return response()->json([
                'status'     => 'scanned',
                'codes'      => $queue,
                'code'       => $latest['code'] ?? '',
                'scanned_at' => $latest['scanned_at'] ?? null,
            ]);
        }

        $isConnected = Cache::has("scanner_connected_{$token}");

        return response()->json([
            'status' => $isConnected ? 'connected' : 'waiting',
            'codes'  => [],
        ]);
    }

    /**
     * Mobile phone reports heartbeat / presence.
     */
    public function heartbeat(Request $request, $token): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        $pairing = Cache::get("scanner_pairing_{$token}");
        if (!$pairing || (int) ($pairing['user_id'] ?? 0) !== (int) $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        Cache::put("scanner_connected_{$token}", true, now()->addMinutes(10));

        return response()->json(['status' => 'ok']);
    }

    /**
     * Search customers and active projects for the Register Asset modal select (limit 20).
     */
    public function searchTargets(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        $q = trim((string) $request->input('q', ''));
        $items = [];

        $projectsQuery = Project::whereNull('deleted_at')
            ->whereNotNull('lead_id')
            ->with('lead:id,customer_name,site_address');

        if ($q !== '') {
            $projectsQuery->where(function ($sub) use ($q) {
                $sub->where('project_code', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhereHas('lead', function ($lq) use ($q) {
                        $lq->where('customer_name', 'like', "%{$q}%")
                           ->orWhere('site_address', 'like', "%{$q}%");
                    });
            });
        }

        $projects = $projectsQuery->orderBy('title')->limit(10)->get(['id', 'project_code', 'title', 'lead_id']);
        $seenLeadIds = [];

        foreach ($projects as $prj) {
            $seenLeadIds[] = $prj->lead_id;
            $code = $prj->project_code ?: ('PRJ-' . $prj->id);
            $clientName = $prj->lead?->customer_name ?? 'Client';
            $items[] = [
                'id'     => $prj->lead_id,
                'label'  => "{$code} - {$prj->title} ({$clientName})",
                'detail' => Str::limit((string) ($prj->lead?->site_address ?? 'Active Project'), 60),
            ];
        }

        $remaining = 20 - count($items);
        if ($remaining > 0) {
            $leadsQuery = Lead::query();
            if ($q !== '') {
                $leadsQuery->where(function ($sub) use ($q) {
                    $sub->where('customer_name', 'like', "%{$q}%")
                        ->orWhere('site_address', 'like', "%{$q}%");
                });
            }

            $leads = $leadsQuery->orderBy('customer_name')
                ->limit($remaining)
                ->get(['id', 'customer_name', 'site_address']);

            foreach ($leads as $lead) {
                if (in_array($lead->id, $seenLeadIds, true)) {
                    continue;
                }
                $items[] = [
                    'id'     => $lead->id,
                    'label'  => $lead->customer_name,
                    'detail' => Str::limit((string) ($lead->site_address ?? 'Customer Site'), 60),
                ];
            }
        }

        return response()->json([
            'items' => array_slice($items, 0, 20),
        ]);
    }

    /**
     * Accurately detect local WiFi / Ethernet IPv4 address only in local environments.
     */
    public static function detectLanIp(): string
    {
        if (!app()->environment('local')) {
            return request()?->getHost() ?? 'localhost';
        }

        $fallbackIp = request()?->getHost() ?? '127.0.0.1';

        if (function_exists('exec')) {
            @exec('ipconfig', $ipOutput);
            if (!empty($ipOutput)) {
                $foundIp = null;
                $isWifiOrEth = false;
                foreach ($ipOutput as $line) {
                    if (preg_match('/(?:Wireless LAN adapter Wi-?Fi|Ethernet adapter)/i', $line)) {
                        $isWifiOrEth = true;
                    } elseif (preg_match('/adapter/i', $line)) {
                        $isWifiOrEth = false;
                    }
                    if ($isWifiOrEth && preg_match('/IPv4 Address[ .:]+([0-9.]+)/i', $line, $matches)) {
                        $candidate = trim($matches[1]);
                        if ($candidate !== '127.0.0.1' && !str_starts_with($candidate, '192.168.137.')) {
                            $foundIp = $candidate;
                            break;
                        }
                    }
                }
                if ($foundIp) {
                    return $foundIp;
                }
            }
        }

        $hostIp = @gethostbyname(gethostname());
        if ($hostIp && $hostIp !== '127.0.0.1' && !str_starts_with($hostIp, '192.168.137.')) {
            return $hostIp;
        }

        return $fallbackIp;
    }

    /**
     * Helper to retrieve detected LAN IP for QR codes (local environment only).
     */
    public function getLanInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        $lanIp = self::detectLanIp();

        return response()->json([
            'lan_ip' => $lanIp,
            'host'   => $request->getHost(),
            'port'   => $request->getPort() ?: 8000,
        ]);
    }
}

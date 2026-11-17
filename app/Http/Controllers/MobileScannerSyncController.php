<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MobileScannerSyncController extends Controller
{
    /**
     * Display the mobile scanner interface for smartphones.
     */
    public function show(Request $request, $token = null)
    {
        $token = $token ?: $request->query('token', Str::random(12));
        $target = $request->query('target', 'serial_number');
        $label = $request->query('label', 'Hardware Serial / MAC');

        // Mark this token as connected
        Cache::put("scanner_connected_{$token}", true, 180);

        return response()
            ->view('mobile-scanner', [
                'token' => $token,
                'target' => $target,
                'label' => $label,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Mobile phone pushes a detected barcode/MAC to the session.
     */
    public function push(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:64',
            'code' => 'required|string|max:255',
        ]);

        $token = $request->input('token');
        $code = trim($request->input('code'));
        $label = $request->input('label', '');

        Cache::put("scanner_sync_{$token}", [
            'code' => $code,
            'label' => $label,
            'scanned_at' => now()->toIso8601String(),
        ], 300); // 5 minute TTL

        Cache::put("scanner_connected_{$token}", true, 180);

        return response()->json([
            'success' => true,
            'message' => 'Scanned code sent to laptop successfully',
            'code' => $code,
        ]);
    }

    /**
     * Laptop polls this endpoint to receive the scanned code.
     */
    public function poll($token)
    {
        if (!$token) {
            return response()->json(['status' => 'error', 'message' => 'Token missing'], 400);
        }

        $data = Cache::get("scanner_sync_{$token}");
        if ($data && !empty($data['code'])) {
            // Found scanned code! Clear it so it doesn't re-trigger
            Cache::forget("scanner_sync_{$token}");

            return response()->json([
                'status' => 'scanned',
                'code' => $data['code'],
                'scanned_at' => $data['scanned_at'] ?? null,
            ]);
        }

        $isConnected = Cache::has("scanner_connected_{$token}");

        return response()->json([
            'status' => $isConnected ? 'connected' : 'waiting',
        ]);
    }

    /**
     * Mobile phone reports heartbeat / presence.
     */
    public function heartbeat(Request $request, $token)
    {
        Cache::put("scanner_connected_{$token}", true, 120);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Accurately detect local WiFi / Ethernet IPv4 address, filtering out virtual hotspot adapters.
     */
    public static function detectLanIp(): string
    {
        $lanIp = '192.168.29.168';

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

        $hostIp = gethostbyname(gethostname());
        if ($hostIp && $hostIp !== '127.0.0.1' && !str_starts_with($hostIp, '192.168.137.')) {
            return $hostIp;
        }

        return $lanIp;
    }

    /**
     * Helper to retrieve detected LAN IP for QR codes.
     */
    public function getLanInfo(Request $request)
    {
        $lanIp = self::detectLanIp();

        return response()->json([
            'lan_ip' => $lanIp,
            'host' => $request->getHost(),
            'port' => $request->getPort() ?: 8000,
        ]);
    }
}

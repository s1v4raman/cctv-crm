<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\IpDevice;
use App\Models\IpDeviceRevealLog;
use App\Models\ProjectAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IpDeviceController extends Controller
{
    /**
     * Store a single IP device in the project's register.
     */
    public function store(Request $request, Project $project)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isTechnician()) {
            abort(403, 'Employees have view-only access to the IP device register. Only technicians and administrators can add or edit devices.');
        }

        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:100',
            'ip_address' => 'required|ip',
            'subnet_mask' => 'nullable|string|max:50',
            'gateway' => 'nullable|ip',
            'dns_server' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'web_port' => 'nullable|integer|min:1|max:65535',
            'rtsp_port' => 'nullable|integer|min:1|max:65535',
            'server_port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'status' => 'nullable|in:configured,online,offline,pending',
            'notes' => 'nullable|string',
        ]);

        // Check for duplicate IP in this project
        $duplicate = IpDevice::where('project_id', $project->id)
            ->where('ip_address', $validated['ip_address'])
            ->exists();

        if ($duplicate) {
            return back()->with('error', "IP conflict: Address {$validated['ip_address']} is already assigned in this project!")->withInput();
        }

        $validated['project_id'] = $project->id;
        $device = IpDevice::create($validated);

        ProjectAuditLog::logChange(
            $project,
            'device_added',
            null,
            null,
            "IP Device '{$device->device_name}' ({$device->ip_address}) added"
        );

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'ip_devices'])
            ->with('status', "Device '{$device->device_name}' added to IP Register!");
    }

    /**
     * Bulk sequential IP generator.
     * E.g. Start IP 192.168.1.101, count 16, prefix "Camera "
     */
    public function bulkStore(Request $request, Project $project)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isTechnician()) {
            abort(403, 'Employees have view-only access to the IP device register. Only technicians and administrators can add or edit devices.');
        }

        $validated = $request->validate([
            'start_ip' => 'required|ip',
            'count' => 'required|integer|min:1|max:254',
            'device_type' => 'required|string|max:100',
            'name_prefix' => 'required|string|max:100',
            'start_number' => 'nullable|integer|min:1',
            'subnet_mask' => 'nullable|string|max:50',
            'gateway' => 'nullable|ip',
            'web_port' => 'nullable|integer|min:1|max:65535',
            'rtsp_port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'status' => 'nullable|in:configured,online,offline,pending',
        ]);

        $ipParts = explode('.', $validated['start_ip']);
        $baseIp = $ipParts[0] . '.' . $ipParts[1] . '.' . $ipParts[2];
        $startOctet = (int) $ipParts[3];
        $count = (int) $validated['count'];
        $startNumber = (int) ($validated['start_number'] ?? 1);

        $created = 0;
        $skipped = 0;

        for ($i = 0; $i < $count; $i++) {
            $currentOctet = $startOctet + $i;
            if ($currentOctet > 254) {
                break;
            }

            $ip = "{$baseIp}.{$currentOctet}";
            $deviceNum = str_pad($startNumber + $i, 2, '0', STR_PAD_LEFT);
            $deviceName = "{$validated['name_prefix']} {$deviceNum}";

            // Check duplicate
            if (IpDevice::where('project_id', $project->id)->where('ip_address', $ip)->exists()) {
                $skipped++;
                continue;
            }

            IpDevice::create([
                'project_id' => $project->id,
                'device_name' => $deviceName,
                'device_type' => $validated['device_type'],
                'ip_address' => $ip,
                'subnet_mask' => $validated['subnet_mask'] ?? '255.255.255.0',
                'gateway' => $validated['gateway'] ?? "{$baseIp}.1",
                'web_port' => $validated['web_port'] ?? 80,
                'rtsp_port' => $validated['rtsp_port'] ?? 554,
                'username' => $validated['username'] ?? 'admin',
                'password' => $validated['password'] ?? null,
                'status' => $validated['status'] ?? 'configured',
            ]);

            $created++;
        }

        ProjectAuditLog::logChange(
            $project,
            'bulk_devices_generated',
            null,
            null,
            "Bulk generated {$created} IP devices ({$validated['name_prefix']}) from {$validated['start_ip']}"
        );

        $msg = "Successfully generated {$created} IP devices!";
        if ($skipped > 0) {
            $msg .= " ({$skipped} IPs skipped due to conflicts)";
        }

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'ip_devices'])
            ->with('status', $msg);
    }

    /**
     * Update an IP device.
     */
    public function update(Request $request, Project $project, IpDevice $device)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isTechnician()) {
            abort(403, 'Employees have view-only access to the IP device register. Only technicians and administrators can add or edit devices.');
        }

        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:100',
            'ip_address' => 'required|ip',
            'subnet_mask' => 'nullable|string|max:50',
            'gateway' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'web_port' => 'nullable|integer|min:1|max:65535',
            'rtsp_port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'status' => 'nullable|in:configured,online,offline,pending',
            'notes' => 'nullable|string',
        ]);

        // Duplicate IP check excluding current
        $duplicate = IpDevice::where('project_id', $project->id)
            ->where('ip_address', $validated['ip_address'])
            ->where('id', '!=', $device->id)
            ->exists();

        if ($duplicate) {
            return back()->with('error', "IP conflict: Address {$validated['ip_address']} is already in use by another device!")->withInput();
        }

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $device->update($validated);

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'ip_devices'])
            ->with('status', "Device '{$device->device_name}' updated successfully.");
    }

    /**
     * Delete an IP device from the register.
     */
    public function destroy(Project $project, IpDevice $device)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete IP devices.');
        }

        $name = $device->device_name;
        $device->delete();

        ProjectAuditLog::logChange(
            $project,
            'device_deleted',
            null,
            null,
            "IP Device '{$name}' removed from register"
        );

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'ip_devices'])
            ->with('status', "Device '{$name}' deleted.");
    }

    /**
     * Secure password reveal endpoint with audit logging.
     */
    public function revealPassword(Request $request, Project $project, IpDevice $device)
    {
        if ($device->project_id !== $project->id) {
            abort(404);
        }

        // Log the reveal action
        IpDeviceRevealLog::create([
            'ip_device_id' => $device->id,
            'user_id' => Auth::id(),
            'revealed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'password' => $device->password ?? '',
            'message' => 'Password revealed and action logged to audit trail.'
        ]);
    }

    /**
     * Export the IP register to CSV sheet.
     */
    public function export(Project $project)
    {
        $devices = $project->ipDevices()->orderBy('ip_address')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"IP_Register_{$project->project_code}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($project, $devices) {
            $handle = fopen('php://output', 'w');

            // Metadata Header
            fputcsv($handle, ['SecureVision CRM - IP Device Configuration Register']);
            fputcsv($handle, ['Project Code', $project->project_code]);
            fputcsv($handle, ['Project Title', $project->title]);
            fputcsv($handle, ['Client / Site', $project->site?->name ?? $project->company_name]);
            fputcsv($handle, ['Executing Company', $project->company?->name ?? 'Precision IT Systems']);
            fputcsv($handle, ['Exported On', now()->toDateTimeString()]);
            fputcsv($handle, []);

            // Columns (Note: passwords excluded from bulk export for high security)
            fputcsv($handle, [
                'Device Name',
                'Device Type',
                'IP Address',
                'Subnet Mask',
                'Gateway',
                'MAC Address',
                'Location',
                'Web Port',
                'RTSP Port',
                'Username',
                'Status',
                'Notes'
            ]);

            foreach ($devices as $d) {
                fputcsv($handle, [
                    $d->device_name,
                    $d->device_type,
                    $d->ip_address,
                    $d->subnet_mask ?? '255.255.255.0',
                    $d->gateway ?? '',
                    $d->mac_address ?? '',
                    $d->location ?? '',
                    $d->web_port ?? 80,
                    $d->rtsp_port ?? 554,
                    $d->username ?? 'admin',
                    $d->status ?? 'configured',
                    $d->notes ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

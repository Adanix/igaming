<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function __construct(
        protected AuditLoggerService $auditLogger
    ) {}

    /**
     * Display Global Platform Configuration.
     */
    public function index(): View
    {
        $settings = SystemSetting::all()->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update Global Platform Configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                $setting->value = (string) $value;
                $setting->save();
            }
        }

        $this->auditLogger->log(
            'settings.update',
            $request->user(),
            SystemSetting::class,
            null,
            'Updated global system settings configuration.'
        );

        return redirect()->route('admin.settings.index')
            ->with('status', 'Platform settings updated successfully.');
    }
}

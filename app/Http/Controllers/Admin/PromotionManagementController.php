<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionManagementController extends Controller
{
    public function __construct(
        protected AuditLoggerService $auditLogger
    ) {}

    /**
     * Display all promotions and active bonus campaign metrics.
     */
    public function index(): View
    {
        $promotions = Promotion::withCount('playerBonuses')
            ->orderBy('id')
            ->get();

        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Create a new promotion campaign.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:promotions,code', 'max:50'],
            'title_en' => ['required', 'string', 'max:150'],
            'title_id' => ['required', 'string', 'max:150'],
            'description_en' => ['nullable', 'string'],
            'description_id' => ['nullable', 'string'],
            'promo_type' => ['required', 'in:deposit_match,free_chip,cashback'],
            'match_percentage' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'max_bonus_usd' => ['required', 'numeric', 'min:1'],
            'fixed_bonus_usd' => ['nullable', 'numeric', 'min:0'],
            'wagering_multiplier' => ['required', 'numeric', 'min:1', 'max:100'],
        ]);

        $promotion = Promotion::create([
            'code' => strtoupper($validated['code']),
            'title_en' => $validated['title_en'],
            'title_id' => $validated['title_id'],
            'description_en' => $validated['description_en'] ?? '',
            'description_id' => $validated['description_id'] ?? '',
            'promo_type' => $validated['promo_type'],
            'match_percentage' => $validated['match_percentage'] ?? 100.0,
            'max_bonus_usd' => $validated['max_bonus_usd'],
            'fixed_bonus_usd' => $validated['fixed_bonus_usd'] ?? 0.0,
            'wagering_multiplier' => $validated['wagering_multiplier'],
            'status' => 'active',
        ]);

        $this->auditLogger->log(
            'promotion.create',
            $request->user(),
            Promotion::class,
            $promotion->id,
            "Created promotional campaign: {$promotion->title_en} ({$promotion->code})"
        );

        return redirect()->route('admin.promotions.index')
            ->with('status', "Promotion campaign '{$promotion->title_en}' created successfully.");
    }

    /**
     * Toggle active/inactive state of a promotion.
     */
    public function toggleStatus(int $id, Request $request): RedirectResponse
    {
        $promotion = Promotion::findOrFail($id);
        $newStatus = $promotion->status === 'active' ? 'inactive' : 'active';
        $promotion->status = $newStatus;
        $promotion->save();

        $this->auditLogger->log(
            'promotion.toggle_status',
            $request->user(),
            Promotion::class,
            $promotion->id,
            "Toggled promotion {$promotion->code} to {$newStatus}"
        );

        return redirect()->back()
            ->with('status', "Promotion {$promotion->code} is now {$newStatus}.");
    }
}

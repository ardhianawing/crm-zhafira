<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappTemplateController extends Controller
{
    public function index(): View
    {
        $templates = WhatsappTemplate::ordered()->get();

        return view('admin.whatsapp-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.whatsapp-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:100',
            'isi_template' => 'required|string',
            'fase' => 'nullable|integer|between:0,3',
        ]);

        $maxUrutan = WhatsappTemplate::max('urutan') ?? 0;

        WhatsappTemplate::create([
            'nama_template' => $validated['nama_template'],
            'isi_template' => $validated['isi_template'],
            'fase' => $validated['fase'] ?? null,
            'is_active' => true,
            'urutan' => $maxUrutan + 1,
        ]);

        return redirect()
            ->route('admin.whatsapp-templates.index')
            ->with('success', 'Template berhasil ditambahkan.');
    }

    public function edit(WhatsappTemplate $whatsappTemplate): View
    {
        return view('admin.whatsapp-templates.edit', compact('whatsappTemplate'));
    }

    public function update(Request $request, WhatsappTemplate $whatsappTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:100',
            'isi_template' => 'required|string',
            'fase' => 'nullable|integer|between:0,3',
        ]);

        $whatsappTemplate->update([
            'nama_template' => $validated['nama_template'],
            'isi_template' => $validated['isi_template'],
            'fase' => $validated['fase'] ?? null,
        ]);

        return redirect()
            ->route('admin.whatsapp-templates.index')
            ->with('success', 'Template berhasil diupdate.');
    }

    public function destroy(WhatsappTemplate $whatsappTemplate): RedirectResponse
    {
        $whatsappTemplate->delete();

        return redirect()
            ->route('admin.whatsapp-templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    public function toggleStatus(WhatsappTemplate $whatsappTemplate): RedirectResponse
    {
        $whatsappTemplate->update([
            'is_active' => !$whatsappTemplate->is_active,
        ]);

        $status = $whatsappTemplate->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Template berhasil {$status}.");
    }
}

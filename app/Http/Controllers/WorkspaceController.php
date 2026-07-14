<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function index(Request $request): View
    {
        $workspaces = $request->user()
            ->workspaces()
            ->latest('updated_at')
            ->get();

        return view('dashboard.workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    }

    public function show(Workspace $workspace): View
    {
        $evaluations = $workspace->evaluations()
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.workspaces.show', [
            'workspace' => $workspace,
            'evaluations' => $evaluations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // dd($request->workspace_name);
        $request->validate([
            'workspace_name' => ['required', 'min:3'],
        ]);

        $request->user()->workspaces()->create([
            'name' => $request->workspace_name,
        ]);

        // return redirect()->route('dashboard.workspaces.index');
        return redirect()->route('dashboard.workspaces.index');
    }

    public function destroy(Workspace $workspace): RedirectResponse
    {
        $workspace->delete();

        return redirect()->route('dashboard.workspaces.index');
    }

    public function update(Workspace $workspace): RedirectResponse
    {
        // dd(request()->new_workspace_name);
        $validated = request()->validate([
            'workspace_name' => ['required', 'min:3'],
        ]);

        $workspace->update([
            'name' => $validated['workspace_name'],
        ]);

        return redirect()->route('dashboard.workspaces.show', $workspace);
    }
}

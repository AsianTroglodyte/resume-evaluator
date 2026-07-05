<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
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

    public function show(Request $request, Workspace $workspace): View
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(404);
        }

        return view('dashboard.workspaces.show', [
            'workspace' => $workspace,
            'evaluations' => [],
            'previewEvaluation' => mockEvaluation(),
        ]);
    }

    public function store(Request $request): View
    {
        // dd($request->workspace_name);

        $request->user()->workspaces()->create([
            'name' => $request->workspace_name
        ]);
        
        $workspaces = $request->user()
            ->workspaces()
            ->latest('updated_at')
            ->get();

        return view('dashboard.workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRule;
use App\Models\SlaRule;
use App\Models\WorkflowDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WorkflowSettingsController extends Controller
{
    public function edit(Request $request)
    {
        Gate::denyIf(! $request->user()->can('workflow.manage'));

        $workflow = WorkflowDefinition::where('is_active', true)->with('steps')->first();
        $slaRules = SlaRule::orderBy('id')->get();
        $approvalRules = ApprovalRule::orderBy('id')->get();

        return view('admin.workflow-settings', compact('workflow', 'slaRules', 'approvalRules'));
    }

    public function update(Request $request)
    {
        Gate::denyIf(! $request->user()->can('workflow.manage'));

        // Validated explicitly (rather than trusting raw array input) so a
        // blank "SLA (hours)" field can never write null/'' into sla_hours
        // — that previously reached Carbon::addHours() unguarded in
        // SLAService::statusFor() and crashed every page showing SLA status.
        $data = $request->validate([
            'sla' => 'nullable|array',
            'sla.*.sla_hours' => 'required|integer|min:1',
            'sla.*.due_soon_threshold_hours' => 'required|integer|min:0',
            'approval' => 'nullable|array',
            'approval.*.threshold_value' => 'required|numeric',
        ]);

        foreach ($data['sla'] ?? [] as $id => $row) {
            SlaRule::where('id', $id)->update([
                'sla_hours' => $row['sla_hours'],
                'due_soon_threshold_hours' => $row['due_soon_threshold_hours'],
            ]);
        }

        foreach ($data['approval'] ?? [] as $id => $row) {
            ApprovalRule::where('id', $id)->update([
                'threshold_value' => $row['threshold_value'],
                'is_active' => isset($request->input('approval')[$id]['is_active']),
            ]);
        }

        return back()->with('success', 'Workflow settings updated.');
    }
}

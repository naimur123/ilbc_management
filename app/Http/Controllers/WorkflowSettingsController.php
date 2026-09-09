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

        foreach ($request->input('sla', []) as $id => $row) {
            SlaRule::where('id', $id)->update([
                'sla_hours' => $row['sla_hours'],
                'due_soon_threshold_hours' => $row['due_soon_threshold_hours'],
            ]);
        }

        foreach ($request->input('approval', []) as $id => $row) {
            ApprovalRule::where('id', $id)->update([
                'threshold_value' => $row['threshold_value'],
                'is_active' => isset($row['is_active']),
            ]);
        }

        return back()->with('success', 'Workflow settings updated.');
    }
}

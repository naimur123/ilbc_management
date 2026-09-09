<?php

namespace App\Services;

use App\Models\Request as WorkRequest;
use App\Models\WorkflowAction;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use Illuminate\Support\Facades\Auth;

/**
 * Moves a request from one stage/status to another and records the action
 * for the visual timeline (Section 29). The 8-stage flow itself is data
 * (workflow_definitions/workflow_steps, seeded by WorkflowDefinitionSeeder)
 * so re-ordering or renaming a stage never touches this class.
 */
class WorkflowService
{
    public function ensureInstance(WorkRequest $request): WorkflowInstance
    {
        $instance = WorkflowInstance::where('request_id', $request->id)->first();

        if ($instance) {
            return $instance;
        }

        $definition = WorkflowDefinition::where('is_active', true)->first();
        $firstStep = $definition?->steps()->orderBy('step_order')->first();

        return WorkflowInstance::create([
            'request_id' => $request->id,
            'workflow_definition_id' => $definition?->id,
            'current_step_id' => $firstStep?->id,
        ]);
    }

    public function transition(WorkRequest $request, string $status, string $stage, string $action, ?string $remarks = null): void
    {
        $request->update([
            'status' => $status,
            'current_stage' => $stage,
            'current_stage_started_at' => now(),
        ]);

        $instance = $this->ensureInstance($request);
        $step = WorkflowStep::where('workflow_definition_id', $instance->workflow_definition_id)
            ->where('step_key', $stage)
            ->first();

        if ($step) {
            $instance->update(['current_step_id' => $step->id]);

            WorkflowAction::create([
                'workflow_instance_id' => $instance->id,
                'workflow_step_id' => $step->id,
                'action' => $action,
                'performed_by' => Auth::id(),
                'remarks' => $remarks,
                'performed_at' => now(),
            ]);
        }
    }

    /**
     * Section 29's timeline data: every stage in order, with its status
     * (done / current / pending) and the actions recorded against it.
     */
    public function timeline(WorkRequest $request): array
    {
        $instance = WorkflowInstance::with(['currentStep', 'actions.step', 'actions.performedBy'])
            ->where('request_id', $request->id)
            ->first();

        if (! $instance) {
            return [];
        }

        $steps = WorkflowStep::where('workflow_definition_id', $instance->workflow_definition_id)
            ->orderBy('step_order')->get();

        $currentOrder = $instance->currentStep?->step_order ?? 0;

        return $steps->map(function (WorkflowStep $step) use ($instance, $currentOrder) {
            $actions = $instance->actions->where('workflow_step_id', $step->id)->values();

            return [
                'step' => $step,
                'state' => $step->step_order < $currentOrder ? 'DONE'
                    : ($step->step_order === $currentOrder ? 'CURRENT' : 'PENDING'),
                'actions' => $actions,
            ];
        })->all();
    }
}

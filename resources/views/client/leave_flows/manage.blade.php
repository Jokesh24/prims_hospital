<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Leave Workflows - HRMS</title>
    @include('client.includes.header_links')
</head>

<body>
    @include('client.includes.sidebar')
    <div id="main">
        @include('client.includes.header')
        <div class="content">
            <div class="container-fluid py-2">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold m-0"><i class="bi bi-diagram-3 text-primary me-2"></i>Leave Workflows</h4>
                    <a href="{{ route('client.leave_flows.create') }}" class="btn btn-primary"><i
                            class="bi bi-plus-lg me-1"></i> New Workflow</a>
                </div>

                <div class="dashboard-card p-3 shadow-sm" style="border-radius:10px;">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Flow Name</th>
                                <th>Department Applied To</th>
                                <th>Approval Chain (Order)</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($flows as $flow)
                                <tr>
                                    <td class="fw-bold">{{ $flow->flow_name }}</td>
                                    <td>{{ $flow->department->department_name ?? 'Global (All Departments)' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            @foreach($flow->steps as $step)
                                                <span class="badge bg-secondary rounded-pill">
                                                    {{ $step->step_order }}. {{ $step->approver->employee_name }}
                                                    @if($step->is_final) (Final) @endif
                                                </span>
                                                @if(!$loop->last) <i class="bi bi-arrow-right text-muted mx-1"></i> @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $flow->status == 1 ? 'success' : 'danger' }}">
                                            {{ $flow->status == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('client.leave_flows.destroy', $flow->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Delete this workflow?');">
                                            @csrf
                                            <button class="btn btn-sm btn-light text-danger"><i
                                                    class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No workflows created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('client.includes.footer_links')
</body>

</html>
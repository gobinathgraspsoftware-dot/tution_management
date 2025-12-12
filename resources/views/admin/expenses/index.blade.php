@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-receipt"></i> Expense Management</h2>
        <div>
            @can('create-expenses')
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Expense
            </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Expenses</h6>
                    <h3>RM {{ number_format($summary['total_expenses'], 2) }}</h3>
                    <small>{{ $summary['expense_count'] }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Pending Approval</h6>
                    <h3>{{ $summary['pending_count'] }}</h3>
                    <small>RM {{ number_format($summary['pending_amount'], 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Average Expense</h6>
                    <h3>RM {{ number_format($summary['average_expense'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="card-title">Top Category</h6>
                    @if($summary['by_category']->isNotEmpty())
                    <h6>{{ $summary['by_category']->first()->name }}</h6>
                    <small>RM {{ number_format($summary['by_category']->first()->total, 2) }}</small>
                    @else
                    <h6>N/A</h6>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filters
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.expenses.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Description, Reference, Vendor..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="d-block">&nbsp;</label>
                        <div class="form-check">
                            <input type="checkbox" name="over_budget" value="1" class="form-check-input" id="overBudget" {{ request('over_budget') ? 'checked' : '' }}>
                            <label class="form-check-label" for="overBudget">
                                Over Budget Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                        @can('export-expenses')
                        <a href="{{ route('admin.expenses.export', request()->all()) }}" class="btn btn-success">
                            <i class="fas fa-file-export"></i> Export CSV
                        </a>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Expenses Table --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Expenses List ({{ $expenses->total() }} records)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Vendor</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>{{ $expense->category->name }}</td>
                            <td>
                                {{ Str::limit($expense->description, 50) }}
                                @if($expense->is_recurring)
                                <span class="badge bg-info"><i class="fas fa-sync"></i> Recurring</span>
                                @endif
                                @if($expense->isOverBudget())
                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Over Budget</span>
                                @endif
                            </td>
                            <td>
                                <strong>RM {{ number_format($expense->amount, 2) }}</strong>
                                @if($expense->budget_amount)
                                <br><small class="text-muted">Budget: RM {{ number_format($expense->budget_amount, 2) }}</small>
                                @endif
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</td>
                            <td>{{ $expense->vendor_name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $expense->getStatusBadgeClass() }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($expense->isPending())
                                        @can('edit-expenses')
                                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('approve-expenses')
                                        <form action="{{ route('admin.expenses.approve', $expense) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Approve" onclick="return confirm('Approve this expense?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No expenses found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

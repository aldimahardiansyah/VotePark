@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Welcome, {{ $user->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">This is your personal dashboard where you can view your unit information.</p>
                    </div>
                </div>

                @if ($units->isEmpty())
                    <div class="alert alert-info">
                        You don't have any units assigned to your account yet.
                    </div>
                @else
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">My Units</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Unit Code</th>
                                            <th>Tower</th>
                                            <th>NPP</th>
                                            <th>Events Participated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($units as $unit)
                                            <tr>
                                                <td><strong>{{ $unit->code }}</strong></td>
                                                <td>Tower {{ $unit->tower ?? '-' }}</td>
                                                <td>{{ $unit->npp ?? '-' }}</td>
                                                <td>{{ $unit->event->count() }} events</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

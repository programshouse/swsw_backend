@extends('admin.layouts.app')

@section('content')
    <div class="page-title">Accepted Delivery Users</div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">
            <div class="table-title">Delivery Users</div>
        </div>

        <div class="table-wrapper">

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Vehicle</th>
                        <th>Level</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Break Time</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($deliveries as $delivery)
                        <tr id="row-{{ $delivery->id }}">

                            <td>{{ $delivery->name }}</td>
                            <td>{{ $delivery->email }}</td>
                            <td>{{ $delivery->phone }}</td>
                            <td>{{ $delivery->type }}</td>

                            <td>
                                @if ($delivery->has_vehicle)
                                    {{ $delivery->vehicle_type }}
                                @else
                                    No Vehicle
                                @endif
                            </td>

                            <td>{{ $delivery->level->name }}</td>

                            <td>
                                @if ($delivery->image)
                                    <img src="{{ asset('storage/' . $delivery->image) }}" width="40" height="40"
                                        style="border-radius:50%">
                                @endif
                            </td>

                            <td>
                                <div class="circle" style="background-color: {{ $delivery->is_break ? 'red' : 'green' }};">
                                </div>
                            </td>

                            <td>
                                @if ($delivery->is_break && $delivery->break_started_at)
                                    <span class="timer"
                                        data-end="{{ $delivery->break_started_at->copy()->addMinutes($delivery->break_time)->timestamp }}">
                                    </span>
                                @else
                                    currently working
                                @endif
                            </td>

                            <td>

                                <div class="action-box">

                                    {{-- Promotion --}}
                                    <div class="action-item">
                                        <form method="POST"
                                            action="{{ route('admin.delivery.promotion', $delivery->id) }}">
                                            @csrf

                                            <select name="level_id" class="action-select">
                                                @foreach ($levels as $level)
                                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                                @endforeach
                                            </select>

                                            <button class="btn-small btn-primary" type="submit">
                                                ترقية
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Generated Code --}}
                                    @if (session('generated_code_user_id') == $delivery->id)
                                        <div class="action-item code-box" id="code-box-{{ $delivery->id }}">

                                            <span class="code-value" id="code-{{ $delivery->id }}">
                                                {{ session('generated_code') }}
                                            </span>

                                            <button class="btn-small btn-copy"
                                                onclick="copyAndHideCode(
                            'code-{{ $delivery->id }}',
                            'code-box-{{ $delivery->id }}'
                        )">
                                                نسخ
                                            </button>

                                        </div>
                                    @endif

                                    {{-- Generate Password --}}
                                    <div class="action-item">
                                        <form action="{{ route('admin.delivery.generate.password', $delivery->id) }}"
                                            method="post">
                                            @csrf

                                            <button class="btn-small btn-warning" type="submit">
                                                Generate Password
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Break --}}
                                    <div class="action-item">
                                        <form action="{{ route('admin.delivery.break', $delivery->id) }}" method="post">
                                            @csrf

                                            @if (!$delivery->is_break)
                                                <input type="text" name="break_time" class="action-input"
                                                    placeholder="minutes">
                                            @endif

                                            <button class="btn-small btn-danger">
                                                {{ !$delivery->is_break ? 'Take Break' : 'End Break' }}
                                            </button>
                                        </form>
                                    </div>

                                </div>

                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>
@endsection

@push('scripts')
    <Script>
        function setCircleStatus(value) {
            const circle = document.getElementById("status-circle");

            if (value == 1) {
                circle.style.backgroundColor = "green";
                circle.style.boxShadow = "0 0 10px green";
            } else {
                circle.style.backgroundColor = "#ccc";
                circle.style.boxShadow = "none";
            }
        }
    </Script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            console.log('script loaded');

            document.querySelectorAll('.timer').forEach(timer => {

                console.log('timer found');

                const end = parseInt(timer.dataset.end);

                function updateTimer() {

                    const now = Math.floor(Date.now() / 1000);
                    const remaining = Math.max(0, end - now);

                    const minutes = Math.floor(remaining / 60);
                    const seconds = remaining % 60;

                    timer.innerText =
                        `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }

                updateTimer();
                setInterval(updateTimer, 1000);
            });

        });
    </script>

    <script>
        function copyAndHideCode(codeId, boxId) {

            const codeElement = document.getElementById(codeId);

            if (!codeElement) {
                console.log('code not found');
                return;
            }

            const text = codeElement.innerText;

            navigator.clipboard.writeText(text)
                .then(() => {
                    alert('Code copied successfully');

                    document.getElementById(boxId).style.display = 'none';
                })
                .catch(err => {
                    console.log('Copy failed', err);
                });
        }
    </script>
@endpush

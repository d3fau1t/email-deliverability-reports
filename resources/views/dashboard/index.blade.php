@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold my-4">Email Deliverability Reports</h1>

        <form method="GET" action="{{ route('dashboard') }}" class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Box -->
                <div>
                    <input type="text" name="search" class="w-full p-2 border border-gray-300 rounded" placeholder="Search by recipient or subject" value="{{ old('search', request('search')) }}">
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" class="w-full p-2 border border-gray-300 rounded" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="sending" {{ request('status') == 'sending' ? 'selected' : '' }}>Sending</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="complained" {{ request('status') == 'complained' ? 'selected' : '' }}>Complained</option>
                        <option value="clicked" {{ request('status') == 'clicked' ? 'selected' : '' }}>Clicked</option>
                        <option value="opened" {{ request('status') == 'opened' ? 'selected' : '' }}>Opened</option>
                        <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                        <option value="temporary_failure" {{ request('status') == 'temporary_failure' ? 'selected' : '' }}>Temporary Failure</option>
                        <option value="permanent_failure" {{ request('status') == 'permanent_failure' ? 'selected' : '' }}>Permanent Failure</option>
                    </select>
                </div>

                <!-- Sender Domain Filter -->
                <div>
                    <select name="sender_domain" class="w-full p-2 border border-gray-300 rounded" onchange="this.form.submit()">
                        <option value="">All Sender Domains</option>
                        @foreach($senderDomains as $domain)
                            <option value="{{ $domain }}" {{ request('sender_domain') == $domain ? 'selected' : '' }}>{{ $domain }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sender Email Filter -->
                <div>
                    <select name="sender_email" class="w-full p-2 border border-gray-300 rounded" onchange="this.form.submit()">
                        <option value="">All Sender Emails</option>
                        @foreach($senderEmails as $email)
                            <option value="{{ $email }}" {{ request('sender_email') == $email ? 'selected' : '' }}>{{ $email }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4 flex items-center space-x-4">
                    <!-- Checkbox to Show Subject Column -->
                    <div class="flex items-center">
                        <input class="mr-2" type="checkbox" id="showSubject" name="show_subject" {{ request('show_subject') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label for="showSubject">Subject</label>
                    </div>

                    <!-- Checkbox to Show Recipient Name Column -->
                    <div class="flex items-center">
                        <input class="mr-2" type="checkbox" id="showRecipientName" name="show_recipient_name" {{ request('show_recipient_name') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label for="showRecipientName">To (Name)</label>
                    </div>

                    <!-- Checkbox to Show Sender Name Column -->
                    <div class="flex items-center">
                        <input class="mr-2" type="checkbox" id="showSenderName" name="show_sender_name" {{ request('show_sender_name') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label for="showSenderName">From (Name)</label>
                    </div>

                    <!-- Checkbox to Show Sender Domain Column -->
                    <div class="flex items-center">
                        <input class="mr-2" type="checkbox" id="showSenderDomain" name="show_sender_domain" {{ request('show_sender_domain') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label for="showSenderDomain">Mailgun Domain</label>
                    </div>
                </div>

                <!-- Download Buttons -->
                <div class="md:col-span-4 flex justify-end gap-4 mt-4 md:mt-0">
                    <a style="text-decoration: none; text-underline: none; padding-top: 11px!important;" href="{{ route('download', request()->query()) }}" class="w-full md:w-auto p-2 bg-green-600 text-white rounded text-center">
                        Download
                    </a>
                </div>
            </div>
        </form>

        <!-- Emails Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                <tr class="border-b border-gray-300">
                    <th class="p-2">ID</th>
                    <th class="p-2">Message ID</th>
                    <th class="p-2">To</th>
                    @if(request('show_recipient_name'))
                        <th class="p-2">To (Name)</th>
                    @endif
                    @if(request('show_subject'))
                        <th class="p-2">Subject</th>
                    @endif
                    <th class="p-2">From</th>
                    @if(request('show_sender_name'))
                        <th class="p-2">From (Name)</th>
                    @endif
                    @if(request('show_sender_domain'))
                        <th class="p-2">Mailgun Domain</th>
                    @endif
                    <th class="p-2">Status</th>
                    <th class="p-2">Sent</th>
                </tr>
                </thead>
                <tbody>
                @php $rowNumber = $emails->total() - ($emails->currentPage() - 1) * $emails->perPage(); @endphp
                @forelse ($emails as $email)
                    <tr class="border-b border-gray-200">
                        <td class="p-2">{{ $rowNumber-- }}</td>
                        <td class="p-2" style="min-width: 200px; white-space: nowrap;">
                            <button type="button" class="text-blue-600 underline" data-bs-toggle="modal" data-bs-target="#messageModal{{ $email->id }}">
                                {{ strtok($email->message_id, '@') ?? 'No Message ID' }}
                            </button>

                            <!-- Bootstrap Modal for Message ID -->
                            <div class="modal fade" id="messageModal{{ $email->id }}" tabindex="-1" aria-labelledby="messageModalLabel{{ $email->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="messageModalLabel{{ $email->id }}">{{ strtok($email->message_id, '@') ?? 'No Message ID' }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body overflow-x-auto break-words">
                                            <p><strong>Message ID:</strong> {{ $email->message_id }}</p>
                                            <p><strong>To:</strong> {{ $email->to }}</p>
                                            <p><strong>Subject:</strong> {{ $email->subject }}</p>
                                            <p><strong>Sender Email:</strong> {{ $email->sender_email }}</p>
                                            <p><strong>Sender Name:</strong> {{ $email->sender_name }}</p>
                                            <p><strong>Sender Domain:</strong> {{ $email->sender_domain }}</p>
                                            @if(request('show_recipient_name'))
                                                <p><strong>Name:</strong> {{ $email->recipient_name }}</p>
                                            @endif
                                            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $email->status)) }}</p>
                                            @if($email->error_message)
                                                <p><strong>Error Message:</strong> {{ $email->error_message }}</p>
                                            @endif
                                            <p><strong>Sent:</strong> {{ $email->created_at->format('Y-m-d H:i') }}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-2" style="max-width: 250px; white-space: nowrap; overflow-x: auto;">{{ $email->to }}</td>
                        @if(request('show_recipient_name'))
                            <td class="p-2" style="white-space: nowrap; max-width: 200px; overflow-x: auto;">{{ $email->recipient_name }}</td>
                        @endif
                        @if(request('show_subject'))
                            <td class="p-2" style="width: 200px; white-space: nowrap; overflow-x: auto;">{{ $email->subject }}</td>
                        @endif
                        <td class="p-2" style="max-width: 250px; white-space: nowrap; overflow-x: auto;">{{ $email->sender_email }}</td>
                        @if(request('show_sender_name'))
                            <td class="p-2" style="white-space: nowrap; max-width: 200px; overflow-x: auto;">{{ $email->sender_name }}</td>
                        @endif
                        @if(request('show_sender_domain'))
                            <td class="p-2" style="white-space: nowrap; max-width: 200px; overflow-x: auto;">{{ $email->sender_domain }}</td>
                        @endif
                        <td class="p-2">
                            <button type="button" class="btn btn-sm text-white" data-bs-toggle="modal" data-bs-target="#statusModal{{ $email->id }}">
                                <span class="badge bg-{{ match($email->status) {
                                    'sending' => 'info',
                                    'delivered' => 'success',
                                    'failed', 'permanent_failure' => 'danger',
                                    'complained', 'temporary_failure' => 'warning',
                                    'clicked' => 'primary',
                                    'opened' => 'secondary',
                                    'unsubscribed' => 'dark',
                                    default => 'secondary',
                                } }}">{{ ucfirst(str_replace('_', ' ', $email->status)) }}</span>
                            </button>

                            <!-- Bootstrap Modal for Status -->
                            <div class="modal fade" id="statusModal{{ $email->id }}" tabindex="-1" aria-labelledby="statusModalLabel{{ $email->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="statusModalLabel{{ $email->id }}">{{ ucfirst(str_replace('_', ' ', $email->status)) }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $email->status)) }}</p>
                                            <p><strong>Error Message:</strong> {{ $email->error_message }}</p>
                                            <p><strong>Sent:</strong> {{ $email->created_at->format('Y-m-d H:i') }}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-2" style="white-space: nowrap;">{{ $email->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ request('show_sender_domain') || request('show_sender_name') ? '9' : '8' }}" class="text-center">No emails found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $emails->links() }}
        </div>
        <div class="mt-3 text-center">
            <p>Showing {{ $emails->count() }} of {{ $emails->total() }} records. Page {{ $emails->currentPage() }} of {{ $emails->lastPage() }}.</p>
        </div>
    </div>
@endsection

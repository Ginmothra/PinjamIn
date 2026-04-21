<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h2 class="text-2xl font-bold">Log Aktivitas Sistem</h2>
                        
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('admin.activity-log.index') }}" class="flex flex-wrap gap-3">
                            <select name="type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua Tipe</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>

                            <select name="action" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua Aksi</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                                @endforeach
                            </select>

                            <input type="date" name="date" value="{{ request('date') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">

                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">
                                Filter
                            </button>
                            
                            @if(request()->anyFilled(['type', 'action', 'date']))
                                <a href="{{ route('admin.activity-log.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    <div class="overflow-x-auto border rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-slate-50 text-slate-500 text-left text-xs uppercase font-bold tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Waktu</th>
                                    <th class="px-6 py-4">Tipe</th>
                                    <th class="px-6 py-4">Aksi</th>
                                    <th class="px-6 py-4">ID Record</th>
                                    <th class="px-6 py-4">Detail Perubahan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-bold rounded-full 
                                                {{ $log->log_type == 'LOANS' ? 'bg-sky-100 text-sky-700' : '' }}
                                                {{ $log->log_type == 'ITEMS' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                {{ $log->log_type == 'RETURNS' ? 'bg-amber-100 text-amber-700' : '' }}
                                                {{ $log->log_type == 'USERS' ? 'bg-purple-100 text-purple-700' : '' }}
                                                {{ $log->log_type == 'REPAIRS' ? 'bg-rose-100 text-rose-700' : '' }}
                                                {{ $log->log_type == 'FINES' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                            ">
                                                {{ $log->log_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            {{ $log->action }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            #{{ $log->record_id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="max-w-xs overflow-hidden">
                                                @if($log->old_data)
                                                    <div class="text-[10px] text-red-500 font-mono mb-1">OLD: {{ $log->old_data }}</div>
                                                @endif
                                                @if($log->new_data)
                                                    <div class="text-[10px] text-emerald-600 font-mono">NEW: {{ $log->new_data }}</div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                            Belum ada log aktivitas yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

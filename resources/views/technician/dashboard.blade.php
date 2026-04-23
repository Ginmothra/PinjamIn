<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Technician Dashboard</h1>
                    <p class="mt-2 text-slate-600 font-medium">Pantau dan kerjakan perbaikan barang yang rusak.</p>
                </div>
            </div>

            <x-auth-session-status class="mb-6" :status="session('status')" />
            @if(session('error'))
                <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-100 text-sm text-red-600 font-bold">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- 1. MENUNGGU PERBAIKAN -->
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600 font-bold">!</span>
                        <h2 class="text-xl font-bold text-slate-800">Menunggu Perbaikan</h2>
                    </div>

                    @forelse($repairs->where('status', 'pending') as $repair)
                        <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-md border-l-4 border-rose-400">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                                        {{ $repair->item->category->name }}
                                    </span>
                                    <h3 class="text-lg font-extrabold text-slate-900">{{ $repair->item->name }}</h3>
                                    <p class="text-xs font-mono text-slate-400">#{{ $repair->item->code }}</p>
                                </div>
                                <span class="rounded-lg bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600 uppercase">Rusak</span>
                            </div>

                            <div class="mb-6 rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Keterangan Kerusakan:</p>
                                <p class="text-sm text-slate-700 italic">"{{ $repair->description }}"</p>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <div class="text-xs text-slate-400 italic">
                                    Dilaporkan: {{ $repair->created_at->diffForHumans() }}
                                </div>
                                <form method="POST" action="{{ route('technician.repairs.start', $repair) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition-all transform active:scale-95">
                                        Mulai Perbaiki
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                            <p class="text-slate-400 font-medium">Tidak ada antrean perbaikan.</p>
                        </div>
                    @endforelse
                </div>

                <!-- 2. SEDANG DIKERJAKAN -->
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600 font-bold">⚙️</span>
                        <h2 class="text-xl font-bold text-slate-800">Sedang Dikerjakan</h2>
                    </div>

                    @forelse($repairs->where('status', 'in_progress') as $repair)
                        <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm ring-1 ring-amber-200 border-l-4 border-amber-400 transition-all">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-extrabold text-slate-900">{{ $repair->item->name }}</h3>
                                    <p class="text-xs font-mono text-slate-400 italic">Mulai sejak: {{ $repair->started_at->diffForHumans() }}</p>
                                </div>
                                <span class="rounded-lg bg-amber-50 px-3 py-1 text-xs font-bold text-amber-600 animate-pulse">PROSES</span>
                            </div>

                            <div class="mb-6 rounded-2xl bg-amber-50/50 p-4 border border-amber-100">
                                <p class="text-xs font-bold text-amber-400 uppercase mb-1">Target Perbaikan:</p>
                                <p class="text-sm text-slate-700 italic">"{{ $repair->description }}"</p>
                            </div>

                            <form method="POST" action="{{ route('technician.repairs.complete', $repair) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white hover:bg-amber-600 shadow-lg shadow-amber-200 transition-all transform active:scale-95">
                                    Konfirmasi Selesai Perbaikan
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                            <p class="text-slate-400 font-medium">Belum ada pengerjaan aktif.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
